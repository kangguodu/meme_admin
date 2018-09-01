<?php
namespace App\Api\Merchant\Controllers;

class AuthBaseController extends BaseController
{
    protected function guard()
    {
        return Auth::guard('stores');
    }

    public function getCurrentAuthUser(){
        $user = $this->auth->user();
        return $user;
    }
    public function noneLoginResponse(){
        return $this->responseError("沒有權限訪問",$this->status_bad_request,401);
    }

    public function getCurrentUserStoreId(){
        $user = $this->getCurrentAuthUser();
        return isset($user->store_id)?$user->store_id:0;
    }

    public function getUserStoreId($user){
        return isset($user->store_id)?$user->store_id:0;
    }

}