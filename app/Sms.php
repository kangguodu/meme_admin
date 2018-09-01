<?php

namespace App;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7;
use GuzzleHttp\Exception\RequestException;

/**
 * 發送短信消息 (Send sms logic Class)
 * @version 1.0
 * @package App
 * @category Class
 * @author xfs
 */
class Sms
{
    private $accessyou_account = '42888818';
    private $accessyou_pwd     = 'ls23012801';

    private $host = 'http://api.every8d.com/API21/HTTP/SendSMS.ashx';

    /**
     * 發送sms驗證碼
     * @param  string $msg urlencode message
     * @param  string $phone phone number
     */
    public function sendSms($msg,$phone){
        if(strlen($phone) < 9){
            return true;
        }
        if(strpos($phone,"+8860") === 0){
            $phone = str_replace("+8860", "+886", $phone);
        }
        //$msg=	urlencode("{$code} 是您的跑腿王驗證碼.");
        $url = $this->host.'?SB=&MSG='.$msg.'&DEST='.$phone.'&PWD='.$this->accessyou_pwd.'&UID='.$this->accessyou_account.'&ST=';
        try{
            $client = new Client();
            $promise = $client->get($url);

            //var_dump($promise);
            // $promise->then(
            //   function (ResponseInterface $res) {
            //       echo $res->getStatusCode() . "\n";
            //   },
            //   function (RequestException $e) {
            //       echo $e->getMessage() . "\n";
            //       echo $e->getRequest()->getMethod();
            //   }
            // );
        }catch(RequestException $e){
            \Log::error("send sms".$e->getMessage());
            // echo Psr7\str($e->getRequest());
            // if ($e->hasResponse()) {
            //     echo Psr7\str($e->getResponse());
            // }
        }

    }

    /**
     * create phone rand number,length 6
     * @link http://stackoverflow.com/a/24884343/5840474
     * @return string
     */
    public function crateSmsCode(){
        // generate a pin based on 2 * 7 digits + a random character
        $pin = mt_rand(100, 999)
            . mt_rand(100, 999);
        // shuffle the result
        $string = str_shuffle($pin);
        return $string;
    }

    /**
     * 主要用于给电话号码添加TW phone zone 886
     */
    public function checkPhone($phone,$zone = ''){
        if(!empty($zone)){
            return $zone.$phone;
        }else if(strpos($phone,"886") !== 0){
            $phone = '886'.$phone;
        }else if(strpos($phone,"+886") === 0){
            $phone = str_replace("+886", "886", $phone);
        }
        return $phone;

    }
}
