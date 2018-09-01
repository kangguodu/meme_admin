<?php
namespace App\Api\Merchant\Controllers;

use App\Api\Merchant\Repositories\StoreRepository;
use App\Api\Merchant\Transformers\BankListTransformer;
use App\Api\Merchant\Transformers\BannerListTransformer;
use App\Api\Merchant\Transformers\DownloadListTransformer;
use App\Api\Merchant\Transformers\StoreAmountTransformer;
use App\Api\Merchant\Transformers\StoreIncomeAmountTransformer;
use App\Api\Merchant\Transformers\StoreInfoTransformer;
use App\Api\Merchant\Transformers\TransListTransFormer;
use App\Api\Merchant\Transformers\TransTransFormer;
use App\Api\Merchant\Transformers\UserTransformer;
use App\Api\Merchant\Transformers\WithDrawlListTransformer;
use App\Api\Merchant\Validators\StoreValidator;
use Illuminate\Http\Request;
use Prettus\Validator\Exceptions\ValidatorException;
use App\Api\Merchant\Services\NotificationService;
use App\Api\Merchant\Transformers\NoticeLogTransformer;
class StoreController extends AuthBaseController
{

    protected $repository;
    protected $validator;

    public function __construct(StoreRepository $storeRepository,StoreValidator $storeValidator){
        $this->repository = $storeRepository;
        $this->validator = $storeValidator;
    }

    //店家資料
    public function index(Request $request){
        $user = $this->getCurrentAuthUser();
        if($user == null){
            return $this->noneLoginResponse();
        }
        return $this->response()->item(
            $this->repository->getStoreInfoById($user->store_id),
            new StoreInfoTransformer());
    }

    public function storeSimpleInfo(Request $request){
        $user = $this->getCurrentAuthUser();
        if($user == null){
            return $this->noneLoginResponse();
        }
        return $this->repository->getStoreSimpleInfo($user->store_id);
    }


    public function saveStoreInfo(Request $request){
        $user = $this->getCurrentAuthUser();
        if($user == null){
            return $this->noneLoginResponse();
        }
        $credentials = $request->all();
        $this->repository->saveStoreInfo($user->store_id,$credentials);
        return $this->responseSuccess();
    }


    /**
     * 獲取店家回贈積分額度
     */
    public function getAmount(){
        $user = $this->getCurrentAuthUser();
        if($user == null){
            return $this->noneLoginResponse();
        }
        return $this->response()->item(
            $this->repository->getAccountAmount($user->store_id),
            new StoreAmountTransformer());
    }

    /**
     * 獲取店家回贈積分額度
     */
    public function getInComeAmount(){
        $user = $this->getCurrentAuthUser();
        if($user == null){
            return $this->noneLoginResponse();
        }
        $result = $this->repository->getAccountAmount($user->store_id);
        $bank_count = $this->repository->getBankCount($user->store_id);
        $process_count = $this->repository->getWithDrawlProcessCount($user->store_id);
        $result->bank_count = $bank_count;
        $result->withdrawl_count = $process_count;
        return $this->response()->item(
            $result,
            new StoreIncomeAmountTransformer());
    }

    /**
     * 獲取儲值歷史記錄
     */
    public function getAmountBills(Request $request){
        $user = $this->getCurrentAuthUser();
        if($user == null){
            return $this->noneLoginResponse();
        }
        $per_page = $request->get('per_page',$this->per_page);
        $result = $this->repository->getAmountBills($user->store_id,$per_page);
        $result->appends('per_page',$per_page);
        return $this->response()->paginator($result,new TransTransFormer());
    }

    public function editProbability(Request $request){
        $user = $this->getCurrentAuthUser();
        if($user == null){
            return $this->noneLoginResponse();
        }
        $probability = $request->get('probability',null);
        $fixed_probability = $request->get('fixed_probability',null);
        $this->repository->storeProbability($user->store_id,$probability,$fixed_probability);
        return $this->responseSuccess();
    }

