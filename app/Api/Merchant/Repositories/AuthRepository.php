<?php
namespace App\Api\Merchant\Repositories;

use App\Api\Merchant\Services\AuthService;
use App\Store;
use App\StoreUser;
use App\Verification;
use App\Sms;
use Mail;
class AuthRepository
{
    public function checkPhoneExist($phone){
        $storeModel = new StoreUser();
        return $storeModel->where('mobile','=',$phone)
            ->orWhere('email','=',$phone)
            ->first([
                'id',
                'password',
                'mobile',
                'email',
                'email_status'
            ]);
    }

    public function getUserByPhoneOrEmail($param){
        $storeModel = new StoreUser();
        return $storeModel->where('mobile','=',$param)
            ->orWhere('email','=',$param)
            ->first([
                'id',
                'password',
                'mobile',
                'email',
                'email_status'
            ]);
    }

    public function loginGetUserByAccount($param){
        $storeModel = new StoreUser();
        return $storeModel->where('mobile','=',$param)
            ->orWhere('email','=',$param)
            ->first([
                'id',
                'nickname',
                'store_id',
                'password',
                'mobile',
                'email',
                'email_status',
                'permission',
                'token',
                'gender',
                'super_account',
                'position',
                'menus'
            ]);
    }


    public function getVerifyActionCode($account){
        $verification = new Verification();
        $info = $verification->where('verification_account',$account)
            ->orderBy('id','DESC')
            ->first([
                'id',
                'verification_account',
                'verification_code',
                'send_at'
            ]);
        if($info){
            return $info;
        }else{
            return null;
        }
    }

    public function loginGetUserByPhone($phone){
        $storeModel = new StoreUser();
        return $storeModel->where('mobile','=',$phone)
            ->first([
                'id',
                'password',
                'mobile',
                'email',
                'email_status'
            ]);
    }

    public function getStoreById($id){
        return (new Store())->where('id',$id)
            ->first([
                'id',
                'store_id',
                'super_uid',
                'name',
                'branchname',
                'service_status',
                'city',
                'district',
                'address',
            ]);
    }

    public function updateProfile($user,$requestParam){
        $updateData = array();
        $updateFields = AuthService::getUpdateProfileFields();
        foreach ($updateFields as $key=>$value){
            if(isset($requestParam[$value])){
                $updateData[$value] = $requestParam[$value];
            }
        }
        $updateData = AuthService::getUpdateProfileEmailStatus($user,$updateData);
        if(count($updateData) > 0){
            (new StoreUser())->where('id',$user->id)->update($updateData);
        }
    }

    public function updatePassword($credentials){
        if($credentials['type'] == 1){
            return (new StoreUser())->where('mobile',trim($credentials['phone']))->update(['password'=>\Hash::make($credentials['password'])]);
        }
        return (new StoreUser())->where('email',trim($credentials['phone']))->update(['password'=>\Hash::make($credentials['password'])]);

    }
    public function checkCode($credentials){
        $phone = trim($credentials['phone']);

        $verification = (new Verification())->where('verification_account',$phone)
            ->where('verification_type','=',$credentials['type'])
            ->first();

        if($verification == null || $verification->verification_code != trim($credentials['code'])){
            return false;
        }
        try{
            (new Verification())->where('verification_account','=',$phone)->delete();
        }catch (\Exception $e){
            \Log::error("刪除驗證碼失敗".$e->getFile().' '.$e->getLine());
        }

        return true;
    }

    public function updatePhone($id,$credentials){
        return (new StoreUser())->where('id',$id)->update(['mobile'=>$credentials['phone']]);
    }

    public function getSimpleUserList($store_id){
        return (new StoreUser())->where('store_id','=',$store_id)
            ->select([
                'id',
                'nickname'
            ])->get();
    }


    public function getUserListByStoreId($store_id)
    {
        return (new StoreUser())->where('store_id', '=', $store_id)
            ->select([
                'id',
                'nickname',
                'email',
                'mobile',
                'zone',
                'permission',
                'email_status',
                'gender',
                'super_account',
                'position',
                'menus'
            ])
            ->orderBy('id', 'ASC')->get();
    }

    public function sendsms($credentials){

        $smsModel = new Sms();
        $code = $smsModel->crateSmsCode();

        $data = [
            'verification_account' => trim($credentials['phone']),
            'verification_code' => $code,
            'send_at'=>time()
        ];
        if($credentials['type'] ==1){
            $data['verification_type'] = 1;
            $msg = urldecode("您的驗證碼為 {$code}，請於3分鐘內在APP上輸入此驗證碼，以確保您的會員身份並維護相關權益。歡迎你/妳加入 MeMeCoins 。");
            $smsModel->sendSms($msg,trim($credentials['phone']));

        }else{
            $data['verification_type'] = 2;
            $email = trim($credentials['phone']);
            Mail::send('email.test',['code'=>$code],function($message) use ($email){
                $to = $email;
                $message ->to($to)->subject('memecoins 店家郵箱驗證');
            });
        }
        $result = (new Verification())->where('verification_account',trim($credentials['phone']))->first();
        if($result){
            $result->verification_code = $code;
            $result->send_at = time();
            $result->save();
        }else{
            (new \App\Verification())->insertGetId($data);
        }
        return ['code'=>$code];

    }

    public function updateEmail($id,$email){
       return (new StoreUser())->where('id',$id)->update(['email'=>$email,'email_status'=>'verified']);
    }

    public function updatePermission($store_id,$user_id,$permission){
        return (new StoreUser())->where('store_id','=',$store_id)
            ->where('id','=',$user_id)
            ->update(['permission'=>$permission]);
    }



}