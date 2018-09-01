<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/29
 * Time: 9:54
 */

namespace App\Api\Merchant\Services;


class MemberCreditsService
{
    public static function initData($member_id){
        return [
            'member_id' => $member_id,
            'total_credits' => 0,
            'grand_total_credits' => 0,
            'wait_total_credits' => 0,
            'freeze_credits' => 0,
            'promo_credits' => 0,
            'promo_credits_total' => 0
        ];
    }

    public static function generateLogNo(){
        return date('ymdHis').uniqid().mt_rand(1,6);
    }

}