<?php
/**
 * Created by PhpStorm.
 * User: zzq
 * Date: 18-6-23
 * Time: 下午2:24
 */

namespace App\Api\Generalize\V1\Controllers\User;


use App\Api\Generalize\V1\Controllers\BaseController;
use App\BankAccount;
use Illuminate\Http\Request;

class BankController extends BaseController
{
    public function get($handle)
    {
        switch ($handle)
        {
            case 'accountinfo':
                return $this->bankAccount();
            default:
                return $this->responseError('404 Not Found',404001,404);
        }
    }
    public function post($handle,Request $request)
    {
        switch ($handle)
        {
            case 'alter':
                return $this->alterBank($request);
            default:
                return $this->responseError('404 Not Found',404001,404);
        }
    }
    private function bankAccount()
    {
        $memberId = $this->getAuthUserId();
        return $bankAccount = BankAccount::where('member_id',$memberId)->get();
//        return $this->success($bankAccount);
//        if ($bankAccount){
//            return $this->success($bankAccount);
//        }else{
//            BankAccount::create(['member_id'=>$memberId,'created_at'=>date('Y-m-d H:i:s')]);
//            return $this->bankAccount();
//        }
    }
    private function alterBank($request)
    {
        $memberId = $this->getAuthUserId();
        $data = $this->checkPostData($request);
        if ($data){
            $cond= ['member_id' => $memberId,];

            if (isset($data['id'])){
                $cond['id'] = $data['id'];
                return $this->alter($data,$cond);
            }else{
                $data['member_id'] = $memberId;
                $account = BankAccount::create($data);
                if ($account)
                    return $account;
//                    return $this->success('create success');
            }
        }else{
            return $this->responseError('bank info not given',40001,400);
        }
    }
    private function checkPostData($request)
    {
        $data=null;
        empty($request->id)?:$data['id'] = (int)$request->id;
        empty($request->bank_name)?:$data['bank_name'] = $request->bank_name;
        empty($request->bank_account)?:$data['bank_account'] = $request->bank_account;
        empty($request->bank_phone)?:$data['bank_phone'] = $request->bank_phone;
        empty($request->receiver_name)?:$data['receiver_name'] = $request->receiver_name;
        return $data;
    }
    private function alter($data,$cond)
    {
        $account = BankAccount::where($cond)->first();
        if ($account){
            $account->update($data);
            return $this->success('alter success');
        }
        return $this->notFoundResponse($account);
    }
}