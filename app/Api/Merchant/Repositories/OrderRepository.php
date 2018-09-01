<?php
namespace App\Api\Merchant\Repositories;


use App\Api\Merchant\Services\MemberCreditsService;
use App\Comment;
use App\Common\Services\MqttNotificationService;
use App\Coupons;
use App\Invitelog;
use App\Jobs\RebateOrderJob;
use App\Jobs\StoreProcessJob;
use App\Member;
use App\MemberCredits;
use App\MemberCreditsLog;
use App\Notice;
use App\Order;
use App\RebateOrder;
use App\StoreAccount;
use App\StoreTrans;
use Carbon\Carbon;

class OrderRepository
{

    protected $platform_probability = 0.04; //單筆消費
    protected $generalize = 0.5;

    /**
     *  獲取待處理的訂單列表
     */
    public function getProcessOrder($store_id,$per_page){
        return (new Order())->join('member','member.id','=','member_id')->where('store_id','=',$store_id)
            ->where('orders.status',0)
            ->select([
                'orders.id',
                'orders.order_no',
                'orders.date',
                'orders.member_id',
                'orders.amount',
                'orders.credits',
                'orders.coupons_money',
                'orders.status',
                'orders.created_at',
                'orders.number',
                'member.username',
                'member.nickname',
                'member.phone'
            ])->orderBy('orders.id','ASC')->paginate($per_page);

    }

    public function getOrderById($store_id,$order_id){
        return (new Order())->leftJoin('store_account','store_account.store_id','=','orders.store_id')
            ->leftJoin('store','store.id','=','orders.store_id')
            ->leftJoin('member','member.id','=','orders.member_id')
            ->where('orders.store_id','=',$store_id)
            ->where('orders.id','=',$order_id)
            ->where('orders.status',0)
            ->first([
                'orders.id',
                'orders.store_id',
                'orders.order_no',
                'orders.order_sn',
                'orders.date',
                'orders.member_id',
                'orders.amount',
                'orders.credits',
                'orders.coupons_id',
                'orders.coupons_money',
                'orders.status',
                'orders.created_at',
                'orders.mrate',
                \DB::raw('store_account.id as account_id'),
                'store_account.return_credits',
                'store_account.credits_income',
                'store_account.business_income',
                'store_account.probability',
                'store_account.fixed_probability',
                'store_account.feature_probability_time',
                'store_account.feature_probability',
                'store.name',
                'member.nickname',
                'member.promo_code',
                'member.code_type',
            ]);
    }

    public function changeOrderStatusByOrder($order,$status,$user){
        /**
         * @var \App\Order $order
         */
        $order->status = $status;
        $order->updated_at = date('Y-m-d H:i:s');
        $order->updated_by = $user->id;
        $order->refund_at = date('Y-m-d H:i:s');
        $order->refund_user_id = $user->id;
        $order->save();

        //如果優惠券id 大於0 ，需要將狀態改回
        if($order->coupons_id > 0){
            (new Coupons())->where('id','=',$order->coupons_id)
                ->where('member_id','=','member_id')
                ->update([
                    'status' => 1
                ]);
        }
        MqttNotificationService::sendStoreProcessMessage($order->store_id,$order->id,'cancel');
        MqttNotificationService::sendMemberMessage($order->member_id,$order->id,'cancel');

        return true;
    }

    /**
     * 獲取推廣人的用戶ID
     */
    protected function getGeneralizeObject($member_id){
        $result = (new Invitelog())->where('invite_uid','=',$member_id)
            ->first(['promo_uid','invite_type']);
        if($result != null){
            return array(
                'promo_uid' => $result->promo_uid,
                'invite_type' => $result->invite_type);
        }else{
            return null;
        }
    }

