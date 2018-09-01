<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/3/15
 * Time: 9:37
 */

namespace App\Api\V1\Repositories;

use Config;
use App\Sms;
use App\Verification;

class ToolsRepository
{
    public function sms($credentials,$zone){
        $phone = trim($credentials['phone']);
        if(isset($credentials['zone']) && trim($credentials['zone'])){
            $zone = trim($credentials['zone']);
        }

        $smsModel = new Sms();
        $smsCode = $smsModel->crateSmsCode();
        $msg = urldecode("您的驗證碼為 {$smsCode}，請於3分鐘內在APP上輸入此驗證碼，以確保您的會員身份並維護相關權益。歡迎你/妳加入 MeMeCoins 。");

        \Log::debug("phone:".$phone);
        $smsModel->sendSms($msg,$phone);

        \DB::table('verification')->where('verification_account', '=', $phone)->where('zone', '=', $zone)->delete();

        $verification = new Verification();

        $verification->verification_account = $phone;
        $verification->zone = $zone;
        $verification->verification_type = 1;
        $verification->verification_code = $smsCode;
        $verification->send_at = time();
        $verification->save();

        return array('code' => $smsCode);
    }

    public function version($type){
        return (new \App\Version())->where('type',$type)->get();
    }



}