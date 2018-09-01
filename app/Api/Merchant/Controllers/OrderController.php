<?php
namespace App\Api\Merchant\Controllers;

use App\Api\Merchant\Repositories\OrderRepository;
use App\Api\Merchant\Services\NotificationService;
use App\Api\Merchant\Transformers\CommentListTransformer;
use App\Api\Merchant\Transformers\OrderHistoryTransformer;
use App\Api\Merchant\Transformers\OrderListTransformer;
use App\Api\Merchant\Validators\OrderValidator;
use Illuminate\Http\Request;
use Prettus\Validator\Exceptions\ValidatorException;

class OrderController extends AuthBaseController
{
    protected $repository;
    protected $validator;

    public function __construct(OrderRepository $orderRepository,OrderValidator $orderValidator){
        $this->repository = $orderRepository;
        $this->validator = $orderValidator;

    }

    /**
     * 結帳記錄
     */
    public function historyList(Request $request){
        $user = $this->getCurrentAuthUser();
        if($user == null){
            return $this->noneLoginResponse();
        }
        $params = [
            'start_date' => $request->get('start_date',date('Y-m-d 00:00:00')),
            'end_date' => $request->get('end_date',date('Y-m-d 23:59:59')),
            'status' => $request->get('status',0),
            'checkout_by' => $request->get('checkout_by',0),
            'refund_by' => $request->get('refund_by',0),
            'per_page' => $request->get('per_page',$this->per_page),
            'store_id' => $user->store_id
        ];
        $result = $this->repository->getOrderHistoryList($params);
        $result->appends('start_date',$params['start_date']);
        $result->appends('end_date',$params['end_date']);
        $result->appends('status',$params['status']);
        $result->appends('checkout_by',$params['checkout_by']);
        $result->appends('refund_by',$params['refund_by']);
        $result->appends('per_page',$params['per_page']);
        $report = $this->repository->getOrderHistoryReport($params);
        return $this->response()->paginator($result,new OrderHistoryTransformer())->addMeta('report',$report);
    }


    /**
     * 待處理訂單列表(結帳列表)
     */
    public function index(Request $request){
        $per_page = $request->get('per_page',$this->per_page);
        $user = $this->getCurrentAuthUser();
        if($user == null){
            return $this->noneLoginResponse();
        }
        $result = $this->repository->getProcessOrder($this->user->store_id,$per_page);
        $result->appends('per_page',$per_page);
        return $this->response()->paginator($result,new OrderListTransformer());
    }

    public function editOrder(Request $request){
        $user = $this->getCurrentAuthUser();
        if($user == null){
            return $this->noneLoginResponse();
        }
        $order_id = $request->get('order_id',0);
        $status = $request->get('status',0);
        $order = $this->repository->getOrderById($user->store_id,$order_id);
        if($order == null){
            return $this->responseError('操作不正確，訂單可能已處理',$this->status_bad_request,400);
        }
        $notificationData = array(
            'order_id' => $order->id,
            'user_id' => $order->member_id,
            'nickname' => $order->nickname,
            'name' => $order->name,
            'mrate' => $order->mrate,
            'amount' => $order->amount,
            'probability' => ($order->feature_probability_time<=time() && $order->feature_probability_time>0) ? $order->feature_probability : $order->probability
        );
        $notificationService = new NotificationService();
        if(intval($status) == 2){
            $this->repository->changeOrderStatusByOrder($order,2,$user);
            $notificationService->sendProcessNotification($notificationData,'cancel');
            return $this->responseSuccess();
        }else{
            $result = $this->repository->editOrderByOrder($order,$user);
            if($result !== false){
                $notificationData['order_rebate'] = $result;
                $notificationService->sendProcessNotification($notificationData,'complete');
                return $this->responseSuccess();
            }else{
                return $this->responseError("處理訂單失敗",$this->status_bad_request,400);
            }
        }
    }

    // 评论列表
    public function commentList(Request $request){
        $user = $this->getCurrentAuthUser();
        if($user == null){
            return $this->noneLoginResponse();
        }
        $level = $request->get('level',0);
        $per_page = $request->get('per_page',$this->per_page);
        $result = $this->repository->commentList($user->store_id,$level,$per_page);
        $result->appends('level',$level);
        $result->appends('per_page',$per_page);
        //$report = $this->repository->commentReport($user->store_id,$level);
        return $this->response()->paginator($result,new CommentListTransformer());
    }

    public function replyComment(Request $request){
        $credentials = $request->all();
        try{
            $this->validator->with($credentials)->passesOrFail('reply_comment');
            $user = $this->getCurrentAuthUser();
            if($user == null){
                return $this->noneLoginResponse();
            }
            $result = $this->repository->addReplyComment($this->getUserStoreId($user),$credentials['id'],$credentials['reply_content']);
            if($result === false){
                return $this->responseError("回覆失敗，請重試",$this->status_bad_request,400);
            }
            return $this->responseSuccess();
        }catch (ValidatorException $e){
            return $this->responseError($e->getMessageBag()->first(),$this->status_validator_error,422);
        }
    }


}