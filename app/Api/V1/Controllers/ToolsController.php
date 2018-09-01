<?php

namespace App\Api\V1\Controllers;
use Illuminate\Support\Facades\Cache;
use App\Api\V1\Repositories\ToolsRepository;
use Carbon\Carbon;
use Illuminate\Http\Request;
use JWTAuth;
use JWTFactory;
use Tymon\JWTAuth\Exceptions\JWTException;
use Dingo\Api\Exception\ValidationHttpException;
use App\Sms;
use Validator;
use App\Verification;
use BaconQrCode\Encoder\QrCode;
use App\Store;
use  App\Api\V1\Services\BaseService;

class ToolsController extends BaseController
{
    protected $repository;
    public function __construct(ToolsRepository $toolsRepository){
        $this->repository = $toolsRepository;
    }

    

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

        $zone = $this->zone;
        return $this->repository->sms($credentials,$zone);

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
            return $this->responseError('上传图片出错，请重试');
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

    public function share(Request $request){
        $store_id = $request->get('id',0);
        return $this->create($store_id);

    }
    public function html($data){
        $htmldata = file_get_contents(resource_path('views/share.blade.php'));
        $name = $data->image;
        $image = BaseService::image($data->image);

        $desc = $data->city.$data->district.$data->address;
        $url = url('/').'/share/'.$data->id.'.html';
        $htmldata = str_replace('https://services.memecoins.com.tw/memecoins-register-h5/#/register',$url,$htmldata);
        $htmldata = str_replace('content="memecoins"','content="'.$name.'"',$htmldata);
        $htmldata = str_replace('https://services.memecoins.com.tw/memecoins/public/upload/logo.png',$image,$htmldata);
        $htmldata = str_replace('指尖一滑，現金輕鬆入袋',$desc,$htmldata);
        return $htmldata;
    }
    public function create($store_id){
        $data = (new \App\Store())->where(['id'=>$store_id])->first();
        if($data){
            try{
                $html = public_path('share/'.$data->id.'.html');
                if(file_exists($html)){
                    @unlink($html);
                }
                $htmldata = $this->html($data);
                file_put_contents($html,$htmldata);
                chmod($html,0777);
                return ['success'=>true];

            }catch (\Exception $e){
                \Log::error("創建分享頁面失敗:".$e->getMessage());
                return ['success'=>false];
            }

        }
        return ['success'=>false];
    }

    public function upload_base64(Request $request)
    {
        $credentials = $request->all();
        if(!isset($credentials['file'])){
            return $this->responseError('傳輸數據出錯');
        }
        $base64_img = trim($credentials['file']);
        if(!isset($credentials['dir'])){
            $credentials['dir'] = 'temp';
        }

        if(preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_img, $result)){
            $entension = $result[2];//图片后缀
            $filename = uniqid().mt_rand(100,999).'.'.$entension;
            $new_file = public_path().'/upload/'.$credentials['dir'].'/'.$filename;
            $host = url('/');
            if(file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_img)))){
                $img_path = $host.'/upload/'.$credentials['dir'].'/'.$filename;

                return $this->responseSuccess(array('url'=>$img_path));

            }else{
                return $this->responseError('图片上传失败');
            }

        }else{
            return $this->responseError('文件错误');

        }

    }

    public function deleteimage(Request $request){
        $url = $request->get('url','');
        $i = strpos($url,'upload');
        $str = substr($url,$i);
        if(file_exists(public_path($str))){
            unlink(public_path($str));
            return ['success'=>true];
        }
        return ['success'=>false];
    }

    public function version(Request $request){
        $type = $request->get('type',1);
        return $this->repository->version($type);
    }
}