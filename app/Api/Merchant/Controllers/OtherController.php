<?php
namespace App\Api\Merchant\Controllers;

use App\Api\Merchant\Repositories\OtherRepository;
use App\Api\Merchant\Transformers\ActivityDetailTransformer;
use App\Api\Merchant\Transformers\ActivityListTransformer;
use App\Api\Merchant\Transformers\ImageApplyListTransformer;
use App\Api\Merchant\Transformers\ImageSignFormTransformer;
use App\Api\Merchant\Validators\OtherValidator;
use App\Common\Services\BaseNotificationService;
use App\Common\Services\MqttNotificationService;
use Illuminate\Http\Request;
use JWTAuth;
use JWTFactory;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Exceptions\TokenBlacklistedException;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Prettus\Validator\Exceptions\ValidatorException;

class OtherController extends AuthBaseController
{

    protected $repository;
    protected $validator;

    public function __construct(OtherRepository $otherRepository,OtherValidator $otherValidator){
        $this->repository = $otherRepository;
        $this->validator = $otherValidator;
    }

    public function simpleActivity(Request $request){
        $user = $this->getCurrentAuthUser();
        if($user == null){
            return $this->noneLoginResponse();
        }
        $result = $this->repository->getSimpleActivity();
        $unReadCount = $this->repository->getActivityUnreadCount($user->store_id);
        return $this->response()->collection($result,new ActivityListTransformer($user->store_id))->addMeta('unread',$unReadCount);
    }

    public function activityList(Request $request){
        $user = $this->getCurrentAuthUser();
        if($user == null){
            return $this->noneLoginResponse();
        }
        $per_page = $request->get('per_page',$this->per_page);
        $result = $this->repository->getActivityList($per_page);
        $result->appends('per_page',$per_page);
        $unReadCount = $this->repository->getActivityUnreadCount($user->store_id);
        return $this->response()->paginator($result,new ActivityListTransformer($user->store_id))->addMeta('unread',$unReadCount);
    }

    public function activityDetail(Request $request){
        $user = $this->getCurrentAuthUser();
        if($user == null){
            return $this->noneLoginResponse();
        }
        $id = intval($request->get('id',0));
        $info = $this->repository->getActivityDetail($id,$user->store_id);
        if($info){
            return $this->response()->item($info,new ActivityDetailTransformer());
        }else{
            return $this->responseError("沒有找到該消息",$this->status_bad_request,400);
        }

    }

    public function  testStoreMsg(){
        BaseNotificationService::testStoreMessage();
    }

    public function testStoreOrdermsg(){
        MqttNotificationService::sendStoreProcessMessage(1,1,'complete');
    }



    public function tokenCheck(Request $request){
        $token = JWTAuth::getToken();
        try {
            JWTAuth::setToken($token)->getPayload();
        } catch (TokenExpiredException $e) {
            return $this->responseError("無效的token",$this->status_jwt_invalidate,401);
        } catch (JWTException $e) {
            return $this->responseError("無效的token",$this->status_jwt_invalidate,401);
        } catch (TokenBlacklistedException $e){
            return $this->responseError("無效的token",$this->status_jwt_invalidate,401);
        } catch (TokenInvalidException $e){
            return $this->responseError("無效的token",$this->status_jwt_invalidate,401);
        }
        return array();
    }

    public function getImageApplyForm(Request $request){
        $user = $this->getCurrentAuthUser();
        if($user == null){
            return $this->noneLoginResponse();
        }
        $imageSignCarriage = $this->repository->getImageSignCarriage();
        $form = $this->repository->getImageSignForm();

        return $this->response->collection($form,new ImageSignFormTransformer())->addMeta('imagesign_carriage',$imageSignCarriage);
    }


    public function addImageSignApply(Request $request){
        $credentials = $request->all();
        try{
            $this->validator->with($credentials)->passesOrFail('create_image_apply');
            $user = $this->getCurrentAuthUser();
            if($user == null){
                return $this->noneLoginResponse();
            }
            $result = $this->repository->addImageSignApply($user->store_id,$credentials);
            if($result){
                return $this->responseSuccess();
            }else{
                return $this->responseError("申請失敗",$this->status_bad_request,400);
            }

        }catch (ValidatorException $e){
            return $this->responseError($e->getMessageBag()->first(),$this->status_validator_error,422);
        }
    }

    /**
     * 獲取單個申請詳情
     * @param Request $request
     * @return \Dingo\Api\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function getImageSignApply(Request $request){
        $credentials = $request->all();
        try{
            $this->validator->with($credentials)->passesOrFail('remove_image_apply');
            $user = $this->getCurrentAuthUser();
            if($user == null){
                return $this->noneLoginResponse();
            }
           $result = $this->repository->getImageSignApply($user->store_id,$credentials);
            if($result){
                return $this->response->item($result,new ImageApplyListTransformer());
            }else{
                return $this->responseSuccess();
            }
        }catch (ValidatorException $e){
            return $this->responseError($e->getMessageBag()->first(),$this->status_validator_error,422);
        }
    }

    /**
     * 獲取正在處理中或待處理的申請數量
     * @param Request $request
     * @return array|\Illuminate\Http\JsonResponse
     */
    public function getImageSignApplyCount(Request $request){
        $user = $this->getCurrentAuthUser();
        if($user == null){
            return $this->noneLoginResponse();
        }
        $result = $this->repository->getProcessISACount($user->store_id);
        return array('count' => $result);
    }


}