<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/21
 * Time: 9:35
 */

namespace App\Api\V1\Repositories;

use App\Coupons;
use App\Store;
use App\Activity;
use App\Api\V1\Services\BaseService;

class CouponsRepository
{
    public function index($store_id,$uid,$money,$use_type){
        $model = (new \App\Coupons())->leftJoin('coupons_release','coupons_id','=','coupons_release.id')
                                    ->select([
                                            'coupons.id',
                                            'coupons.status',
                                            'coupons.start_at',
                                            'coupons.expire_time',
                                            'title',
                                            'description',
                                            'store_id',
                                            'activity_id',
                                            'money',
                                            'discount',
                                            'type',
                                            'conditions',
                                            'use_type',
                                            'number',
                                            'limit_number',
                                            'limit_receive_days',
                                            'limit_receive',
                                    ]
                                    )
                                    ->where(['member_id'=>$uid]);

        if($use_type){
            $model = $model->where(['coupons_release.use_type'=>$use_type,'coupons.status'=>1]);
        }else{
            $model = $model->where('coupons.status','!=',0);
        }
        $data =$model ->where('coupons.expire_time','>=',time())
            ->orderBy('coupons.status','ASC')
            ->orderBy('coupons.start_at','ASC')
            ->orderBy('coupons.expire_time','DESC')
            ->get();
        if($data){
            foreach ($data as $v){
                $remark = [];//備註原因
                if($v->type==1){
                    $v->desc = '通用';
                }else if($v->type==2){
                    $v->desc = $this->getStoreNameById($v->store_id).'專用';
                }
                $status = 1;  //可使用
                if($store_id){
                    if($store_id != $v->store_id && $v->store_id){
                        $status = 2;
                        $remark[] = '僅限'.$this->getStoreNameById($v->store_id).'使用';
                    }
                }else if($v->start_at>=time() && $v->start_at){
                    $remark[] = '未到使用時間';
                    $status = 2;
                }
                if($money){
                  if($money < $v->conditions && $v->conditions){
                        $remark[] = '滿'.$v->conditions.'元可使用';
                      $status = 2;
                    }
                }
                if($v->status == -1){
                    $status = -1;
                }
                $v->status = $status;
                $v->remark = $remark;
                $v->store_name = $this->getStoreNameById($v->store_id);
                $v->start_at = $v->start_at ? date('Y-m-d',$v->start_at) : '';
                $v->expire_time = $v->expire_time ? date('Y-m-d',$v->expire_time) : '';
            }
        }
        $data = collect($data)->sortBy('status')->values()->all();
        return $data;
    }

    /**
     * @param $id 優惠券id
     * @param $member_id
     * 獲取優惠券，包括優惠券和直接送蜂幣
     */

