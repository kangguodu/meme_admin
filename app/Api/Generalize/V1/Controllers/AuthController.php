<?php
/**
 * Created by PhpStorm.
 * User: zzq
 * Date: 18-6-20
 * Time: 上午9:50
 */

namespace App\Api\Generalize\V1\Controllers;



use App\Api\Generalize\V1\Functions\Functions;
use App\Member;
use App\Verification;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends BaseController
{
    public function login(Request $request)
    {
        $credentials = $request->only(['phone','code']);

        $rule = ['phone'=>'required|min:6','code'=>'required|min:4'];
        $validator = Validator::make($credentials,$rule);
        if ($validator->fails()){
            return $this->responseError($validator->errors());
        }

        $verifyCond = [
                'verification_account'=>$credentials['phone'],
                'verification_code'=>$credentials['code'],
        ];
        if ($verify = Verification::where($verifyCond)->first()){
            $verify->delete();//刪除驗證碼

            $sendTime = $verify->send_at;
            if ((time()-$sendTime)>60*5)
                return $this->responseError('invalid sms code');

            $cond = [
                'phone'=>$credentials['phone'],
                'user_type'=>2,
            ];
            $user = Member::where($cond)->first();
            if ($user){
                $token = JWTAuth::fromUser($user);
                $user = $user->toArray();
                $user['token'] = $token;
                return $this->success($user);
            }else{
                return $this->notFoundResponse($user);
            }
        }else{
            return $this->responseError('sms code not valid',40001,400);
        }
    }
    public function logout()
    {
        try{
            $token = JWTAuth::getToken();
            if ($token)
                JWTAuth::setToken($token)->invalidate();
            return $this->success('logout success');
        }catch (\ErrorException $e){
            return $this->responseError('logout',200);
        }
    }
    public function sms(Request $request)
    {
        $phone = $request->phone;
        if (Functions::checkPhone($phone)){
            $cond = ['phone'=>$phone,'user_type'=>2,];
            $obj = Member::where($cond)->first();
            if ($obj){
                $code = Verification::makeVerifyCode($phone);
                return $this->success(['code'=>$code]);
            }else{
                return $this->responseError('user not exist');
            }
        }else{
            return $this->responseError('phone number not valid');
        }
    }
}