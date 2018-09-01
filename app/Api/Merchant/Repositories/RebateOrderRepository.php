<?php


namespace App\Api\Merchant\Repositories;

use App\Jobs\RebateOrderJob;
use App\MemberCredits;
use App\MemberCreditsLog;
use App\RebateOrder;
use Carbon\Carbon;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;


class RebateOrderRepository
{

    protected $host = '192.168.1.80';
    protected $port = 5672;
    protected $queueName = 'meme_sync_order';
    protected $publicKey = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC+n1jnnUhLjty9IjFUfR3MFpKz
LLl+toOuPjGSb8zcEnhnMhuUmSPTgi3ne+4M1HVxXPBcp4GUeSRlnXkOqxpTMNk5
x1ixYdR6kD9T5fWmYy8hHU/Vjj2OrznRnWunc1B1Z915eHe1dyB+e4RvpCmfWTQZ
VGt325tyjyVch0bnZQIDAQAB
-----END PUBLIC KEY-----';

    public function rebateOrderFromJob($order_id){
        
        $rebateOrder = (new RebateOrder())->where('id','=',$order_id)->first();
        if($rebateOrder != null){
            $exchange = 'amq.topic';
            $queue = 'meme_sync_order';
            $connection = new AMQPStreamConnection($this->host, $this->port, 'memecoins', 'memecoins','/');
            $channel = $connection->channel();
            $channel->queue_declare($queue, false, true, false, true);
            $publicResourceId = openssl_pkey_get_public($this->publicKey);

            $messageData = array(
                'platform_name' => 'memecoins',
                'order_id' => $rebateOrder->order_id,
                'member_id' => $rebateOrder->member_id,
                'cycle_point' => $rebateOrder->cycle_point,
                'cycle_start' => $rebateOrder->cycle_start,
                'cycle_end' => $rebateOrder->cycle_end,
                'interest_remain' => $rebateOrder->interest_remain,
                'cycle_days_remain' => $rebateOrder->cycle_days_remain,
                'cycle_status' => $rebateOrder->cycle_status,
                'interest_ever' => $rebateOrder->interest_ever,
                'cycle_days' => $rebateOrder->cycle_days,
            );
            $jsonData = json_encode($messageData);
            $encrypted = '';
            openssl_public_encrypt($jsonData,$encrypted,$publicResourceId);
            $encrypted = base64_encode($encrypted);
            $msg = new AMQPMessage($encrypted);
            $channel->basic_publish($msg, '', $queue);
            $channel->close();
            $connection->close();
        }
    }

    public function rebateOrderFromJobOld($order_id){
        $rebateOrder = (new RebateOrder())->where('id','=',$order_id)->first();
        //\Log::debug("rebate job is runing rebateOrderFromJob");
        if($rebateOrder != null){
           // \Log::debug("rebate job is in {$order_id}");
            //待返利，需要將狀態改爲返利中
            if($rebateOrder->cycle_status == 1){
                $rebateOrder->cycle_status = 2;
            }
            \DB::beginTransaction();
            try{
                if($rebateOrder->cycle_days_remain > 0){
                    if($rebateOrder->interest_ever <= 0){
                        $rebateOrder->interest_ever = floor(($rebateOrder->cycle_point / $rebateOrder->cycle_days)*100)/100;
                    }
                    $currentCredits = 0;
                    $rebateDays = $rebateOrder->cycle_days - $rebateOrder->cycle_days_remain;//已返的天数
                    //第1天返利
                    if($rebateDays === 0){
                        $currentCredits = $rebateOrder->interest_ever;
                    }else if($rebateDays == ($rebateOrder->cycle_days - 1)){
                        //最后一天返利金额
                        $currentCredits = $rebateOrder->interest_remain;
                        $rebateOrder->cycle_status = 3; //回赠返利完毕
                    }else if($rebateDays < ($rebateOrder->cycle_days - 1) ){
                        //每天返的金额
                        $currentCredits = $rebateOrder->interest_ever;
                    }
                    if($rebateOrder->cycle_days_remain > 0){
                        $rebateOrder->cycle_days_remain -= 1; //减少剩余天数
                    }
                    $rebateOrder->interest_remain -= $currentCredits; //更新剩余未返的积分
                    $rebateOrder->save();
                    $result = $this->rebateCredits([
                        'amount' => $currentCredits,
                        'member_id' => $rebateOrder->member_id,
                        'order_id' => $rebateOrder->order_id
                    ]);
                    if($result === false){
                        //\Log::debug("rebate job is in remain fail {$rebateOrder->interest_remain}");
                        \DB::rollback();
                    }else{
                        \DB::commit();
                        //\Log::debug("rebate job is in remain {$rebateOrder->interest_remain}");
                        if($rebateOrder->interest_remain > 0){
                            $firstRebateJob = (new RebateOrderJob($order_id))->onQueue('rebate_order')->delay(1 * 2);
                            dispatch($firstRebateJob);
                        }

                    }
                }
            }catch (\Exception $e){
                \DB::rollback();
                \Log::error("rebate order {$rebateOrder->id} fail".$e->getMessage());
            }
        }
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
                $this->createCreditsLog($params['member_id'],$credits,'蜂幣回饋',$current_blance,$remark,$params['order_id']);
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
    public function createCreditsLog($member_id,$amount,$trade_type = '蜂幣回饋',$balance = 0,$remark = '',$order_id = 0, $order_sn = ''){
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