    /**
     * 獲取會員積分錢包
     */
    protected function getMemberCredits($member_id){
        $memberCredits = (new MemberCredits())->where('member_id','=',$member_id)
            ->first([
                'id',
                'total_credits',
                'grand_total_credits',
                'wait_total_credits',
                'promo_credits',
                'promo_credits_total'
            ]);
        if($memberCredits == null){
            (new MemberCredits())->insert(MemberCreditsService::initData($member_id));
            return $this->getMemberCredits($member_id);
        }
        return $memberCredits;
    }

    public function editOrderByOrder($order,$user){
        if(intval($order->status) !== 0){
            return 0;
        }
        $member_rebate = 0;
        //订单更新资料
        \DB::beginTransaction();
        try{
            $orderUpdate = array(
                'status' => 1,
                'updated_at' => date('Y-m-d H:i:s'),
                'checkout_at' => date('Y-m-d H:i:s'),
                'checkout_user_id' => $user->id,
                'updated_by' => $user->id
            );
            //1. 單筆消費金額
            $amount = $order->amount;
            $fixed_probability = $order->fixed_probability;
            $probability = $order->probability;

            //2. 獲取各項回贈積分金額
            $mfixedrate = floatval(($amount * ($fixed_probability/100)) * 100) / 100; //會員立即返積分
            $prate = floatval(($amount * $this->platform_probability)*100)/100; //平臺收益
            $mrate = floatval(($amount * ($probability/100)) * 100) / 100; //會員週期回贈
            $store_promo_rate = 0; //店铺推广回赠积分
            //确保45天内，每天至少能够返利到0.01积分
            if($mrate < 0.45){
                $mrate = 0;
            }
            //3. 比較商家回贈積分是否足夠回贈
            $store_credits = $order->return_credits;
            $store_credits_income = $order->credits_income; //回饋積分收入
            $business_income = $order->business_income; //營業收入
            $outcome = $mfixedrate + $prate + $mrate; //总回赠金额
            $transData = array();
            //4. 如果商家回贈積分足夠回贈，需要進行回贈
            if($outcome <= $store_credits){
                $orderUpdate['mrate'] = $mrate;
                $orderUpdate['prate'] = $prate;
                $orderUpdate['mfixedrate'] = $mfixedrate;
                // 5. 从商家积分回赠余额中扣除金额
                // 财务记录 //支出
                $transData[] = array(
                    'store_id' => $order->store_id,
                    'trans_type' => 2,
                    'trans_category' => 3,
                    'trans_category_name' => '蜂幣額度',
                    'trans_description' => '支出蜂幣',
                    'trans_date' => date('Y-m-d'),
                    'trans_datetime' => date('Y-m-d H:i:s'),
                    'amount' => $outcome,
                    'balance' => $store_credits,
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => $user->id,
                    'created_name' => $user->nickname,
                    'custom_field1' => ''
                );
                $store_credits -= $outcome;

                //\Log::debug("rebate:{$mfixedrate},{$mrate}");
                $this->memberLogs($order,$mfixedrate,$mrate);
                //将平台收益的 50% 放入到推广中
                $other_promo_rate = floatval(($prate * 0.5) * 100) / 100;
                $promo_exist = true; //会员是否绑定了推广对象
                //獲取會員是否綁定推廣人
                $generalizeObject = $this->getGeneralizeObject($order->member_id);
                if($generalizeObject != null){
                    $promo_type = intval($generalizeObject['invite_type']); //推广类型
                    \Log::debug("store order: 会员ID:{$order->member_id} 类型:{$promo_type}");
                    if($promo_type == 1){
                        //如果會員綁定推广的会员，需要從平臺中抽取 50%給推廣人
                        $this->generalizeToMember($order,$generalizeObject['promo_uid'],$other_promo_rate);
                    }else if($promo_type == 2){
                        //如果會員綁定店家，需要從平臺中抽取 50%給推廣人
                        if($generalizeObject['promo_uid'] == $order->store_id){
                            $transData[] = array(
                                'store_id' => $generalizeObject['promo_uid'],
                                'trans_type' => 1,
                                'trans_category' => 4,
                                'trans_category_name' => '蜂幣收入',
                                'trans_description' => '店家推廣回贈獲得蜂幣',
                                'trans_date' => date('Y-m-d'),
                                'trans_datetime' => date('Y-m-d H:i:s'),
                                'amount' => $other_promo_rate,
                                'balance' => $store_credits_income,
                                'created_at' => date('Y-m-d H:i:s'),
                                'created_by' => 0,
                                'created_name' => 'system',
                                'custom_field1' => ''
                            );

                            $store_credits_income += $other_promo_rate;
                        }else{
                            $tempStoreAccount = (new StoreAccount())->where('store_id','=',$generalizeObject['promo_uid'])
                                ->first([
                                    'id',
                                    'credits_income',
                                ]);
                            if($tempStoreAccount){
                                $tempData = array(
                                    'store_id' => $generalizeObject['promo_uid'],
                                    'trans_type' => 1,
                                    'trans_category' => 4,
                                    'trans_category_name' => '蜂幣收入',
                                    'trans_description' => '店家推廣回贈獲得蜂幣',
                                    'trans_date' => date('Y-m-d'),
                                    'trans_datetime' => date('Y-m-d H:i:s'),
                                    'amount' => $other_promo_rate,
                                    'balance' => $tempStoreAccount->credits_income,
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'created_by' => 0,
                                    'created_name' => 'system',
                                    'custom_field1' => ''
                                );
                                (new StoreTrans())->insert($tempData);
                                $tempStoreAccount->credits_income += $other_promo_rate;
                                $tempStoreAccount->save();
                            }
                        }

                    }
                }else{
                    $promo_exist = false;
                }

                if($promo_exist){
                    $prate = $prate - $other_promo_rate;
                    $orderUpdate['prate'] = $prate;
                    $orderUpdate['promoreate'] = $other_promo_rate;
                }else{
                    $orderUpdate['prate'] = $prate;
                    $orderUpdate['promoreate'] = 0;
                }
                $member_rebate = $mfixedrate  + $mrate;

            }
            //積分折抵，算商家營業收入
            if($order->credits > 0){
                //積分收入
                $transData[] = array(
                    'store_id' => $order->store_id,
                    'trans_type' => 1,
                    'trans_category' => 2,
                    'trans_category_name' => '蜂幣收入',
                    'trans_description' => '消費者使用蜂幣抵折',
                    'trans_date' => date('Y-m-d'),
                    'trans_datetime' => date('Y-m-d H:i:s'),
                    'amount' => $order->credits,
                    'balance' => $business_income,
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => 0,
                    'created_name' => 'system',
                    'custom_field1' => ''
                );
                $business_income += $order->credits;
            }
            //平台發行的優惠券需要返回金額給店家
            if(intval($order->coupons_id) > 0){
                $res = (new \App\Coupons())->leftJoin('coupons_release','coupons_release.id','=','coupons_id')
                    ->where(['coupons.id'=>$order->coupons_id])
                    ->first(['release_type']);
                if($res && $res->release_type==1){

                    $transData[] = array(
                        'store_id' => $order->store_id,
                        'trans_type' => 1,
                        'trans_category' => 2,
                        'trans_category_name' => '蜂幣收入',
                        'trans_description' => '消費者使用優惠券抵折',
                        'trans_date' => date('Y-m-d'),
                        'trans_datetime' => date('Y-m-d H:i:s'),
                        'amount' => $order->coupons_money,
                        'balance' => $business_income,
                        'created_at' => date('Y-m-d H:i:s'),
                        'created_by' => 0,
                        'created_name' => 'system',
                        'custom_field1' => ''
                    );

                    $business_income += $order->coupons_money;
                }
                //優惠券使用記錄
                $data = (new Coupons())->where('id','=',$order->coupons_id)->first();
                if($data){
                    $this->coupons_use_log($data->coupons_id,$data->member_id);
                }
                //商家處理了訂單，需要將優惠券刪除
                (new Coupons())->where('id','=',$order->coupons_id)
                    ->where('member_id','=',$order->member_id)
                    ->delete();
            }

            if(count($transData) > 0){
                (new StoreTrans())->insert($transData);
            }


            //更新店家資金
            \Log::debug("order Account id:".$order->account_id." return_credits -{$store_credits} credits_income +{$store_credits_income}");
            (new StoreAccount())->where('id','=',$order->account_id)
                ->update([
                    'return_credits' => $store_credits,
                    'credits_income' => $store_credits_income,
                    'business_income' => $business_income,
                ]);

            (new Order())->where('id','=',$order->id)->update($orderUpdate);


            \DB::commit();

            MqttNotificationService::sendStoreProcessMessage($order->store_id,$order->id,'complete');
            MqttNotificationService::sendMemberMessage($order->member_id,$order->id,'complete',$member_rebate);

            return $member_rebate;
        }catch (\Exception $e){
            \DB::rollback();
            \Log::error("Deal with order fail:".$e->getMessage().$e->getFile().$e->getLine());
            return false;
        }
    }
    /**
     * 優惠券使用記錄
     */
    private function coupons_use_log($coupons_id,$member_id){
        $data = [
            'coupons_id'  => $coupons_id,
            'member_id'   => $member_id,
            'use_at' => date('Y-m-d'),
            'type' => 2
        ];
        (new \App\CouponsReceiveLog())->insert($data);

    }

