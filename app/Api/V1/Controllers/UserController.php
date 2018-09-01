<?php
namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Tymon\JWTAuth\Facades\JWTAuth;
use Prettus\Validator\Exceptions\ValidatorException;

use App\Api\V1\Validators\AuthValidator;
use App\Api\V1\Transformers\UserTransformer;
use App\Api\V1\Repositories\AuthRepository;
use Validator;
use App\Api\V1\Repositories\ToolsRepository;
use App\Api\V1\Services\QRCodeServiceImpl;
use Illuminate\Support\Facades\Cache;
class UserController extends BaseController
{

    protected $authRepository;
    protected $authValidator;
    protected $toolsrepository;

    use  AuthenticatesUsers;


    public function __construct(AuthRepository $authRepository,AuthValidator $authValidator,ToolsRepository $toolsRepository){
        $this->authRepository = $authRepository;
        $this->authValidator = $authValidator;
        $this->toolsrepository = $toolsRepository;
    }

    public function login(Request $request){
        $credentials = $request->only(['phone','password','zone','type']);
        try{
            $this->authValidator->with($credentials)->passesOrFail('login');
            $user = $this->authRepository->login($credentials,$this->zone);
            if(!$user){
                return $this->responseError('手機號不存在');
            }
            if($user->status !=1){
                return $this->noneLoginResponse();
            }
            $hashed_password = $user->password;
            $password = trim($credentials['password']);

            if(!\Hash::check($password,$hashed_password)){
                return $this->responseError('賬戶名密碼錯誤');
            }

            $user->token = JWTAuth::fromUser($user);
            $user->save();
            $user = $this->authRepository->getById($user->id);
            $user->invite_number = $user->invite_count;
            $data = QRCodeServiceImpl::generateMemberCode($user->id);
            $user->qrcode = $data['image'];
            $user->my_promo = $user->promo_code ? $this->authRepository->getPromoInfo($user->promo_code) : '';
            return $this->response()->item($user,new UserTransformer());
        }catch (ValidatorException $e){
            return $this->responseError($e->getMessageBag()->first(),$this->status_validator_error,422);
        }

    }

    public function signUp(Request $request)
    {
        $credentials = $request->all();
        try{
            $this->authValidator->with($credentials)->passesOrFail('sign_up');

            $res = $this->authRepository->checkCode($credentials,$this->zone);
            if(!$res){
                return $this->responseError(trans("messages.mobile_auth_code_error"),$this->status_mobile_verification_error,422);
            }
            $checked = $this->authRepository->is_singup($credentials,$this->zone);
            if($checked){
                return $this->responseError('您已經註冊，可直接登錄');
            }
            return $credentials;

        }catch (ValidatorException $e){
            return $this->responseError($e->getMessageBag()->first(),$this->status_validator_error,422);
        }
    }

    /**
     * 填寫個人資料
     */
    public function fillInfo(Request $request){
        $credentials = $request->all();
        try{
            $this->authValidator->with($credentials)->passesOrFail('fill');
            if(isset($credentials['code']) && trim($credentials['code'])){
                $res = $this->authRepository->checkCode($credentials,$this->zone);
                if(!$res){
                    return $this->responseError(trans("messages.mobile_auth_code_error"),$this->status_mobile_verification_error,422);
                }
                $checked = $this->authRepository->is_singup($credentials,$this->zone);
                if($checked){
                    return $this->responseError('您已經註冊，可直接登錄');
                }

            }
            //註冊人數統計
            if(Cache::has('number')){
                Cache::increment('number');
            }else{
                Cache::put('number',1);
            }
            return $this->authRepository->fillInfo($credentials,$this->zone);

        }catch (ValidatorException $e){
            return $this->responseError($e->getMessageBag()->first(),$this->status_validator_error,422);
        }

    }

    public function userInfo(Request $request){
        $user = $this->auth->user();
        $user_id = $user->id;
        if(!$this->getAuthUserStatus($user_id)){
            return $this->noneLoginResponse();
        }
        $userInfo = $this->authRepository->getById($user_id);
        if($userInfo){
            unset($userInfo->password);
            unset($userInfo->token);
            $userInfo->invite_number = $userInfo->invite_count;
            $data = QRCodeServiceImpl::generateMemberCode($user_id);
            $userInfo->qrcode = $data['image'];
            $userInfo->my_promo = $userInfo->promo_code ? $this->authRepository->getPromoInfo($userInfo->promo_code) : '';
        }
        return $this->response()->item($userInfo,new UserTransformer());
    }

