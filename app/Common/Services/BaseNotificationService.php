<?php
namespace App\Common\Services;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;

class BaseNotificationService
{
    /**
     * onesignal App Id
     * @var string
     */
    protected $appId    = '013a601c-8836-4d1d-8a90-ffb2ed2e0aef';

    /**
     * onesignal Rest Key,use to connect to rest server
     * @var string
     */
    protected $restKey  = 'ZTM2ZDllNjgtZTMyNi00ZjcyLWI1NzYtYTkyYzI5NDZmMTE5';



    public function setAppId($appId){
        $this->appId = $appId;
    }

    public function setRestKey($restKey){
        $this->restKey = $restKey;
    }


    function cutstr($string, $length, $dot = ' ...'){
        $charset = 'utf-8';
        if(mb_strlen($string,$charset) <= $length){   //边界条件
            return $string;
        }

        $string = str_replace(array('&', '"', '<', '>'), array('&', '"', '<', '>'), $string);
        $strcut = '';
        if(strtolower($charset) == 'utf-8') {
            $n = $tn = $noc = 0;
            while($n < mb_strlen($string,$charset)) {
                $t = ord($string[$n]);
                if($t == 9 || $t == 10 || (32 <= $t && $t <= 126)) {
                    $tn = 1; $n++; $noc++;
                } elseif(194 <= $t && $t <= 223) {
                    $tn = 2; $n += 2; $noc += 2;
                } elseif(224 <= $t && $t <= 239) {
                    $tn = 3; $n += 3; $noc += 2;
                } elseif(240 <= $t && $t <= 247) {
                    $tn = 4; $n += 4; $noc += 2;
                } elseif(248 <= $t && $t <= 251) {
                    $tn = 5; $n += 5; $noc += 2;
                } elseif($t == 252 || $t == 253) {
                    $tn = 6; $n += 6; $noc += 2;
                } else {
                    $n++;
                }
                if($noc >= $length) {
                    break;
                }
            }
            if($noc > $length)
            {
                $n -= $tn;
            }
            $strcut = mb_substr($string, 0, $n,$charset);
        } else{
            for($i = 0; $i < $length; $i++){
                $strcut .= ord($string[$i]) > 127 ? $string[$i].$string[++$i] : $string[$i];
            }
        }
        $strcut = str_replace(array('&', '"', '<', '>'), array('&', '"', '<', '>'), $strcut);
        return $strcut.$dot;
    }


    public function storeTestMsg(){
        $fields = array(
            'app_id' => $this->appId,
            'included_segments' => array('All'),
            'data' => array(
                'msg_type' => 'single',
                'order_id' => 10 * 1
            ),
            'headings' => [
                'en' => '测试推送',
                'zh-Hant' => '你有新的訂單待處理'
            ],
            'contents' => [
                'en' => '你有新的訂單待處理',
                'zh-Hant' => '你有新的訂單待處理'
            ],
            'small_icon' => 'ic_stat_onesignal_default',
            'ios_badgeType' => 'Increase',
            'ios_badgeCount' => 1,
            'filters' => array(
                array(
                    'field' => 'tag',
                    'key' => 'id',
                    'relation' => '=',
                    'value' => 9999
                )
            )
        );
        return $fields;
    }


    public static function sendMessage($fields){
        self::sendNotification($fields);
    }

    public static function testStoreMessage(){
        $notificationService = new BaseNotificationService();
        $fields = $notificationService->storeTestMsg();
        self::sendMessage($fields);
    }


    /**
     * push notification to onesignal by Rest api
     */
    public static function sendNotification($fields){
        $notificationService = new BaseNotificationService();
        $fields = count($fields)?$fields:array();
        $fields = json_encode($fields);
        try{
            $client = new Client();
            $promise = $client->requestAsync('POST','https://onesignal.com/api/v1/notifications',[
                'headers' =>array(
                    'Content-Type'=>'application/json',
                    'Authorization'=>'Basic '.$notificationService->restKey
                ),
                'body'=>$fields
            ]);
            $promise->then(
                function (ResponseInterface $res){
                    $body = $res->getBody();
                    $stringBody = (string) $body;
                    \Log::debug("onesignal: ".$stringBody);
                },
                function (RequestException $e) {
                    \Log::debug("onesignal error: ".$e->getMessage().'  '.$e->getRequest()->getMethod());
                }
            );
            $promise->wait();
        }catch (RequestException $e) {
            \Log::error("send fail");
        }
    }
}