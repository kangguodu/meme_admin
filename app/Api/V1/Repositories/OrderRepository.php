<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/20
 * Time: 14:37
 */

namespace App\Api\V1\Repositories;

use App\Order;
use App\OrderRefund;
use App\Comment;
use App\Store;
use App\Coupons;
use App\MemberCredits;
use App\StoreUser;
use App\User;
use App\Api\V1\Services\BaseService;

class OrderRepository
{
    private function getOrderById($id){
       return Order::where('id',$id)->first();
    }
    private function getStoreById($id){
        return Store::where('id',$id)->first();
    }

    /**
     * 會員積分
     */
    private function getCredit($member_id){
        $data = MemberCredits::where('member_id',$member_id)->first(['total_credits']);
        if($data){
            return $data->total_credits;
        }
        return 0;
    }

    /**
     * 會員暱稱
     */
    private function getMemberInfoById($id){
        $data = User::where('id',$id)->first([
            'nickname',
        ]);
        if($data){
            return $data->nickname;
        }
        return '';
    }

    /**
     * 店家暱稱
     */
    private function getStoreUserInfoById($id){
        $data = StoreUser::where('id',$id)->first([
            'nickname',
        ]);
        if($data){
            return $data->nickname;
        }
        return '';
    }

    /**
     *下單會員number加1
     */
    private function updateMemberNumber($id){
        User::where('id',$id)->increment('number',1);
    }

    /**
     * 檢查是否有未處理的訂單
     */
    public function checkOrder($member_id){
        $data = Order::where(['member_id'=>$member_id,'status'=>0])->first();
        return $data;
    }

    /**
     * 店家回贈額度是否足夠
     */
    public function getStoreMoney($store_id){
        $data = (new \App\StoreAccount())->where('store_id',$store_id)
                                ->where('return_credits','>=',3000)
                                ->first();
        $result = $this->getStoreById($store_id);
        if(!$result->is_return){
            return false;
        }
        if(!$data){
            $job = (new \App\Jobs\CloseStoreJob($store_id))->onQueue('close')->delay(2*72*3600);
            dispatch($job);
            return false;
        }
        return true;
    }

