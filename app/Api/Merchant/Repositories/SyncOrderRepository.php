<?php
namespace App\Api\Merchant\Repositories;


use App\MemberCredits;
use App\MemberCreditsLog;
use App\Order;
use App\RebateOrder;
use Carbon\Carbon;

class SyncOrderRepository
{
    public function orderList($param,$per_page){
        return (new Order())->leftJoin('rebate_orders','rebate_orders.order_id','=','orders.id')
            ->where('orders.date','=',$param['date'])
            ->where('orders.status','=',1)
            ->where('rebate_orders.cycle_point','>=',0.45)
            ->select([
                'orders.date',
                'rebate_orders.id',
                'rebate_orders.order_id',
                'rebate_orders.member_id',
                'rebate_orders.cycle_point',
                'rebate_orders.cycle_start',
                'rebate_orders.cycle_end',
                'rebate_orders.interest_remain',
                'rebate_orders.cycle_days_remain',
                'rebate_orders.cycle_status',
                'rebate_orders.interest_ever',
                'rebate_orders.cycle_days',
            ])
            ->orderBy('orders.id','ASC')
            ->paginate($per_page);
    }

    public function syncRebateOrder($param){
        $result = array(
            'success' => true,
            'data' => array(
                'old_data' => array()
            )
        );
        $order_id = $param['order_id'];
        $cycle_status = $param['cycle_status'];
        $cycle_days_remain = $param['cycle_days_remain'];
        $interest_ever = $param['interest_ever'];
        $interest_remain = $param['interest_remain'];
        $currentCredits = $param['current_rebate'];
        $rebateOrder = (new RebateOrder())->where('order_id','=',$order_id)
            ->first([
                'id',
                'order_id',
                'member_id',
                'cycle_status',
                'cycle_days_remain',
                'interest_ever',
                'interest_remain'
            ]);
        if($rebateOrder != null){
            \DB::beginTransaction();
            try{
                $result['data']['old_data'] = array(
                    'order_id' => $rebateOrder->order_id,
                    'cycle_status' => $rebateOrder->cycle_status,
                    'cycle_days_remain' => $rebateOrder->cycle_days_remain,
                    'interest_ever' => $rebateOrder->interest_ever,
                    'interest_remain' => $rebateOrder->interest_remain,
                    'platform' => 'memecoins'
                );
                //1. 保存返利资料
                $rebateOrder->cycle_status = $cycle_status;
                $rebateOrder->cycle_days_remain = $cycle_days_remain;
                $rebateOrder->interest_ever = $interest_ever;
                $rebateOrder->interest_remain = $interest_remain;
                $rebateOrder->save();
                //2. 生成返利日志
                $resultLog = $this->rebateCredits([
                    'amount' => $currentCredits,
                    'member_id' => $rebateOrder->member_id,
                    'order_id' => $rebateOrder->order_id
                ]);
                if($resultLog === false){
                    \Log::debug("rebate job is in remain fail {$rebateOrder->order_id} {$rebateOrder->interest_remain}");
                    \DB::rollback();
                }else{
                    \DB::commit();
                    \Log::debug("rebate job is in remain {$rebateOrder->order_id} {$rebateOrder->interest_remain}");
                }
            }catch (\Exception $e){
                \DB::rollback();
                \Log::error("rebate order {$rebateOrder->id} fail".$e->getMessage());
                $result['success'] = false;
            }
        }else{
            $result['success'] = false;
        }
        return $result;
    }

    /**
     * 返利積分給會員
     */
    protected function rebateCredits($params){
        try{
            $credits = $params['amount'];
            //更新用戶錢包 現金餘額
            $member_wallet = (new MemberCredits())->where('member_id',$params['member_id'])->first([
                'id',
                'total_credits',
                'grand_total_credits',
                'wait_total_credits'
            ]);

            $current_blance = 0;
            $id = 0;
            if($member_wallet){
                $current_blance = $member_wallet->total_credits;
                $id = $member_wallet->id;
                $member_wallet->total_credits += $credits;
                $member_wallet->grand_total_credits += $credits;
                $member_wallet->wait_total_credits -= $credits;
                $member_wallet->save();
                $remark = sprintf('在 %s 消費獲得回饋蜂幣','test');
                $this->createCreditsLog($params['member_id'],$credits,'回饋蜂幣',$current_blance,$remark,$params['order_id']);
            }
            return true;
        }catch (\Exception $e){
            \Log::error("create rebate credits fail:".$e->getMessage().' '.$e->getFile().''.$e->getLine());
            return false;
        }
    }

    public static function orderFormatUser($uid){
        return sprintf('%05d',$uid);
    }

    public static function getTradeNo($type,$uid = 0){
        $types = array(
            'recharge' => '901', //充值积分
            'wallet_log' => '201', //钱包明细
            'trade' => '301',//交易订单
            'pay' => '801',//積分支付
            'interest' => '601',//訂單回贈
            'refund' => '401',//退款
        );
        $prefix = isset($types[$type])?$types[$type]:'';
        return $prefix.date('ymdHis').mt_rand(1,8).self::orderFormatUser($uid);
    }

    /**
     * 添加 钱包明细
     * @param $member_id
     * @param $amount
     * @param string $trade_type
     * @param int $balance
     * @param string $remark
     */
    public function createCreditsLog($member_id,$amount,$trade_type = '回饋蜂幣',$balance = 0,$remark = '',$order_id = 0, $order_sn = ''){
        $data = array(
            'log_no' => $this->getTradeNo('interest',$member_id),
            'type' => 1,
            'amount' => $amount,
            'remark' => $remark,
            'trade_type' => $trade_type,
            'member_id' => $member_id,
            'date' => Carbon::now()->toDateString(),
            'log_date' => Carbon::now()->toDateTimeString(),
            'status' => 1,
            'balance' => $balance,
            'order_id' => $order_id,
            'order_sn' => $order_sn
        );
        (new MemberCreditsLog())->insert($data);
    }

}