    /**
     * 如果用户绑定了推广会员，则回赠给绑定的会员
     * @param $order
     * @param $promo_uid
     * @param $other_promo_rate
     */
    protected function generalizeToMember($order,$promo_uid,$other_promo_rate){
        $promo_member = (new Member())->where('id','=',$promo_uid)
            ->first(['id','user_type']);
        if($promo_member){
            $user_type = ($promo_member->user_type == 2)?2:1;
            $this->generalizeMemberLog($order,$promo_uid,$other_promo_rate,$user_type);
        }
    }

    /**
     * @param $order
     * @param $member_id
     * @param $promoreate
     * @param int $type 会员类型 1:普通会员 2推广用户
     */
    protected function generalizeMemberLog($order,$member_id,$promoreate,$type = 1){
        $memberCredits = $this->getMemberCredits($member_id);
        //推廣会员收益记录
        $memberTransData = array(
            'member_id' => $member_id,
            'type' => 1,
            'date' => date('Y-m-d'),
            'trade_type' => '推廣回饋',
            'log_date' => date('Y-m-d H:i:s'),
            'log_no' =>  MemberCreditsService::generateLogNo(),
            'amount' => $promoreate,
            'status' => 1,
            'remark' => sprintf('%s在 %s 消費獲得推廣回饋','會員',$order->name),
            'order_id' => $order->id,
            'order_sn' => $order->order_sn
        );
        //更新推廣會員資金
        if($type==2){
            $memberTransData['balance'] = $memberCredits->promo_credits;
            $memberCredits->promo_credits_total += $promoreate;
            $memberCredits->promo_credits += $promoreate;
        }else{
            $memberTransData['balance'] = $memberCredits->total_credits;
            $memberCredits->total_credits += $promoreate;
            $memberCredits->grand_total_credits += $promoreate;
        }

        (new MemberCreditsLog())->insert($memberTransData);
        $memberCredits->save();
    }