    /**
     * 回贈額度檢測店家
     *
     */
    private function checkStoreMoney($store_id,$amount){
        $result = (new \App\StoreAccount())->where('store_id',$store_id)->first();
        $return = 0;
        if($result->feature_fixed_probability_time>0 && $result->feature_fixed_probability_time<=time()){
            $return += $amount * $result->feature_fixed_probability /100;
        }else{
            $return += $amount * $result->fixed_probability/100;
        }
        if($result->feature_probability_time>0 && $result->feature_probability_time<=time()){
            $return += $amount * $result->feature_probability/100;
        }else{
            $return += $amount * $result->probability/100;
        }
        $return += $amount * 0.04;
        if($result->return_credits < $return){
            return ['success'=>false,'msg'=>'店鋪當前回贈金額不足，暫時無法下單','flag'=>2];
        }
        return ['success'=>true];
    }
    /*
     * 下單
     */
    public function create($credentials,$member_id){
        $result = $this->checkOrder($member_id);
        if($result){
            return ['success'=>true,'msg'=>'您尚有待處理的訂單，請處理完再下單','result'=>$result,'flag'=>1];
        }
        $store = $this->getStoreById($credentials['store_id']);
        if(!$store){
            return ['success'=>false,'msg'=>'店铺不存在','flag'=>0];
        }
        if($store->status != 1){
            return ['success'=>false,'msg'=>'哭哭~糟糕！此店鋪尚未開啟權限，目前無法參與現金回饋活動，請告知店員或直接聯繫客服','flag'=>0];
        }

        $return_result = $this->checkStoreMoney($credentials['store_id'],$credentials['amount']);
        if(!$return_result['success']){
            return $return_result;
        }

        $mycredit = $this->getCredit($member_id);

        if($mycredit<$credentials['credits']){
            return ['success'=>false,'msg'=>'您的抵現蜂幣不足','flag'=>0];
        }
        $coupons_id = 0;
        $coupons = 0;//優惠券底線金額

        if(isset($credentials['coupons_id']) && $credentials['coupons_id']){
            $coupons_id = intval($credentials['coupons_id']);
            $res = (new \App\Coupons())->leftJoin('coupons_release','coupons_release.id','=','coupons_id')
                                        ->where(['coupons.id'=>$coupons_id,'coupons.status'=>1,'member_id'=>$member_id])
                                        ->first();
            if(!$res){
                return ['success'=>false,'msg'=>'此優惠券不存在','flag'=>0];
            }
            if($res->type==2 && $res->store_id != $credentials['store_id']){
                return ['success'=>false,'msg'=>'此優惠券不能在該店鋪使用','flag'=>0];
            }
            if($res->start_at > time() && $res->start_at){
                return ['success'=>false,'msg'=>'此優惠券未到使用時間','flag'=>0];
            }
            if($res->expire_time < time() && $res->expire_time){
                return ['success'=>false,'msg'=>'此優惠券已過期','flag'=>0];
            }
            if($res->conditions > $credentials['amount']){
                return ['success'=>false,'msg'=>'消費金額滿'.$res->conditions.'元才能用','flag'=>0];
            }
            $used = (new \App\CouponsReceiveLog())->where(['coupons_id'=>$res->coupons_id,'member_id'=>$member_id,'type'=>2,'use_at'=>date('Y-m-d')])->count();
            if($used >= $res->limit_number){
                return ['success'=>false,'msg'=>'當日最多使用'.$res->limit_number.'張','flag'=>0];
            }

            if($res->money > 0){
                $coupons = $res->money;
            }else{
                $coupons = $credentials['amount'] * (100-$res->discount)/100;
            }
        }

//        if(($coupons + $credentials['credits'] + $credentials['cash']) != $credentials['amount']){
//            return ['success'=>false,'msg'=>'錢款計算有誤，請重新計算后下單','flag'=>0];
//        }

        $number = $this->total($credentials['store_id']);
        $data = [
            'order_no' =>date('YmdHis').sprintf("%07d",$number),
            'store_id' => $credentials['store_id'],
            'member_id' => $member_id,
            'amount' =>  $credentials['amount'],
            'credits' => $credentials['credits'],
            'coupons_id' => $coupons_id,
            'coupons_money' => $coupons,
            'status' => 0,
            'store_name' => $store->name,
            'date' => date('Y-m-d'),
            'month'=>date('Y-m'),
            'number'=> $number,
        ];
        \DB::beginTransaction();
        try{
            Comment::unguard();
            $order = Order::create($data);
            Comment::reguard();
            if($order->coupons_id){
                (new \App\Coupons())->where('id',$order->coupons_id)->update(['status'=>0]);
            }
            \DB::commit();
            $data['created_at'] = date('Y-m-d H:i:s');
            $data['id'] = $order->id;
            $this->updateMemberNumber($member_id);
            $this->updateStoreOrderNumber($credentials['store_id']);
            $this->bind_store($store,$member_id);//綁定店家邀請碼
            $this->contact_store($credentials['store_id'],$member_id);
            return ['success'=>true,'msg'=>'','result'=>$data,'flag'=>0];

        }catch (\Exception $e){
            \DB::rollback();
            \Log::error("create order fail: ".$e->getMessage().', '.$e->getLine());
            return ['success'=>false,'msg'=>'下單出錯','flag'=>0];
        }
    }

    /*
     * 取消
     */
    public function cancel($id){
        \DB::beginTransaction();
        try{
            $order = Order::where('id',$id)->first();
            if($order->status != 0){
                return ['success'=>false,'msg'=>'訂單已處理'];
            }
            $order->status = -1;
            $order->save();
            if($order->coupons_id) {
                (new \App\Coupons())->where('id',$order->coupons_id)->update(['status'=>1]);
            }

            \DB::commit();
            $this->updateStoreOrderNumber($order->store_id);
            return ['success'=>true,'store_id' => $order->store_id,'id' => $id];

        }catch (\Exception $e){
            \DB::rollback();
            \Log::error("cancel order fail: ".$e->getMessage().', '.$e->getLine());
            return ['success'=>false,'msg'=>'取消失敗'];
        }
    }

