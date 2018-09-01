<?php
/**
 * Created by PhpStorm.
 * User: zzq
 * Date: 18-6-25
 * Time: 上午11:17
 */

namespace App\Api\Generalize\V1\Controllers\Withdraw;


use App\Api\Generalize\V1\Controllers\BaseController;
use App\Api\Generalize\V1\Validator\ValidatorRule;
use App\BankAccount;
use App\MemberCredits;
use App\Withdrawl;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Console\Helper\Table;

class WithdrawController extends BaseController
{
    public function get($handle,Request $request)
    {
        switch ($handle){
            case 'history':
                return $this->withdrawHistory($request);
            default:
                return $this->responseError('404 Not Found',404001,404);
        }
    }
    public function post($handle,Request $request)
    {
        switch ($handle){
            case 'apply':
                return $this->withdrawApply($request);
            default:
                return $this->responseError('404 Not Found',404001,404);
        }
    }
    private function withdrawHistory(Request$request)
    {
        $uid = $this->getAuthUserId();
        $perPage = empty($request->per_page)?20:(int)$request->per_page;
        $cond = [
            'uid' => $uid,
            'type' => 2,
        ];
        $history = Withdrawl::where($cond)->paginate($perPage);
        $history->appends('per_page',$perPage);
        return $history->toArray();
//        return $this->success($history);
    }
    private function withdrawApply(Request $request)
    {
        $uid = $this->getAuthUserId();

        $data = $request->only(['amount','bank_card_id']);
        $rule = ValidatorRule::$withdrawApply;
        $validator = Validator::make($data,$rule);
        if ($validator->fails())
            return $this->responseError($validator->errors()->first(),42201,422);

        $data['amount'] = number_format($data['amount'],2);

        $cond = [
            'id'=>$data['bank_card_id'],
            'member_id'=>$uid,
        ];
        $bankInfo = BankAccount::where($cond)->first(['bank_name','receiver_name','bank_account','bank_phone']);
        if (!$bankInfo)
            return $this->responseError('bank card not found',40001,400);
        $data = array_merge($data,$bankInfo->toArray());
        $data['uid'] = $uid;$data['type'] = 2;$data['created_at'] = date("Y-m-d H:i:s");

        return $this->doWithdraw($data);
    }
    private function doWithdraw($data)
    {
        try{
            $status = null;
            DB::transaction(function ()use ($data,&$status){
                $credits = MemberCredits::where('member_id',$data['uid'])->first();
                if ($credits->promo_credits>=$data['amount']){
                    $credits->promo_credits -= $data['amount'];
                    $credits->freeze_credits += $data['amount'];
                    $credits->save();
                    Withdrawl::create($data);
                    $status = true;
                }else{
                    $status = false;
                }
            });
            if ($status)
                return $this->success('apply success');
            return $this->responseError('less credits amount',42201,422);
        }catch (\ErrorException $e){
            return $this->responseError('apply failure');
        }
    }
}