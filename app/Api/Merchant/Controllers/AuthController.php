<?php
namespace App\Api\Merchant\Controllers;

use App\Api\Merchant\Transformers\UserListTransformer;
use JWTAuth;
use JWTFactory;
use Validator;
use Carbon\Carbon;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Api\Merchant\Repositories\AuthRepository;
use App\Api\Merchant\Requests\VerifyAccountRequest;
use App\Api\Merchant\Transformers\UserTransformer;
use App\Api\Merchant\Validators\AuthValidator;

use Prettus\Validator\Exceptions\ValidatorException;
/**
 * 验证 Api
 * Class AuthController
 * @package App\Api\Merchant\Controllers
 */
class AuthController extends AuthBaseController
{
    protected $repository;
    protected $validator;

    public function __construct(AuthRepository $authRepository,AuthValidator $authValidator)
    {
        $this->repository = $authRepository;
        $this->validator = $authValidator;
    }



    public function verifyAccount(Request $request){
        try{
            $credentials = $request->all();
            $this->validator->with($credentials)->passesOrFail('init_verify_account');
            $phone = $request->get('phone');
            $zone = $request->get('zone','886');
            $accountType = $request->get('type',1);
            $checkExist = $this->repository->checkPhoneExist($phone);
            if($checkExist == null ||  ($phone == $checkExist->email && $checkExist->email_status == 'unverified')){
                return $this->responseError('此帳號不存在',$this->status_bad_request,400);
            }
            if(!empty($checkExist->password)){
                return $this->responseError('此帳號已驗證',$this->status_bad_request,400);
            }
            $verifyCode = '';
            if($accountType == 1){
                //發送短信驗證碼
                $verifyCode = $this->sendSmsMessage($phone,$zone);
            }else{
                $verifyCode = $this->sendEmailMessage($phone);
            }

            $factory = JWTFactory::addClaims([
                'sub'   => env('API_ID'),
                'iss'   => config('app.name'),
                'iat'   => Carbon::now()->timestamp,
                'exp'   => 60 * 5 ,
                'nbf'   => Carbon::now()->timestamp,
                'jti'   => uniqid(),
                'phone' => $phone,
                'code' =>  $verifyCode
            ]);

            $payload = $factory->make();

            $token = JWTAuth::encode($payload);

            return ['code' => $verifyCode,'token' => $token->get()];
        }catch (ValidatorException $e){
            return $this->responseError($e->getMessageBag()->first(),$this->status_validator_error,422);
        }
    }


    public function checkVerificationCode(Request $request){
        //@todo 判斷驗證碼5分鐘失效
        $credentials = $request->all();
        try{
            $this->validator->with($credentials)->passesOrFail('check_sms_code');
            try{
                $tokens = JWTAuth::getToken();
                //检查token中的手机和入驻手机是否一致
                $payload = JWTAuth::setToken($tokens)->getPayload();
                $phone = $payload->get('phone');
                $code = $payload->get('code');
                $requestPhone = $credentials['phone'];
                $requestCode = $credentials['code'];
                $verificationInfo = $this->repository->getVerifyActionCode($requestPhone);
                if($verificationInfo == null || $phone != $requestPhone ||$code != $requestCode){
                    return $this->responseError("驗證失敗，請重試",$this->status_jwt_invalidate,400);
                }

                $factory = JWTFactory::addClaims([
                    'sub'   => env('API_ID'),
                    'iss'   => config('app.name'),
                    'iat'   => Carbon::now()->timestamp,
                    'exp'   => 60 * 5 ,
                    'nbf'   => Carbon::now()->timestamp,
                    'jti'   => uniqid(),
                    'phone' => $phone
                ]);

                $payload = $factory->make();

                $token = JWTAuth::encode($payload);

                return array('verify' => 1,'token' => $token->get());
            }catch (\Exception $e){
                \Log::debug("error".$e->getMessage().' '.$e->getFile());
                return $this->responseError("驗證碼不正確",$this->status_jwt_invalidate,400);
            }
        }catch (ValidatorException $e){
            return $this->responseError($e->getMessageBag()->first(),$this->status_validator_error,422);
        }
    }

