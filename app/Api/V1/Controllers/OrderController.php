<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/20
 * Time: 14:35
 */

namespace App\Api\V1\Controllers;

use App\Api\V1\Services\NotificationService;
use App\Common\Services\MqttNotificationService;
use Prettus\Validator\Exceptions\ValidatorException;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Tymon\JWTAuth\Facades\JWTAuth;

use App\Api\V1\Repositories\OrderRepository;
use App\Api\V1\Validators\OrderValidator;
use App\Api\V1\Transformers\OrderTransformer;
use App\Api\V1\Transformers\RebateTransformer;

class OrderController extends BaseController
{
    protected $repository;
    protected $validator;
    public function __construct(OrderRepository $orderRepository,OrderValidator $orderValidator)
    {
        $this->repository = $orderRepository;
        $this->validator = $orderValidator;
    }

    public function createOrder(Request $request){
        $credentials = $request->all();
        $user = $this->auth->user();
        $member_id = $user->id;
        if(!$this->getAuthUserStatus($member_id)){
            return $this->noneLoginResponse();
        }
        try{
            $this->validator->with($credentials)->passesOrFail('order');
            $list = $this->repository->getStoreMoney($credentials['store_id']);
            if(!$list){
                return $this->responseError('哭哭~糟糕！此店鋪尚未開啟權限，目前無法參與現金回饋活動，請告知店員或直接聯繫客服');
            }
            $result = $this->repository->create($credentials,$member_id);
            if($result['success']){
                $notificationService = new NotificationService();
                $notificationService->sendOrderNotification([
                    'order_id' => $result['result']['id'],
                    'store_id' => $result['result']['store_id']
                ],'add');
                MqttNotificationService::sendStoreProcessMessage($result['result']['store_id'],$result['result']['id'],'new');
                return $this->responseSuccess($result);
            }
            return $this->responseError($result['msg']);

        }catch (ValidatorException $e){
            return $this->responseError($e->getMessageBag()->first(),$this->status_validator_error,422);
        }
    }

    public function cancel(Request $request){
        $user = $this->auth->user();
        $member_id = $user->id;
        if(!$this->getAuthUserStatus($member_id)){
            return $this->noneLoginResponse();
        }
        $id = $request->get('order_id',0);
        $result = $this->repository->cancel($id);
        if($result['success']){
            $notificationService = new NotificationService();
            $notificationService->sendOrderNotification([
                'order_id' => $result['id'],
                'store_id' => $result['store_id']
            ],'cancel');
            MqttNotificationService::sendStoreProcessMessage($result['store_id'],$result['id'],'cancel');
            return $this->responseSuccess();
        }
        return $this->responseError($result['msg']);
    }

    public function orderList(Request $request){
        $user = $this->auth->user();
        $member_id = $user->id;
        if(!$this->getAuthUserStatus($member_id)){
            return $this->noneLoginResponse();
        }
        $per_page = $request->get('per_page',10);
        $type = $request->get('type',0);
        $date = $request->get('date','');
        if($type){
            $result = $this->repository->orderListByStore($member_id,$per_page);
        }else{
            $result = $this->repository->orderListByTime($member_id,$per_page,$date);
        }
        if($date){
            $result->appends('date',$date);
        }
        $result->appends('per_page',$per_page);
        return $this->response()->paginator($result,new OrderTransformer());
    }

    public function details(Request $request){
        $user = $this->auth->user();
        $member_id = $user->id;
        if(!$this->getAuthUserStatus($member_id)){
            return $this->noneLoginResponse();
        }
        $store_id = $request->get('store_id',0);
        $result = $this->repository->detailsByStore($member_id,$store_id);
        return $this->responseSuccess($result);
    }
    public function view(Request $request){
        $id = $request->get('id',0);
        $user = $this->auth->user();
        $member_id = $user->id;
        if(!$this->getAuthUserStatus($member_id)){
            return $this->noneLoginResponse();
        }
        $data = $this->repository->view($id);
        return $this->responseSuccess($data);
    }

    public function checkOrder(Request $request){
        $user = $this->auth->user();
        $member_id = $user->id;
        if(!$this->getAuthUserStatus($member_id)){
            return $this->noneLoginResponse();
        }
        $code = strtoupper(trim($request->get('code','')));
        $id = $request->get('id',0);
        $type = $request->get('type',1);
        if($type == 1){
            $result = $this->repository->query($code,$member_id);
            return $result;
        }
        $result = $this->repository->getStoreInfo($code,$id);
        if(!$result){
            return ['success'=>false,'msg'=>'該店鋪不存在','flag'=>1];
        }
//        if($result->status != 1){
//            return ['success'=>false,'msg'=>'該店铺已停權關閉','flag'=>1];
//        }
//        $list = $this->repository->getStoreMoney($result->id);
//        if(!$list){
//            return ['success'=>false,'msg'=>'該店鋪回贈金額不足，暫時不能下單','flag'=>1];
//        }
        $this->repository->updateStoreNumber($code);
        $data = $this->repository->checkOrder($member_id);

        if(!empty($data)){
            return ['success'=>false,'msg'=>'您尚有待處理的訂單，請處理完再下單','result'=>$data,'store'=>$result,'flag'=>2];
        }

        return ['success'=>true,'store'=>$result];
    }

    /**
     * 待回贈詳細
     */
    public function rebateOrder(Request $request){
        $user = $this->auth->user();
        $member_id = $user->id;
        if(!$this->getAuthUserStatus($member_id)){
            return $this->noneLoginResponse();
        }
        $per_page = $request->get('per_page',10);
        $data = $this->repository->rebateOrder($member_id,$per_page);
        $data->appends('per_page',$per_page);
        return $this->response()->paginator($data,new RebateTransformer());
    }

    /**
     * 我的積分使用情況
     */
    public function usage(Request $request){
        $user = $this->auth->user();
        $member_id = $user->id;
        if(!$this->getAuthUserStatus($member_id)){
            return $this->noneLoginResponse();
        }
        $per_page = $request->get('per_page',10);
        $data = $this->repository->usage($member_id,$per_page);
        $data->appends('per_page',$per_page);
        return $this->response()->paginator($data,new OrderTransformer());
    }

    public function comment(Request $request){
        $credentials = $request->all();
        $user = $this->auth->user();
        $member_id = $user->id;
        if(!$this->getAuthUserStatus($member_id)){
            return $this->noneLoginResponse();
        }
        try{
            $this->validator->with($credentials)->passesOrFail('comment');
            $result = $this->repository->addComment($credentials,$member_id);
            if($result){
                return $this->responseSuccess();
            }
            return $this->responseError('評論出錯');
        }catch (ValidatorException $e){
            return $this->responseError($e->getMessageBag()->first(),$this->status_validator_error,422);
        }
    }

    public function refund(Request $request){
        $credentials = $request->all();
        $user = $this->auth->user();
        $member_id = $user->id;
        if(!$this->getAuthUserStatus($member_id)){
            return $this->noneLoginResponse();
        }
        try{
            $this->validator->with($credentials)->passesOrFail('refund');
            return $this->repository->refund($credentials);

        }catch (ValidatorException $e){
            return $this->responseError($e->getMessageBag()->first(),$this->status_validator_error,422);
        }
    }
}