    protected function memberLogs($order,$mfixedrate,$mrate){
        $memberCredits = $this->getMemberCredits($order->member_id);

        //会员收益记录
        $memberTransData = array(

        );

        if($mfixedrate > 0){
            $memberTransData[] = array(
                'member_id' => $order->member_id,
                'type' => 1,
                'trade_type' => '回饋',
                'log_date' => date('Y-m-d H:i:s'),
                'log_no' =>  MemberCreditsService::generateLogNo(),
                'amount' => $mfixedrate,
                'balance' => $memberCredits->total_credits,
                'status' => 1,
                'remark' => sprintf('在 %s 領返利蜂幣',$order->name),
                'order_id' => $order->id,
                'order_sn' => $order->order_sn,
                'date' => date('Y-m-d')
            );
        }


        //更新會員資金
        $memberCredits->total_credits += $mfixedrate;
        $memberCredits->grand_total_credits += $mfixedrate;
        if($mrate >= 0.45){
            $memberCredits->wait_total_credits += $mrate;
        }

        //會員折抵記錄
        if($order->credits > 0){
            $memberTransData[] = array(
                'member_id' => $order->member_id,
                'type' => 2,
                'trade_type' => '折抵',
                'log_date' => date('Y-m-d H:i:s'),
                'log_no' => MemberCreditsService::generateLogNo(),
                'amount' => $order->credits,
                'balance' => $memberCredits->total_credits,
                'status' => 1,
                'remark' => sprintf('在 %s 使用蜂幣折抵',$order->name),
                'order_id' => $order->id,
                'order_sn' => $order->order_sn,
                'date' => date('Y-m-d')
            );
            $memberCredits->total_credits -= $order->credits;
        }
        if(count($memberTransData) > 0){
            (new MemberCreditsLog())->insert($memberTransData);
        }

        $memberCredits->save();

        if($mrate >= 0.45){
            //從當前時間開始計算開始時間和結束時間
            $start_date = Carbon::create(date('Y'),date('m'),date('d'),0,0,0)->addDay(0)
                ->addMinute(1); //處理訂單之後，次日開始週期返利
            $end_date = Carbon::create(date('Y'),date('m'),date('d'),0,0,0)
                ->addDay(0)->addMinute(45);
            $start_data = array(
                'order_id' => $order->id,
                'member_id' => $order->member_id,
                'cycle_point' => $mrate,
                'cycle_start' => $start_date->toDateTimeString(),
                'cycle_end' => $end_date->toDateTimeString(),
                'cycle_days' => 45 ,
                'interest_remain' => $mrate,
                'cycle_days_remain' => 45,
                'cycle_status' => 1, //待返利
            );
            $rebateOrderId = (new RebateOrder())->insertGetId($start_data);

            //添加通知消息
            $noticeDesc = sprintf(
                config('memecoins.noticeTemplate.walletRebate.description'),
                $order->nickname,
                date('Y-m-d'),
                $order->name,
                $order->amount,
                $order->probability.'%',
                $mrate);
            (new Notice())->insert([
                'title' => config('memecoins.noticeTemplate.walletRebate.title'),
                'description' => $noticeDesc,
                'type_id' => 2,
                'content' => '',
                'icon' => '',
                'url' => '',
                'point_id' => $order->id,
                'member_id' => $order->member_id,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            //\Log::debug("i'm jobs {$rebateOrderId}");
//            $firstRebateJob = (new RebateOrderJob($rebateOrderId))->onQueue('rebate_order');
//            dispatch($firstRebateJob);

        }


    }

    /**
     * @param  \Illuminate\Database\Query\Builder $query
     * @param  array $param
     * @return \Illuminate\Database\Query\Builder
     */
    protected function commonHistoryCondition($query,$param){
        //时间
        if($param['start_date'] && $param['end_date']){
            $param['start_date'] = date('Y-m-d 00:00:00',strtotime($param['start_date']));
            $param['end_date'] = date('Y-m-d 23:59:59',strtotime($param['end_date']));
            $query->whereBetween('orders.created_at',[$param['start_date'],$param['end_date']]);
        }
        //状态
        if($param['status'] > 0){
            $query->where('orders.status','=',$param['status']);
        }else if (intval($param['status']) === 0){
            $query->where('orders.status','>=',1);
        }

        //完成用户
        if(intval($param['checkout_by']) > 0){
            $query->where('orders.updated_by','=',intval($param['checkout_by']));
        }


        if(intval($param['refund_by']) > 0){
            $query->where('orders.updated_by','=',intval($param['refund_by']));
        }
        return $query;
    }

    public function getOrderHistoryList($param){
        $query = (new Order())
            ->leftJoin('store_user','store_user.id','=','orders.updated_by')
            ->leftJoin('member','member.id','=','orders.member_id')
            ->where('orders.store_id','=',$param['store_id']);
        $query = $this->commonHistoryCondition($query,$param);
        $query->select([
            'orders.id',
            'orders.number',
            'orders.order_no',
            'orders.status',
            'orders.amount',
            'orders.credits',
            'orders.coupons_money',
            'orders.updated_at',
            'store_user.nickname',
            \DB::raw( 'member.nickname as member_nickname'),
            \DB::raw( 'member.username as member_username'),
        ])->orderBy('orders.id','DESC');
        return $query->paginate(intval($param['per_page']));
    }

    public function getOrderHistoryReport($param){
        $query = (new Order())
            ->leftJoin('store_user','store_user.id','=','orders.updated_by')
            ->where('orders.store_id','=',$param['store_id']);
        $query = $this->commonHistoryCondition($query,$param);
        $result = $query->first([
            \DB::raw('sum(amount) as amount'),
            \DB::raw('sum(credits) as credits'),
            \DB::raw('sum(coupons_money) as coupons'),
            \DB::raw('sum(prate) as prate'),
            \DB::raw('sum(mfixedrate) as mfixedrate'),
            \DB::raw('sum(mrate) as mrate'),
            \DB::raw('sum(promoreate) as promoreate'),
        ]);
        $sumResult = $result;
        $amount = floatval($sumResult->amount);
        $discounts = floatval($sumResult->credits);
        $coupons = floatval($sumResult->coupons);
        $outcome = ($sumResult->prate + $sumResult->mfixedrate + $sumResult->mrate + $sumResult->promoreate);
        return ['sales' => $amount,'discounts' => $discounts,'outcome' => $outcome,'coupons' => $coupons];
    }

    protected function commonCommentListQuery($store_id,$level){
        $start_date = Carbon::now()->subMonth(3)->toDateTimeString();
        $end_date = Carbon::now()->toDateTimeString();
        $query = (new Comment())->leftJoin('member','comments.member_id','=','member.id')
            ->where('comments.store_id','=',$store_id)
            ->whereBetween('comments.created_at',[strtotime($start_date),strtotime($end_date)]);
        if(intval($level) > 0){
            $query->where('level','=',$level);
        }
        return $query;
    }

    public function commentList($store_id,$level = 0,$per_page){
        $query = $this->commonCommentListQuery($store_id,$level);
        $query->select([
            'comments.id',
            'comments.content',
            'comments.level',
            'comments.is_reply',
            'comments.reply_content',
            'comments.created_at',
            'comments.updated_at',
            'comments.member_id',
            'member.nickname',
            'member.avatar',
        ]);
        return $query->orderBy('comments.id','DESC')
            ->paginate($per_page);
    }

    public function commentReport($store_id,$level = 0){
        $query = $this->commonCommentListQuery($store_id,$level);
        $result = $query->first([
            \DB::raw('sum(IF(level = 1,1,0)) as satisfy'),
            \DB::raw('sum(IF(level = 2,1,0)) as general'),
            \DB::raw('sum(IF(level = 3,1,0)) as yawp'),
        ]);
        if($result){
            return [
                'satisfy' => intval($result->satisfy),
                'general' => intval($result->general),
                'yawp' => intval($result->yawp),
            ];
        }else{
            return [
                'satisfy' => 0,
                'general' => 0,
                'yawp' => 0,
            ];
        }
    }

    public function addReplyComment($store_id,$id,$reply_content){
        return (new Comment())->where('id','=',$id)
            ->where('store_id','=',$store_id)
            ->update([
                'is_reply' => 1,
                'reply_content' => $reply_content,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
    }




}