    public function getBills(Request $request){
        $user = $this->getCurrentAuthUser();
        if($user == null){
            return $this->noneLoginResponse();
        }
        $per_page = $request->get('per_page',$this->per_page);
        $category = $request->get('category',0);
        $result = $this->repository->getBills($user->store_id,$category,$per_page);
        $result->appends('per_page',$per_page);
        $result->appends('category',$category);
        return $this->response()->paginator($result,new TransListTransFormer());
    }


    public function getOpenHours(Request $request){
        $user = $this->getCurrentAuthUser();
        if($user == null){
            return $this->noneLoginResponse();
        }
        $result = $this->repository->getStoreOpenHours($user->store_id);
        return $result;
    }

    public function saveOpenHours(Request $request){
        $user = $this->getCurrentAuthUser();
        if($user == null){
            return $this->noneLoginResponse();
        }
        $credentials = $request->all();
        $this->repository->updateOpenHours($user->store_id,$credentials);
        return $this->responseSuccess();
    }

    public function addStoreUser(Request $request){
        $credentials = $request->all();
        try{
            $this->validator->with($credentials)->passesOrFail('add_store_user');

            $user = $this->getCurrentAuthUser();
            if($user == null){
                return $this->noneLoginResponse();
            }
            $verification = $this->repository->checkAddUserVerification($credentials['mobile'],$credentials['code']);
            if($verification == null){
                return $this->responseError("驗證碼不正確",$this->status_validator_error,422);
            }
            $result = $this->repository->addStoreUser($user->store_id,$credentials);
            if($result){
                $this->repository->deleteVerificationById($verification->id);
                return $this->responseSuccess();
            }else{
                return $this->responseError("新增角色失敗，請重試",$this->status_bad_request,400);
            }
        }catch (ValidatorException $e){
            return $this->responseError($e->getMessageBag()->first(),$this->status_validator_error,422);
        }
    }

    public function getStoreUser(Request $request){
        $credentials = $request->all();
        try{
            $this->validator->with($credentials)->passesOrFail('get_store_user');
            $user = $this->getCurrentAuthUser();
            if($user == null){
                return $this->noneLoginResponse();
            }
            $result = $this->repository->getStoreUserById($user->store_id,$credentials);
            if($result != null){
                return $this->response->item($result,new UserTransformer());
            }else{
                return $this->responseError("角色不存在",$this->status_bad_request,400);
            }
        }catch (ValidatorException $e){
            return $this->responseError($e->getMessageBag()->first(),$this->status_validator_error,422);
        }
    }

    public function updateStoreUser(Request $request){
        $credentials = $request->all();
        try{
            if(!isset($credentials['id'])){
                $credentials['id'] = 0;
            }
            $this->validator->setId(intval($credentials['id']))->with($credentials)->passesOrFail('edit_store_user');
            $user = $this->getCurrentAuthUser();
            if($user == null){
                return $this->noneLoginResponse();
            }
            $result = $this->repository->updateStoreUser($user->store_id,$credentials);
            if($result){
                return $this->responseSuccess();
            }else{
                return $this->responseError("編輯角色失敗，請重試",$this->status_bad_request,400);
            }
        }catch (ValidatorException $e){
            return $this->responseError($e->getMessageBag()->first(),$this->status_validator_error,422);
        }
    }

    public function removeStoreUser(Request $request){
        $credentials = $request->all();
        try{
            $this->validator->with($credentials)->passesOrFail('remove_store_user');
            $user = $this->getCurrentAuthUser();
            if($user == null){
                return $this->noneLoginResponse();
            }
            $this->repository->removeStoreUser($user->store_id,$credentials['id']);
            return $this->responseSuccess();
        }catch (ValidatorException $e){
            return $this->responseError($e->getMessageBag()->first(),$this->status_validator_error,422);
        }
    }

    public function bannerList(Request $request){
        $user = $this->getCurrentAuthUser();
        if($user == null){
            return $this->noneLoginResponse();
        }
        $result = $this->repository->getBannerList($user->store_id);
        if($result->isNotEmpty()){
            return $this->response()->collection($result,new BannerListTransformer());
        }else{
            return $this->responseSuccess(['data' => array()]);
        }
    }

    public function addBanner(Request $request){
        $user = $this->getCurrentAuthUser();
        if($user == null){
            return $this->noneLoginResponse();
        }
        $credentials = $request->all();
        $this->repository->saveBannerList($user->store_id,$credentials);
        return $this->responseSuccess();
    }