    /**
     * 評論
     */
    public function addComment($credentials,$member_id){
        $info = $this->getOrderById(intval($credentials['order_id']));
        $data = [
            'store_id' => $info->store_id,
            'order_id' => intval($credentials['order_id']),
            'member_id' => $member_id,
            'content' => isset($credentials['content']) ? trim($credentials['content']) : '',
            'level' => $credentials['level'],
            'nickname' => $this->getMemberInfoById($member_id),
            'created_at'=>time(),
            'image'=> (isset($credentials['image']) && $credentials['image']) ? $credentials['image'] : ''
        ];
        \DB::beginTransaction();
        try{
            Comment::unguard();
            $comment = Comment::create($data);
            Comment::reguard();
            $this->updateStoreLevel($info->store_id);
            $this->updateStoreCommentNumber($info->store_id);
            $info->is_evaluate = 1;
            $info->save();
            \DB::commit();
            return true;
        }catch (\Exception $e){
            \DB::rollback();
            \Log::error("comment fail: ".$e->getMessage().', '.$e->getLine());
            return false;
        }
    }

    //消费记录列表按时间
    public function orderListByTime($member_id,$per_page,$month){
        $where = array(
            'orders.member_id'=>$member_id,
//            'orders.status'=>1
        );
        if($month){
            $where['month'] = $month;
        }
        return (new \App\Order())->join('store','store.id','=','orders.store_id')
                 ->where($where)
                   ->select([
                    'orders.id',
                    'orders.date',
                    'orders.store_name',
                    'orders.amount',
                    'orders.created_at',
                    'orders.status',
                    'store.image',
                    'is_evaluate'
                ])->orderBy('orders.created_at','DESC')
                ->paginate($per_page);
    }

    //消费记录列表按店铺
    public function orderListByStore($member_id,$per_page){
        return Order::where(['member_id'=>$member_id,'status'=>1])->select([
                'id',
                'store_id',
                'store_name',
                'created_at',
                'is_evaluate'
            ])->groupBy('store_id')
            ->orderBy('created_at','DESC')
            ->paginate($per_page);
    }

    //店铺内消费记录
    public function detailsByStore($member_id,$store_id){
      return (new \App\Order())->join('store','orders.store_id','=','store.id')
                        ->where(['orders.member_id'=>$member_id,'orders.status'=>1,'orders.store_id'=>$store_id])
                        ->select([
                            'orders.id',
                            'orders.date',
                            'orders.store_name',
                            'orders.amount',
                            'orders.created_at',
                            'store.image',
                            'is_evaluate'
                       ])
                        ->orderBy('orders.created_at','DESC')
                        ->get();
    }

    //订单消费记录详情
    public function view($id){
        $data = Order::where('id',$id)->select([
                    'id',
                    'order_no',
                    'store_id',
                    'store_name',
                    'amount',
                    'credits',
                    'coupons_money',
                    'status',
                    'checkout_at',
                    'checkout_user_id',
                    'refund_at',
                    'refund_user_id',
                    'created_at',
                     'mfixedrate',
                      'mrate',
                ])->first();
        if($data){
            $data->checkout_user = $data->checkout_user_id ? $this->getStoreUserInfoById($data->checkout_user_id) : $data->checkout_user_id;
            $data->refund_user = $data->refund_user_id ? $this->getStoreUserInfoById($data->refund_user_id) : $data->refund_user_id;

            $comment = Comment::where('order_id',$id)->first([
                'content',
                'level',
                'is_reply',
                'reply_content',
                'created_at',
                'updated_at',
            ]);
            $data->has_comment = 0;
            $data->comment = ['content'=>'', 'level'=>'', 'is_reply'=>'', 'reply_content'=>'','created_at'=>'','updated_at'=>''];
            if($comment){
                $data->has_comment = 1;
                $data->comment = $comment;
            }
        }
        return $data;
    }

    //退货
    public function refund($credentials){
        $id = intval($credentials['order_id']);
        return Order::where('id',$id)->update(['status'=>2]);
    }

    public function getStoreInfo($code,$id){
        if($code){
            $code = strtoupper(trim($code));
            return Store::where('code',$code)->first(['id','name','status']);
        }
        if($id){
            return Store::where('id',$id)->first(['id','name','status']);
        }
        return null;

    }

    /**
     * 待回贈訂單詳情
     */
    public function rebateOrder($member_id,$per_page){
        return (new \App\Order())->join('rebate_orders','orders.id','=','order_id')
                        ->where(['orders.member_id'=>$member_id,'orders.status'=>1])
                        ->where('cycle_status','!=',0)
                        ->where('cycle_status','!=',3)
                        ->select([
                            'rebate_orders.*',
                            'store_id',
                            'store_name',
                            'amount',
                            'credits',
                            'coupons_money',
                            'orders.created_at'
                        ])
                        ->orderBy('orders.created_at','DESC')
                        ->paginate($per_page);

    }