    public function changepassword(Request $request){
        $credentials = $request->only(
            'old_password', 'password', 'password_confirmation'
        );

        $user = $this->auth->user();
        $member_id = $user->id;
        if(!$this->getAuthUserStatus($member_id)){
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
        $user->password = $credentials['password'];
        $user->save();
        return array();
    }

    public function resetpassword(Request $request){
        $credentials = $request->all();
        try{
            $this->authValidator->with($credentials)->passesOrFail('forget');
            $result = $this->authRepository->updatePassword($credentials,$this->zone);
            if($result){
                return $this->responseSuccess();
            }
            return $this->responseError('手機號不存在');
        }catch (ValidatorException $e){
            return $this->responseError($e->getMessageBag()->first(),$this->status_validator_error,422);
        }

    }

    public function updateInfo(Request $request){
        $credentials = $request->all();
        $user = $this->auth->user();
        $id = $user->id;
        if(!$this->getAuthUserStatus($id)){
            return $this->noneLoginResponse();
        }
        try{

            return $this->authRepository->update($credentials,$id);

        }catch (ValidatorException $e){
            return $this->responseError($e->getMessageBag()->first(),$this->status_validator_error,422);
        }
    }


    public function isSetPayPassword(Request $request){
        $user = $this->auth->user();
        $id = $user->id;
        if(!$this->getAuthUserStatus($id)){
            return $this->noneLoginResponse();
        }
        $result =$this->authRepository->isSetPassword($id);

        return array('success' => $result);

    }

    public function verifyPayPassword(Request $request){
        $credentials = $request->only(
            'secure_password'
        );
        //get jwt auth user info
        $user = $this->auth->user();
        $id = $user->id;
        if(!$this->getAuthUserStatus($id)){
            return $this->noneLoginResponse();
        }
        $hashed_password = $user->secure_password;
        $validator = Validator::make($credentials, [
            'secure_password' => 'required|password_hash_check:'.$hashed_password,
        ]);
        if($validator->fails()) {
            return $this->responseError('安全碼不正確',$this->status_validator_error,422);
        }
        return array('success' => true);
    }

    public function updatePhone(Request $request){
        $credentials = $request->all();
        $user = $this->auth->user();
        $id = $user->id;
        if(!$this->getAuthUserStatus($id)){
            return $this->noneLoginResponse();
        }
        try{
            $this->authValidator->with($credentials)->passesOrFail('sign_up');

            $res = $this->authRepository->checkCode($credentials,$this->zone);
            if(!$res){
                return $this->responseError(trans("messages.mobile_auth_code_error"),$this->status_mobile_verification_error,422);
            }

           return $this->authRepository->updatePhone($id,$credentials,$this->zone);

        }catch (ValidatorException $e){
            return $this->responseError($e->getMessageBag()->first(),$this->status_validator_error,422);
        }
    }

    public function updatePayPassword(Request $request){
        $credentials = $request->all();
        $user = $this->auth->user();
        $id = $user->id;
        if(!$this->getAuthUserStatus($id)){
            return $this->noneLoginResponse();
        }
        try{
            $this->authValidator->with($credentials)->passesOrFail('paypassword');

            return $this->authRepository->updatePayPassword($id,trim($credentials['secure_password']));

        }catch (ValidatorException $e){
            return $this->responseError($e->getMessageBag()->first(),$this->status_validator_error,422);
        }
    }

    public function checkCode(Request $request){
        $credentials = $request->all();
        try{
            $this->authValidator->with($credentials)->passesOrFail('check');

            $result = $this->authRepository->checkCode($credentials,$this->zone);
            return ['success'=>$result];

        }catch (ValidatorException $e){
            return $this->responseError($e->getMessageBag()->first(),$this->status_validator_error,422);
        }
    }

    public function checkphone(Request $request){
        $credentials = $request->all();
        $checked = $this->authRepository->is_singup($credentials,$this->zone);
        if($checked){
            return $this->responseError('您已經註冊，可直接登錄');
        }
        return $this->toolsrepository->sms($credentials,$this->zone);

    }

    public function vertifyphone(Request $request){
        $credentials = $request->all();
        $checked = $this->authRepository->is_singup($credentials,$this->zone);
        if(!$checked){
            return $this->responseError('您尚未註冊，請先註冊');
        }
        return $this->toolsrepository->sms($credentials,$this->zone);

    }

    public function logout(Request $request){
        $token = JWTAuth::getToken();
        try {
            JWTAuth::setToken($token)->invalidate();
        } catch (TokenExpiredException $e) {
            return $this->responseError(trans("messages.logout_success"),$this->status_jwt_invalidate,401);
        } catch (JWTException $e) {
            return $this->responseError(trans("messages.logout_success"),$this->status_jwt_invalidate,401);
        } catch (TokenBlacklistedException $e){
            return $this->responseError(trans("messages.logout_success"),$this->status_jwt_invalidate,401);
        } catch (TokenInvalidException $e){
            return $this->responseError(trans("messages.logout_success"),$this->status_jwt_invalidate,401);
        }
        return array();
    }

    public function getQrCode(Request $request){
        $user = $this->auth->user();
        $id = $user->id;
        if(!$this->getAuthUserStatus($id)){
            return $this->noneLoginResponse();
        }
        $data = QRCodeServiceImpl::generateMemberCode($id);
        return $this->responseSuccess($data);
    }


    public function bindInvite(Request $request){
        $user = $this->auth->user();
        $uid = $user->id;
        if(!$this->getAuthUserStatus($uid)){
            return $this->noneLoginResponse();
        }
        $inviteid = $request->get('id',0);
        if($uid == $inviteid){
            return ['success'=>false];
        }

        return $this->authRepository->bind($inviteid,$uid);
    }

    public function view_promo(Request $request){
        $code = $request->get('code','');
        $user = $this->auth->user();
        $uid = $user->id;
        if(!$this->getAuthUserStatus($uid)){
            return $this->noneLoginResponse();
        }
        return $this->authRepository->getPromoInfo($code);
    }

}