<?php
namespace App\Api\Merchant\Controllers;

use App\Api\Merchant\Repositories\RechargeRepository;
use App\Api\Merchant\Transformers\StoreTransferTransformer;
use App\Api\Merchant\Validators\RechargeValidator;
use Illuminate\Http\Request;
use Prettus\Validator\Exceptions\ValidatorException;

/**
 * 商店充值
 * Class RechargeController
 * @package App\Api\Merchant\Controllers
 */
class RechargeController extends AuthBaseController
{
    protected $validator;
    protected $repository;

    public function __construct(RechargeRepository $rechargeRepository,RechargeValidator $rechargeValidator)
    {
        $this->validator = $rechargeValidator;
        $this->repository = $rechargeRepository;
    }

    public function createOrder(Request $request){
        $user = $this->getCurrentAuthUser();
        if($user == null){
            return $this->noneLoginResponse();
        }
        $amount = $request->get('amount',500);
        $this->repository->createOrder($amount);
        exit('');
        //return response($result,200);
    }

    /**
     * 提交儲值申請
     */
    public function createTransfer(Request $request){
        $credentials = $request->all();
        try{
            $this->validator->with($credentials)->passesOrFail('apply_transfer');
            $user = $this->getCurrentAuthUser();
            if($user == null){
                return $this->noneLoginResponse();
            }
            $result = $this->repository->createTransfer($credentials,$user);
            if($result === false){
                return $this->responseError("當前有儲值申請在待處理中，請稍後重試",$this->status_bad_request,400);
            }
            return $this->responseSuccess();
        }catch (ValidatorException $e){
            return $this->responseError($e->getMessageBag()->first(),$this->status_validator_error,422);
        }
    }

    /**
     * 轉賬充值
     */
    public function createTransferBy(Request $request){
        $credentials = $request->all();
        try{
            $this->validator->with($credentials)->passesOrFail('apply_transferby');
            $user = $this->getCurrentAuthUser();
            if($user == null){
                return $this->noneLoginResponse();
            }
            $result = $this->repository->createTransferby($credentials,$user);
            if(!$result['success']){
                return $this->responseError($result['msg']);
            }
            return $this->responseSuccess();
        }catch (ValidatorException $e){
            return $this->responseError($e->getMessageBag()->first(),$this->status_validator_error,422);
        }
    }

    public function transferList(Request $request){
        $user = $this->getCurrentAuthUser();
        if($user == null){
            return $this->noneLoginResponse();
        }
        $per_page = $request->get('per_page',$this->per_page);
        $result = $this->repository->getTransferList($user,$per_page);
        $result->appends('per_page',$per_page);
        return $this->response()->paginator($result,new StoreTransferTransformer());
    }

}