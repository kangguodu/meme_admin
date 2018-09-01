<?php
/**
 * Created by PhpStorm.
 * User: zzq
 * Date: 18-7-3
 * Time: 下午4:13
 */

namespace App\Api\Generalize\V1\Controllers\Credit;


use App\Api\Generalize\V1\Controllers\BaseController;
use App\MemberCredits;
use App\MemberCreditsLog;
use Illuminate\Http\Request;

class CreditController extends BaseController
{
    public function get($handle,Request $request)
    {
        switch ($handle){
            case "info":
                return $this->creditInfo();
        }
    }

    private function creditInfo()
    {
        $uid = $this->getAuthUserId();
        $credit = MemberCredits::where('member_id',$uid)->first(['freeze_credits','promo_credits','promo_credits_total']);
        if ($credit){
            return $credit->toArray();
        }else{
            return $this->responseError('用戶錢包沒有撥現',400,400);
        }
    }
}