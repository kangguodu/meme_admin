<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/21
 * Time: 9:34
 */

namespace App\Api\V1\Controllers;
use Prettus\Validator\Exceptions\ValidatorException;
use Illuminate\Http\Request;
use App\Api\V1\Repositories\CouponsRepository;
use App\Api\V1\Validators\CouponsValidator;
use App\Api\Merchant\Services\NotificationService;

class CouponsController extends BaseController
{
    protected $repository;
    protected $validator;
    public function __construct(CouponsRepository $couponsRepository,CouponsValidator $couponsValidator)
    {
        $this->repository = $couponsRepository;
        $this->validator = $couponsValidator;
    }

    public function index(Request $request){
        $store_id = $request->get('store_id',0);
        $money = $request->get('money',0);
        $use_type = $request->get('use_type',0);
        $user = $this->auth->user();
        $uid = $user->id;
        if(!$this->getAuthUserStatus($uid)){
            return $this->noneLoginResponse();
        }
        $data = $this->repository->index($store_id,$uid,$money,$use_type);
        return $this->responseSuccess($data);
    }

    /**
     * 領取 ，狀態修改，老版本，暫時不使用
     */
//    public function change(Request $request){
//        $user = $this->auth->user();
//        $member_id = $user->id;
//        if(!$this->getAuthUserStatus($member_id)){
//            return $this->noneLoginResponse();
//        }
//        $id = $request->get('id',0);
//        $result = $this->repository->receive($id,$member_id);
//        return $this->responseSuccess(['success'=>$result]);
//    }

    /**
     *獲取优惠券
     */
    public function receive(Request $request){
        $id = $request->get('id',0); //優惠券id
        $user = $this->auth->user();
        $member_id = $user->id;
        if(!$this->getAuthUserStatus($member_id)){
            return $this->noneLoginResponse();
        }
        $result = $this->repository->create($id,$member_id);
        if(!$result['success']){
            return $this->responseError($result['msg']);
        }
        return $this->responseSuccess();
    }

    /**
     *註冊后領取优惠券
     */
    public function register_after_receive(Request $request){
        $id = $request->get('id',0); //20元優惠券id
        $member_id = $request->get('member_id',0);
        $result = $this->repository->getCoupons($id,$member_id);
        if(!$result['success']){
            return $this->responseError($result['msg']);
        }
        return $this->responseSuccess();
    }
    /**
     *分享后領取优惠券
     */
    public function share_after_receive(Request $request){
        $id = $request->get('id',0); //10元優惠券id
        $code = $request->get('code','');
        $result = $this->repository->getShareCoupons($id,$code);
        if(!$result['success']){
            return $this->responseError($result['msg']);
        }
        return $this->responseSuccess();
    }

    /**
     * 發行優惠券
     */
    public function release_coupons(Request $request){
        $credentials = $request->all();
        try{
            $this->validator->with($credentials)->passesOrFail('release');
            return $this->repository->release($credentials);

        }catch (ValidatorException $e){
            return $this->responseError($e->getMessageBag()->first(),$this->status_validator_error,422);
        }
    }

    public function check(Request $request){
        $code = $request->get('code','');
        return $this->repository->check($code);
    }

    public function login_after_receive(Request $request){
        $code = $request->get('code','');
        $user = $this->getUserinfo($code);
        if(!$user){
            return $this->responseError('會員不存在');
        }
        $result = $this->repository->create(1,$user->id);
        if(!$result['success']){
            return $this->responseError($result['msg']);
        }
        $notificationService = new NotificationService();
        $notificationService->sendProcessNotification([
            'msg' => $result['content'],
            'member_id' => $user->id,
        ],'coupons');
        return $this->responseSuccess();
    }

}