    public function create($coupons_id,$member_id){
        $data = [
            'coupons_id'  => $coupons_id,
            'member_id'   => $member_id,
            'status' => 1,
            'receive_at'=>time(),
            'start_at' => strtotime(date('Y-m-d')),
        ];
        \DB::beginTransaction();
        try{
            $list = (new \App\CouponsRelease())->where('id',$coupons_id)->first();
            if($list){
                if($list->start_at > date('Y-m-d')){
                    return ['success'=>false,'msg'=>'領取優惠券活動尚未開始'];
                }
                if($list->expire_at < date('Y-m-d') && $list->expire_at && $list->expire_at!='0000-00-00'){
                    return ['success'=>false,'msg'=>'優惠券領取已結束'];
                }
                if($list->number<=0){
                    return ['success'=>false,'msg'=>'優惠券已領取完'];
                }
                $where = [
                    'coupons_id'=>$coupons_id,
                    'member_id'=>$member_id,
                    'type'=>1
                ];
                $limit_receive = (new \App\CouponsReceiveLog())->where($where)->count();
                $limit_receive_days = (new \App\CouponsReceiveLog())->where($where)
                    ->where(['receive_date'=>date('Y-m-d')])
                    ->count();
                if($limit_receive >= $list->limit_receive){
                    return ['success'=>false,'msg'=>'您已領取過此優惠券'];
                }

                if($limit_receive_days >= $list->limit_receive_days){
                    return ['success'=>false,'msg'=>'您今日已領取過此優惠券'];
                }
                $data['expire_time'] = $data['start_at'] + $list->valid_time*24*3600;
                $content = '';
                if($list->use_type == 2){   //送優惠券
                    $id = (new \App\Coupons())->insertGetId($data);
                    $content = '恭喜您獲得一張'.($list->money>0 ? $list->money.'元' : $list->discount.'折').'優惠券';
                }else if($list->use_type == 1){  //直接送蜂幣

                    $this->coupons_credits($list->money,$member_id);
                    $content = '恭喜您獲得'.$list->money.'蜂幣';
                }


                $list->number -= 1;
                $list->save();
                BaseService::coupons_receive_log($coupons_id,$member_id);
                (new \App\Notice())->insert([
                    'title' =>$content,
                    'description' => $content,
                    'type_id' => 2,
                    'member_id' => $member_id,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
                \DB::commit();

                return ['success'=>true,'msg'=>'領取成功','content'=>$content];
            }
            return ['success'=>false,'msg'=>'優惠券尚未發行'];
        }catch (\Exception $e){
            \DB::rollback();
            \Log::error("receive coupons fail: ".$e->getMessage().', '.$e->getLine());
            return ['success'=>false,'msg'=>'系統出錯'];
        }
    }
    /**
     * 註冊后得到優惠券
     */
    public function getCoupons($coupons_id,$member_id){
        if(!$member_id){
            return ['success'=>false,'msg'=>'會員不存在'];
        }
        return BaseService::operation($member_id,$coupons_id);
    }

    /**
     * 分享后得到優惠券
     */
    public function getShareCoupons($coupons_id,$promo_code){
        $user = (new \App\User())->where('invite_code',$promo_code)->first();
        if(!$user){
            return ['success'=>false,'msg'=>'會員不存在'];
        }
        return BaseService::operation($user->id,$coupons_id);
    }




    /**
     * 發行優惠券
     */
    public function release($credentials){

        $data['title'] = trim($credentials['title']);
        if(isset($credentials['description']) && trim($credentials['description'])){
            $data['description'] = trim($credentials['description']);
        }
        if(isset($credentials['store_id']) && intval($credentials['store_id'])){
            $data['store_id'] = intval($credentials['store_id']);
        }
        if(isset($credentials['activity_id']) && intval($credentials['activity_id'])){
            $data['activity_id'] = intval($credentials['activity_id']);
        }
        if(isset($credentials['money']) && intval($credentials['money'])){
            $data['money'] = intval($credentials['money']);
        }
        if(isset($credentials['discount']) && intval($credentials['discount'])){
            $data['discount'] = intval($credentials['discount']);
        }
        if(isset($credentials['start_at'])){
            $data['start_at'] = $credentials['start_at'];
        }
        if(isset($credentials['expire_at'])){
            $data['expire_at'] = $credentials['expire_at'];
        }
        if(isset($credentials['release_type']) && intval($credentials['release_type'])){
            $data['release_type'] = intval($credentials['release_type']);
        }


        $data['use_type'] = intval($credentials['use_type']);

        $data['valid_time'] = intval($credentials['valid_time']);

        $data['conditions'] = intval($credentials['conditions']);

        $data['type'] = intval($credentials['type']);

        $data['number'] = intval($credentials['number']);

        $data['limit_number'] = intval($credentials['limit_number']);

        $data['limit_receive_days'] = intval($credentials['limit_receive_days']);

        $data['limit_receive'] = intval($credentials['limit_receive']);

        return (new \App\CouponsRelease())->insertGetId($data);


    }

    public function check($code){
        $data = (new \App\CouponsRelease())->where('id',1)->first();
        $is_over = 0;
        if($data){
            if($data->start_at > date('Y-m-d')){
                return ['msg'=>'活動尚未開始','success'=>false,'flag'=>1,'is_over'=>$is_over];
            }
            if($data->expire_at < date('Y-m-d')){
                $is_over = 1;
                return ['msg'=>'活動已結束','success'=>false,'flag'=>2,'is_over'=>$is_over];
            }
        }else{
            return ['success'=>false,'msg'=>'活動不存在','flag'=>3,'is_over'=>$is_over];
        }
        if($code){
            $result = (new \App\User())->where('invite_code',$code)->first();
            if(!$result){
                return ['success'=>false,'msg'=>'會員不存在','flag'=>4,'is_over'=>$is_over];
            }
            $count = (new \App\CouponsReceiveLog())->where('member_id',$result->id)->where('coupons_id',1)->count();
            if($count > 0){
                return ['success'=>false,'msg'=>'您已領取過','flag'=>5,'is_over'=>$is_over];
            }
        }

        return ['success'=>true];
    }


    //老版本的，默認是我的優惠券里已顯示，但需要點擊領取才算領取成功
//    public function receive($id,$member_id){
//        $data = (new \App\Coupons())->where(['id'=>$id,'member_id'=>$member_id])->first();
//        if($data){
//            \DB::beginTransaction();
//            try{
//
//                    if($data->use_type == 1){
//                        $result = (new \App\MemberCredits())->where('member_id',$member_id)->first();
//
//                        $log = [
//                            'type'  => 1,
//                            'trade_type' => '優惠券領取',
//                            'date' => date('Y-m-d'),
//                            'log_date' => date('Y-m-d H:i:s'),
//                            'log_no' => date('ymdHis').uniqid().mt_rand(1,6),
//                            'amount' => $data->money,
//                            'balance' => $result->total_credits,
//                            'status' => 1,
//                            'remark' => '領取優惠券獲得'.$data->money.'蜂幣',
//                            'member_id' => $member_id,
//                        ];
//
//                        $result->total_credits = $result->total_credits + $data->money;
//
//                        $result->save();
//
//                        (new \App\MemberCreditsLog())->insertGetId($log);
//
//                        (new \App\Coupons())->where('id',$id)->delete();
//
//                }else{
//                        $data->status = 1;
//                        $data->save();
//                }
//                \DB::commit();
//                return true;
//            }catch (\Exception $e){
//                \DB::rollback();
//                \Log::error("coupons receive fail: ".$e->getMessage().', '.$e->getLine());
//                return false;
//            }
//        }
//
//        return false;
//
//    }


    private function getStoreNameById($id){
        $data = Store::where('id',$id)->first(['name']);
        if($data){
            return $data->name;
        }
        return '';
    }
    private function getActivityNameById($id){
        $data = Activity::where('id',$id)->first(['title']);
        if($data){
            return $data->title;
        }
        return '';
    }

    /**
     * @param $id  我的優惠券id
     * @param $member_id
     * 直接送蜂幣
     */
    private function coupons_credits($money,$member_id){

        \DB::beginTransaction();
        try {

            $result = (new \App\MemberCredits())->where('member_id', $member_id)->first();

            $log = [
                'type' => 1,
                'trade_type' => '註冊活動領取蜂幣',
                'date' => date('Y-m-d'),
                'log_date' => date('Y-m-d H:i:s'),
                'log_no' => date('ymdHis') . uniqid() . mt_rand(1, 6),
                'amount' => $money,
                'balance' => $result->total_credits,
                'status' => 1,
                'remark' => '領取優惠券獲得' . $money . '蜂幣',
                'member_id' => $member_id,
            ];

            $result->total_credits = $result->total_credits + $money;

            $result->save();

            (new \App\MemberCreditsLog())->insertGetId($log);

            \DB::commit();

            return true;

        }catch (\Exception $e){
            \DB::rollback();
            \Log::error("receive coupons fail by use_type is 1: ".$e->getMessage().', '.$e->getLine());
            return false;
        }

    }


}