<?php
namespace App\Api\Merchant\Controllers;

use App\Api\Merchant\Repositories\SyncOrderRepository;
use App\Api\Merchant\Transformers\SyncOrderListTransformer;
use App\Api\Merchant\Validators\SyncOrderValidator;
use Illuminate\Http\Request;
use Prettus\Validator\Exceptions\ValidatorException;
/**
 * 同步订单的控制器
 * Class SyncController
 * @package App\Api\Merchant\Controllers
 */
class SyncOrderController extends BaseController
{

    protected $repository;
    protected $validator;

    public function __construct(SyncOrderRepository $syncOrderRepository,SyncOrderValidator $syncOrderValidator)
    {
        $this->repository = $syncOrderRepository;
        $this->validator = $syncOrderValidator;
    }

    public function index(Request $request){
        $credentials = $request->all();
        try{
            $this->validator->with($credentials)->passesOrFail('get_order');
            $per_page = isset($credentials['per_page'])?$credentials['per_page']:$this->per_page;
            $result = $this->repository->orderList($credentials,$per_page);
            $result->appends('per_page',$per_page);
            $result->appends('date',$credentials['date']);
            return $this->response()->paginator($result,new SyncOrderListTransformer());
        }catch (ValidatorException $e){
            return $this->responseError($e->getMessageBag()->first(),$this->status_validator_error,422);
        }
    }

    public function syncOrder(Request $request){
        $credentials = $request->all();
        try{
            $this->validator->with($credentials)->passesOrFail('sync_order');
            $result = $this->repository->syncRebateOrder($credentials);
            if($result['success'] === false){
                return $this->responseError("同步失败",$this->status_bad_request,400);
            }else{
                return $result['data'];
            }
        }catch (ValidatorException $e){
            return $this->responseError($e->getMessageBag()->first(),$this->status_validator_error,422);
        }
    }

}