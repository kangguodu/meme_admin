<?php
namespace App\Common\Controllers;

use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;
use Illuminate\Http\Request;
use JWTAuth;
use JWTFactory;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Sms;
use Validator;
use App\Verification;

class ToolsController extends BaseController
{

    public function tokenCheck(Request $request){
        $token = JWTAuth::getToken();
        try {
            JWTAuth::setToken($token)->getPayload();
        } catch (TokenExpiredException $e) {
            return $this->responseError(trans("messages.invalid_token"),$this->status_jwt_invalidate,401);
        } catch (JWTException $e) {
            return $this->responseError(trans("messages.invalid_token"),$this->status_jwt_invalidate,401);
        } catch (TokenBlacklistedException $e){
            return $this->responseError(trans("messages.invalid_token"),$this->status_jwt_invalidate,401);
        } catch (TokenInvalidException $e){
            return $this->responseError(trans("messages.invalid_token"),$this->status_jwt_invalidate,401);
        }
        return array();
    }


    public function sendSms(Request $request){
        $credentials = $request->only(['zone', 'phone']);

        $validator = Validator::make($credentials, [
            'phone' => 'required',
        ]);

        if($validator->fails()) {
            return $this->responseError($validator->errors()->first(),422);
        }

        $phone = trim($credentials['phone']);
        $zone = '';
        if(isset($credentials['zone']) && !empty($credentials['zone'])){
            $zone = trim($credentials['zone']);
        }
        $smsCode = $this->sendSmsMessage($phone,$zone);
        return array('code' => $smsCode);
    }



    public function upload(Request $request)
    {
        //判断请求中是否包含name=file的上传文件
        if (!$request->hasFile('file')) {
            return $this->responseError('请上传图片');
        }
        // 判断图片上传中是否出错
        $file = $request->file('file');
        if (!$file->isValid()) {
            return responseError('上传图片出错，请重试');
        }
        $credentials = $request->all();
        $entension = $file -> getClientOriginalExtension(); //  上传文件后缀
        $filename = uniqid().mt_rand(100,999);  // 重命名图片
        if(!empty($entension)){
            $filename .= '.'.$entension;
        }
        if(!isset($credentials['dir'])){
            $credentials['dir'] = 'temp';
        }
        $host = url('/');
        $file->move(public_path().'/upload/'.$credentials['dir'].'/',$filename);  // 重命名保存
        $img_path = $host.'/upload/'.$credentials['dir'].'/'.$filename;
        return $this->responseSuccess(array('url'=>$img_path));
    }

}