    public function setInitPassword(Request $request)
    {
        $credentials = $request->all();
        try{
            $this->validator->with($credentials)->passesOrFail('init_password');
            try{
                $tokens = JWTAuth::getToken();
                $payload = JWTAuth::setToken($tokens)->getPayload();
                $phone = $payload->get('phone');
                $verificationInfo = $this->repository->getVerifyActionCode($phone);
                if($verificationInfo == null){
                    return $this->responseError("驗證碼不正確",$this->status_bad_request,400);
                }
                \DB::table('verification')->where('verification_account', '=', $phone)->delete();
                $user = $this->repository->checkPhoneExist($phone);
                if($user == null){
                    return $this->responseError("此帳號不存在",$this->status_bad_request,400);
                }
                $user->password = $credentials['password'];
                $user->save();
                return array('success' => 1);
            }catch (JWTException $e){
                return $this->responseError("不是有效的請求",$this->status_bad_request,400);
            }
        }catch (ValidatorException $e){
            return $this->responseError($e->getMessageBag()->first(),$this->status_validator_error,422);
        }
    }

    public function login(Request $request)
    {
        $credentials = $request->only(['account','password','type']);
        try{
            $this->validator->with($credentials)->passesOrFail('login');
            $type = $credentials['type'];
            $user = $this->repository->loginGetUserByAccount($credentials['account']);
            if($user == null){
                return $this->responseError("登錄失敗",$this->status_bad_request,400);
            }
            $password = $credentials['password'];
            if(!\Hash::check($password,$user->password)){
                return $this->responseError("登錄失敗",$this->status_bad_request,400);
            }

            if($type == 'email'){
                if($user->email == '' || $user->email_status == 'unverified'){
                    return $this->responseError("登錄失敗",$this->status_bad_request,400);
                }
            }
            if($user->token != ''){
                try {
                    JWTAuth::setToken($user->token)->invalidate();
                } catch (TokenExpiredException $e) {

                } catch (JWTException $e) {

                } catch (TokenBlacklistedException $e){

                } catch (TokenInvalidException $e){

                }
            }
            $token = JWTAuth::fromUser($user);
            $user->token = $token;
            $user->last_login = date('Y-m-d H:i:s');
            $user->save();
            $storeInfo = $this->repository->getStoreById($user->store_id);
            $user->store = $storeInfo;
            if($storeInfo->super_uid == $user->id){
                $user->superAccount = true;
            }else{
                $user->superAccount = false;
            }
            return $this->response()->item($user,new UserTransformer());
        }catch (ValidatorException $e){
            return $this->responseError($e->getMessageBag()->first(),$this->status_validator_error,422);
        }
    }

    public function profile()
    {
        $user = $this->auth->user();
        if($user == null){
            return $this->noneLoginResponse();
        }
        unset($user->token);
        return $this->response->item($user,new UserTransformer());
    }

    public function logout(Request $request){
        $token = JWTAuth::getToken();
        try {
            JWTAuth::setToken($token)->invalidate();
        } catch (TokenExpiredException $e) {
            return $this->responseError("登出成功",$this->status_jwt_invalidate,401);
        } catch (JWTException $e) {
            return $this->responseError("登出成功",$this->status_jwt_invalidate,401);
        } catch (TokenBlacklistedException $e){
            return $this->responseError("登出成功",$this->status_jwt_invalidate,401);
        } catch (TokenInvalidException $e){
            return $this->responseError("登出成功",$this->status_jwt_invalidate,401);
        }
        return array('success' => 1);
    }

    public function updateProfile(Request $request)
    {
        $credentials = $request->all();
        $user = $this->getCurrentAuthUser();
        if($user == null){
            return $this->noneLoginResponse();
        }
        $this->repository->updateProfile($user,$credentials);
        return array('success' => 1);
    }

    public function changePassword(Request $request){
        $credentials = $request->only(
            'old_password', 'password', 'password_confirmation'
        );
        //get jwt auth user info
        $user = $this->getCurrentAuthUser();
        if($user == null){
            return $this->noneLoginResponse();
        }
        $hashed_password = $user->password;
        $message = array(
            'old_password.password_hash_check' => "舊密碼不正確",
        );
        $validator = Validator::make($credentials, [
            'old_password' => 'required|password_hash_check:'.$hashed_password,
            'password' => 'required|confirmed|min:6',
        ],$message);

        if($validator->fails()) {
            return $this->responseError($validator->errors()->first(),422);
        }

        //User::unguard();
        $user->password = $credentials['password'];
        $user->save();
        //User::reguard();

        return $this->responseSuccess();

    }

