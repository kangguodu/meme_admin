<?php

namespace App\Api\V1\Repositories;


use App\Api\V1\Services\BaseService;
use App\User;
use App\Verification;
use App\Credits;
use App\Api\Merchant\Services\NotificationService;

class AuthRepository
{
    private $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

  
    public function signUp($request,$zone){
        $userData = array(
            'phone' => trim($request['phone']),
            'user_type'=>1,
            'zone' => $zone,
        );

        if(isset($request['zone']) && trim($request['zone'])){
            $userData['zone'] = trim($request['zone']);
        }
        User::unguard();
        $user = User::create($userData);
        User::reguard();
        if(!$user->id) {
            return ['success'=>false];
        }else{
            $this->initCredit($user->id);
            return ['success'=>true,'id'=>$user->id];
        }
    }

    public function is_singup($request,$zone){

        if(isset($request['zone'])){
            $zone = trim($request['zone']);
        }
        $where = array(
            'phone' => trim($request['phone']),
        );
        if($zone){
            $where['zone'] = $zone;
        }
        $user = User::where($where)->first();
        if($user){
            return true;
        }
        return false;
    }

    public function login($credentials,$zone){
        $where = array(
            'phone'  => trim($credentials['phone']),
        );
        if(isset($credentials['zone']) && trim($credentials['zone'])){
            $zone = trim($credentials['zone']);
        }
        if(isset($credentials['type']) && $credentials['type']){
            $where['user_type'] = $credentials['type'];
        }
        if($zone){
            $where['zone'] = $zone;
        }
        return User::where($where)->first();
    }