    /**
     * 我的積分使用情況
     */
    public function usage($member_id,$per_page){
       return (new \App\Order())->where('member_id',$member_id)
                        ->where('status',1)
                        ->where('credits','>',0)
                        ->select([
                            'id',
                            'order_no',
                            'store_id',
                            'store_name',
                            'amount',
                            'credits',
                            'coupons_money',
                            'orders.created_at'
                        ])
                        ->orderBy('orders.created_at','DESC')
                        ->paginate($per_page);
    }

    /**
     * 更新店鋪人氣次數
     */
    public function updateStoreNumber($code){
        $data = Store::where('code',$code)->first(['id']);
        if($data){
            $result = (new \App\StoreData)->where('store_id',$data->id)->first();
            if($result){
                $result->number = $result->number + 1;
                $result->save();
            }else{
                (new \App\StoreData)->insertGetId(['store_id'=>$data->id,'number'=>1]);
            }
        }

    }
    /**
     * 更新店鋪评论次數
     */
    private function updateStoreCommentNumber($store_id){
        $data = (new \App\StoreData)->where('store_id',$store_id)->first();
        if($data){
            $data->comment_number = $data->comment_number + 1;
            $data->save();
        }else{
            (new \App\StoreData)->insertGetId(['store_id'=>$store_id,'comment_number'=>1]);
        }
    }
    /**
     * 更新店鋪评分
     */
    private function updateStoreLevel($store_id){
        $level = Comment::where('store_id',$store_id)->whereBetween('created_at',[time()-30*24*3600,time()])->avg('level');
        $data = (new \App\StoreData)->where('store_id',$store_id)->first();
        if($data){
            $data->level = $level;
            $data->save();
        }else{
            (new \App\StoreData)->insertGetId(['store_id'=>$store_id,'level'=>$level]);
        }
    }

    /**
     * 更新店鋪人氣次數,一個月以內訂單數
     */
    private function updateStoreOrderNumber($store_id){
        $count = Order::where('store_id',$store_id)
                        ->where('status','>=',0)
                        ->whereBetween('created_at',[date('Y-m-d H:i:s',time()-30*24*3600),date('Y-m-d H:i:s',time())])
                        ->count();
        $data = (new \App\StoreData())->where('store_id',$store_id)->first();
        if($data){
            $data->order_number = $count;
            $data->save();
        }else{
            (new \App\StoreData())->insertGetId(['store_id'=>$store_id,'order_number'=>$count]);
        }
    }
    /**
     * 計算該店鋪今日第幾單
     */
    private function total($store_id){
        $date = date('Y-m-d');
        $count = (new \App\Order())->where(['store_id'=>$store_id,'date'=>$date])->count();
        return ++$count;
    }

    /**
     * 未綁定會員，默認下單綁定店家
     */

    private function bind_store($store,$member_id){
        $user = (new \App\User())->where('id',$member_id)->first();
        if(!$user->promo_code){
            $user->promo_code = $store->code;
            $user->code_type = 2; //1網紅或會員，2店家
            (new \App\Invitelog())->insert(['promo_uid'=>$store->id,'invite_uid'=>$member_id,'invite_date'=>date('Y-m-d H:i:s'),'invite_type'=>2]);
            $user->save();
        }
    }

    /**
     * 消费足迹
     */
    private function contact_store($store_id,$member_id){
        $data = (new \App\ContactStore())->where(['member_id'=>$member_id,'store_id'=>$store_id])->first();
        if($data){
            $data->created_at = date('Y-m-d H:i:s');
            $data->number = $data->number + 1;
            $data->save();
        }else{
            (new \App\ContactStore())->insert(['member_id'=>$member_id,'store_id'=>$store_id,'number'=>1,'created_at'=>date('Y-m-d H:i:s')]);
        }
    }

    public function query($code,$uid){

        $data = (new \App\User())->where('invite_code',$code)->first(['avatar','nickname','id']);
        if($data){
            $data->avatar = empty($data->avatar)?url('/images/avatar/').'/avatar.png': BaseService::image($data->avatar);
            return ['success'=>true,'data'=>$data];

        }else{
            return ['success'=>false,'msg'=>'邀請碼錯誤','flag'=>0];
        }


    }
}