    /**
     * 编辑店铺logo
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function editStoreLogo(Request $request){
        $credentials = $request->all();
        try{
            $this->validator->with($credentials)->passesOrFail('update_logo');
            $user = $this->getCurrentAuthUser();
            if($user == null){
                return $this->noneLoginResponse();
            }
            $credentials['store_id'] = $user->store_id;
            $this->repository->updateStoreLogo($credentials);
            return $this->responseSuccess();
        }catch (ValidatorException $e){
            return $this->responseError($e->getMessageBag()->first(),$this->status_validator_error,422);
        }
    }

    public function getStoreLogo(Request $request){
        $user = $this->getCurrentAuthUser();
        if($user == null){
            return $this->noneLoginResponse();
        }
        $result = $this->repository->getStoreLogo($user->store_id);
        return $this->responseSuccess(['logo' => $result]);
    }

    public function downloadAreaList(Request $request){
        $user = $this->getCurrentAuthUser();
        if($user == null){
            return $this->noneLoginResponse();
        }
        $result = $this->repository->getDownloadAreaList($user->store_id);
        if($result->isEmpty()){
            return $this->responseSuccess(['data' => []]);
        }else{
            return $this->response()->collection($result,new DownloadListTransformer());
        }
    }

    public function getDownloadDetail(Request $request){
        $user = $this->getCurrentAuthUser();
        if($user == null){
            return $this->noneLoginResponse();
        }
        $id = $request->get('id',0);
        return $this->repository->getDownloadAreaDetail($user->store_id,$id);
    }

    public function getBankList(Request $request){
        $user = $this->getCurrentAuthUser();
        if($user == null){
            return $this->noneLoginResponse();
        }
        $result = $this->repository->getBankList($user->store_id);
        if($result->isEmpty()){
            return $this->responseSuccess(['data' => []]);
        }else{
            return $this->response()->collection($result,new BankListTransformer());
        }
    }

    public function getBankCount(Request $request){
        $user = $this->getCurrentAuthUser();
        if($user == null){
            return $this->noneLoginResponse();
        }
        $bankCount = $this->repository->getBankCount($user->store_id);
        return $this->responseSuccess(['count' => $bankCount]);
    }

    public function addBankAccount(Request $request){
        $credentials = $request->all();
        try{
            $this->validator->with($credentials)->passesOrFail('add_bank');
            $user = $this->getCurrentAuthUser();
            if($user == null){
                return $this->noneLoginResponse();
            }
            $this->repository->addBank($user->store_id,$credentials);
            return $this->responseSuccess();
        }catch (ValidatorException $e){
            return $this->responseError($e->getMessageBag()->first(),$this->status_validator_error,422);
        }
    }

    public function deleteBankAccount(Request $request){
        $user = $this->getCurrentAuthUser();
        if($user == null){
            return $this->noneLoginResponse();
        }
        $id = $request->get('id',0);
        $this->repository->deleteBank($user->store_id,$id);
        return $this->responseSuccess(['data' => []]);
    }

    public function getWithDrawlList(Request $request){
        $user = $this->getCurrentAuthUser();
        if($user == null){
            return $this->noneLoginResponse();
        }
        $per_page = $request->get('per_page',$this->per_page);
        $result = $this->repository->getWithDrawlList($user->store_id,$per_page);
        if($result->isEmpty()){
            return $this->responseSuccess(['data' => []]);
        }else{
            $result->appends('per_page',$per_page);
            return $this->response()->paginator($result,new WithDrawlListTransformer());
        }
    }

    public function getWithDrawlCount(Request $request){
        $user = $this->getCurrentAuthUser();
        if($user == null){
            return $this->noneLoginResponse();
        }
        $withDrawling = $this->repository->getWithDrawlProcessCount($user->store_id);
        return $this->responseSuccess(['count' => $withDrawling]);
    }

    public function addWithDrawl(Request $request){
        $credentials = $request->all();
        try{
            $this->validator->with($credentials)->passesOrFail('add_with_drawl');
            $user = $this->getCurrentAuthUser();
            if($user == null){
                return $this->noneLoginResponse();
            }
            $withDrawling = $this->repository->getWithDrawlProcessCount($user->store_id);
            if($withDrawling > 0){
                return $this->responseError("您有正在請款的記錄，請完結後再次申請",$this->status_bad_request,400);
            }
            $bank = $this->repository->getBankById($user->store_id,$credentials['bank_id']);
            if($bank == null){
                return $this->responseError("銀行卡不存在",$this->status_bad_request,400);
            }
            $account = $this->repository->getStoreIncome($user->store_id);
            if($account == null){
                return $this->responseError("請款失敗",$this->status_bad_request,400);
            }else if(intval($credentials['type']) ==1 && $account->business_income < $credentials['amount']){
                return $this->responseError("請款金額大於營業收入，請款失敗",$this->status_bad_request,400);
            }else if(intval($credentials['type']) ==2 && $account->credits_income < $credentials['amount']){
                return $this->responseError("請款金額大於蜂幣收入，請款失敗",$this->status_bad_request,400);
            }else if($credentials['amount']<1000){
                return $this->responseError("請款金額不得少於1000，請款失敗",$this->status_bad_request,400);
            }else if($account->return_credits <3000){
                return $this->responseError("你的店鋪已停權，不能請款，請盡快充值",$this->status_bad_request,400);
            }
            $this->repository->addWithDrawl($user->store_id,$credentials,$bank,$account,intval($credentials['type']));
            return $this->responseSuccess();
        }catch (ValidatorException $e){
            return $this->responseError($e->getMessageBag()->first(),$this->status_validator_error,422);
        }
    }

    public function cancelWithDrawl(Request $request){
        $credentials = $request->all();
        try{
            $this->validator->with($credentials)->passesOrFail('cancel_with_drawl');
            $user = $this->getCurrentAuthUser();
            if($user == null){
                return $this->noneLoginResponse();
            }
            $withDrawlInfo = $this->repository->getWithDrawlInfo($user->store_id,$credentials['id']);
            if($withDrawlInfo == null){
                return $this->responseError("請款記錄不存在",$this->status_bad_request,400);
            }
            if(intval($withDrawlInfo->status) > 0){
                return $this->responseError("請款記錄已處理，無需再進行取消操作",$this->status_bad_request,400);
            }
            $account = $this->repository->getStoreIncome($user->store_id);
            if($account == null){
                return $this->responseError("操作不正確",$this->status_bad_request,400);
            }
            $this->repository->cancelWithDrawl($withDrawlInfo,$account);
            return $this->responseSuccess();
        }catch (ValidatorException $e){
            return $this->responseError($e->getMessageBag()->first(),$this->status_validator_error,422);
        }
    }

    public function inviteNum(Request $request){
        $user = $this->getCurrentAuthUser();
        if($user == null){
            return $this->noneLoginResponse();
        }
        $data = $this->repository->inviteNum($user->store_id);
        return $this->responseSuccess(['data'=>$data]);
    }

    public function CompanyBank(Request $request){
        $data = $this->repository->company_bank();
        if($data){
            return ['data'=>$data,'flag'=>1];
        }
        return ['data'=>$data,'flag'=>0];
    }

    public function noneReturnCredits(Request $request){
        $user = $this->getCurrentAuthUser();
        if($user == null){
            return $this->noneLoginResponse();
        }

        $data = $this->repository->return_notice($user->store_id);
       
       return $data;
    }

    public function notice(Request $request){
        $user = $this->getCurrentAuthUser();
        if($user == null){
            return $this->noneLoginResponse();
        }
        $per_page = $request->get('per_page',10);
        $result = $this->repository->getNotice($user->store_id,$per_page);
        $result->appends('per_page',$per_page);
        $unReadCount = $this->repository->unread($user->store_id);
        return $this->response()->paginator($result,new NoticeLogTransformer($user->store_id))->addMeta('unread',$unReadCount);
    }

    public function notice_view(Request $request){
        $user = $this->getCurrentAuthUser();
        if($user == null){
            return $this->noneLoginResponse();
        }
        $id = $request->get('id',0);
        return $this->repository->notice_view($id,$user->store_id);
    }

}