    public function fillInfo($credentials,$zone){
        $data = [
            'phone' => trim($credentials['phone']),
            'zone' => (isset($credentials['zone']) && trim($credentials['zone'])) ? trim($credentials['zone']) : $zone,
            'username'  => (isset($credentials['username']) && trim($credentials['username'])) ? trim($credentials['username']) : trim($credentials['phone']),
            'nickname'  => (isset($credentials['nickname']) && trim($credentials['nickname'])) ? trim($credentials['nickname']) : trim($credentials['phone']),
            'password' => $credentials['password'],
            'user_type' => 1,
        ];
        $promo_code = '';
        if(isset($credentials['promo_code']) && trim($credentials['promo_code'])){
            $promo_code = strtoupper(trim($credentials['promo_code']));
        }
        if(isset($credentials['user_type']) && intval($credentials['user_type'])){
            $data['user_type'] = intval($credentials['user_type']);
            $data['honor'] = $data['user_type']==1 ? 0 : 1;
        }
        User::unguard();
        $user = User::create($data);
        User::reguard();
        if($user->id){
            $this->initCredit($user->id);
//            BaseService::operation($user->id,1);   //默認20元優惠券，改為登陸后點擊領取
            if($promo_code){
                $info = User::where(['invite_code'=>$promo_code])->first();  //邀請方
                $store = (new \App\Store())->where('code',$promo_code)->first();
                if($info){
                    if($info->user_type ==2){
                        $this->invitelog($info->id,$user->id,1);
                    }
                    $info->invite_count = $info->invite_count + 1;
                    $info->save();
                    $user->promo_code = $promo_code;
                    $user->save();
                    if(isset($credentials['source']) && intval($credentials['source'])==1){
                        BaseService::operation($info->id,2); //10元優惠券

                        $notificationService = new NotificationService();
                        $notificationService->sendProcessNotification([
                            'msg' => '恭喜您獲得10元優惠券',
                            'member_id' => $info->id,
                        ],'coupons');

                        (new \App\Notice())->insert([
                            'title' => '恭喜您獲得10元優惠券',
                            'description' => '分享好友邀請註冊，獲得10元優惠券',
                            'type_id' => 2,
                            'member_id' => $info->id,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
                    }

                }else if($store){
                    $user->promo_code = $promo_code;
                    $user->code_type = 2;
                    $this->invitelog($store->id,$user->id,2);
                    $user->save();
                }

            }
            $user->invite_code = BaseService::inviteCode($user->id);   //生成invite_code

        }
        return $user;
    }

    public function updatePassword($credentials,$zone){
        $where['phone'] = trim($credentials['phone']);
        if(isset($credentials['zone']) && trim($credentials['zone'])){
            $zone = trim($credentials['zone']);
        }
        if($zone){
            $where['zone'] = $zone;
        }
        $user = User::where($where)->first();
        if($user){
            $user->password = $credentials['password'];
            $user->save();
            return true;
        }
        return false;
    }


    public function checkCode($credentials,$zone){
        $phone = trim($credentials['phone']);
        if(isset($credentials['zone']) && trim($credentials['zone'])){
            $zone = trim($credentials['zone']);
        }
        $where = ['verification_account'=>$phone,'verification_type'=>1];
        if($zone){
            $where['zone'] = $zone;
        }
        $verification = Verification::where($where)->first();

        if($verification == null || $verification->verification_code != trim($credentials['code'])){
            return false;
        }
        Verification::where($where)->delete();
        return true;
    }


    private function operation($member_id,$coupons_id){

        \DB::beginTransaction();
        try{
            $list = (new \App\CouponsRelease())->where('id',$coupons_id)->first();
            if($list) {
                if ($list->number <= 0) {
                    return ['success'=>false,'msg'=>'優惠券已領取完'];
                }
                if($list->start_at > date('Y-m-d')){
                    return ['success'=>false,'msg'=>'領取優惠券活動尚未開始'];
                }
                if($list->expire_at < date('Y-m-d') && $list->expire_at && $list->expire_at!='0000-00-00'){
                    return ['success'=>false,'msg'=>'優惠券領取已結束'];
                }
                $data = [
                    'coupons_id'  => $coupons_id,
                    'member_id'   => $member_id,
                    'status' => 1,
                    'receive_at'=>time(),
                    'start_at' => strtotime(date('Y-m-d')),
                    'expire_time'=> strtotime(date('Y-m-d')) +  $list->valid_time
                ];

                (new \App\Coupons())->insertGetId($data);
                $list->number -= 1;
                $list->save();
                $this->coupons_receive_log($coupons_id,$member_id);
                \DB::commit();
                return ['success'=>true,'msg'=>'領取成功'];
            }
            return ['success'=>false,'msg'=>'優惠券尚未發行'];


        }catch (\Exception $e){
            \DB::rollback();
            \Log::error("receive coupons fail by invite: ".$e->getMessage().', '.$e->getLine());
            return ['success'=>false,'msg'=>'系統出錯'];
        }
    }

    private function invitelog($promo_uid,$invite_uid,$type){
        $data = [
            'promo_uid' => $promo_uid,
            'invite_uid' => $invite_uid,
            'invite_date' => date('Y-m-d H:i:s'),
            'invite_type' => $type
        ];
        return (new \App\Invitelog())->insert($data);

    }

    private function initCredit($uid){
        $data = [
            'member_id' => $uid,
            'total_credits' => 0,
        ];
        (new \App\Credits())->insert($data);
    }


    public function getById($id){
        return $this->user->join('member_credits','member_id','member.id')->where('member.id', $id)->first([
            'member.*',
            'total_credits',
            'grand_total_credits',
            'wait_total_credits',
            'promo_credits_total',
            'promo_credits',
        ]);
       
    }

    public function update($credentials,$id){
        $data = [];

        if(isset($credentials['nickname']) && trim($credentials['nickname'])){
            $data['nickname'] = trim($credentials['nickname']);
        }

        if(isset($credentials['username']) && trim($credentials['username'])){
            $data['username'] = $credentials['username'];
        }

        if(isset($credentials['avatar']) && $credentials['avatar']){
            $i = strpos(trim($credentials['avatar']),'upload');
            $avatar = substr(trim($credentials['avatar']),$i);
            $data['avatar'] = $avatar;
        }
        if(isset($credentials['birthday']) && $credentials['birthday']){
            $data['birthday'] = trim($credentials['birthday']);
        }
        if(isset($credentials['gender']) && $credentials['gender']){
            $data['gender'] = intval($credentials['gender']);
        }
        if(empty($data)){
            return 0;
        }
        return $this->user->where('id',$id)->update($data);
    }

    //是否設定安全碼
    public function isSetPassword($id){
        $data = $this->user->where('id',$id)->select('secure_password')->first();

        if($data->secure_password){
            return true;
        }
        return false;
    }

    //修改手机号
    public function updatePhone($id,$credentials,$zone){

        if(isset($credentials['zone']) && trim($credentials['zone'])){
            $zone = trim($credentials['zone']);
        }
        $data = array(
            'phone' => trim($credentials['phone']),
            'zone' => $zone,
        );
        return $this->user->where('id',$id)->update($data);
    }

    //修改安全碼
    public function updatePayPassword($id,$secure_password){
        return $this->user->where('id',$id)->update(['secure_password'=>\Hash::make($secure_password)]);
    }

    public function inviteNumber($uid){
        return (new \App\Invitelog())->where('promo_uid',$uid)->where('invite_type',1)->count();
    }

    public function query($code,$uid,$type){
        if($type ==1){
            $data = $this->user->where('invite_code',$code)->first(['avatar','nickname','id']);
            if($data){
                $data->avatar = empty($data->avatar)?url('/images/avatar/').'/avatar.png': BaseService::image($data->avatar);
                return ['success'=>true,'data'=>$data];

            }else{
                return ['success'=>false,'msg'=>'邀請碼錯誤'];
            }
        }else{
            $store = (new \App\Store())->where('code',$code)->first();
            if($store){
                return ['success'=>false,'data'=>$store];
            }else{
                return ['success'=>false,'msg'=>'店鋪不存在'];
            }
        }


    }

    public function bind($inviteid,$id){
        \DB::beginTransaction();
        try{
            $userinfo = User::where(['id'=>$id])->first();  //自己
            $result = 1;

            $info = User::where(['id'=>$inviteid])->first();  //邀請方
            if($info->user_type == 2){
                $result = $this->invitelog($inviteid,$id,1);
            }
            $info->invite_count = $info->invite_count + 1;

            $userinfo->promo_code = $info->invite_code;

            $userinfo->save();
            $info->save();
            \DB::commit();
            return ['success'=>$result];

        }catch (\Exception $e){
            \DB::rollback();
            \Log::error("invite log fail: ".$e->getMessage().', '.$e->getLine());
            return false;
        }

    }

    public function getPromoInfo($code){
        $data = (new \App\User())->where('invite_code',$code)->first(['avatar','nickname','id']);
        if($data){
            return $data;
        }
        $data = (new \App\Store())->where('code',$code)->first(['image','name','id']);
        if($data){
            $data->avatar = $data->image;
            $data->nickname = $data->name;
            return $data;
        }
    }
}