    public function resetPassword(Request $request){
        $credentials = $request->all();
        try{
            $this->validator->with($credentials)->passesOrFail('forget');
            $result = $this->repository->checkPhoneExist(trim($credentials['phone']));
            if(!$result){
                return $this->responseError('此賬號不存在');
            }
            $res = $this->repository->checkCode($credentials);
            if(!$res){
                return $this->responseError(trans("messages.mobile_auth_code_error"),$this->status_mobile_verification_error,422);
            }
            $result = $this->repository->updatePassword($credentials);
            return ['result'=>$result];
        }catch (ValidatorException $e){
            return $this->responseError($e->getMessageBag()->first(),$this->status_validator_error,422);
        }
    }

    public function updatePhone(Request $request){
        $credentials = $request->all();
        try{
            $this->validator->with($credentials)->passesOrFail('check_sms_code');
            $credentials['type'] = 1;
            $res = $this->repository->checkCode($credentials);
            if(!$res){
                return $this->responseError(trans("messages.mobile_auth_code_error"),$this->status_mobile_verification_error,422);
            }

            $user = $this->getCurrentAuthUser();
            $id = $user->id;
            return $this->repository->updatePhone($id,$credentials);

        }catch (ValidatorException $e){
            return $this->responseError($e->getMessageBag()->first(),$this->status_validator_error,422);
        }
    }

    public function userSimpleList(){
        $user = $this->getCurrentAuthUser();
        if($user == null){
            return $this->noneLoginResponse();
        }
        return $this->repository->getSimpleUserList($user->store_id);
    }


    public function userList(Request $request)
    {
        $user = $this->getCurrentAuthUser();
        if ($user == null) {
            return $this->noneLoginResponse();
        }
        $store_id = $this->getUserStoreId($user);
        return $this->response()->collection($this->repository->getUserListByStoreId($store_id), new UserListTransformer());
    }

    public function sendsms(Request $request){
        $credentials = $request->all();
        try{
            $this->validator->with($credentials)->passesOrFail('sms');
            if(isset($credentials['flag'])){
                $result = $this->repository->checkPhoneExist(trim($credentials['phone']));
                if(!$result){
                    return $this->responseError('此賬號不存在');
                }
            }
            if($credentials['type'] ==2){
                $validator = Validator::make($credentials, [
                    'phone' =>'required|email',
                ]);
                if($validator->fails()) {
                    return $this->responseError($validator->errors()->first(),422);
                }
            }

            return $this->repository->sendsms($credentials);

        }catch (ValidatorException $e){
            return $this->responseError($e->getMessageBag()->first(),$this->status_validator_error,422);
        }
    }


    public function updateEmail(Request $request){
        $credentials = $request->all();
        try{
            $this->validator->with($credentials)->passesOrFail('email');

            $credentials['type'] = 2;
            $credentials['phone'] = $credentials['email'];
            $res = $this->repository->checkCode($credentials);
            if(!$res){
                return $this->responseError(trans("messages.mobile_auth_code_error"),$this->status_mobile_verification_error,422);
            }
            $user = $this->getCurrentAuthUser();
            $id = $user->id;
            return $this->repository->updateEmail($id,$credentials['email']);

        }catch (ValidatorException $e){
            return $this->responseError($e->getMessageBag()->first(),$this->status_validator_error,422);
        }
    }

    public function updatePermission(Request $request){
        $credentials = $request->all();
        try{
            $this->validator->with($credentials)->passesOrFail('update_permission');
            $user = $this->getCurrentAuthUser();
            if ($user == null) {
                return $this->noneLoginResponse();
            }
            $this->repository->updatePermission($user->store_id,$credentials['user_id'],$credentials['permission']);
            return $this->responseSuccess();
        }catch (ValidatorException $e){
            return $this->responseError($e->getMessageBag()->first(),$this->status_validator_error,422);
        }
    }

}