<?php
namespace App\Api\Merchant\Services;

use App\Common\Services\BaseNotificationService;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;

class NotificationService extends BaseNotificationService
{
    /**
     * onesignal App Id
     * @var string
     */
    protected $appId    = 'dd420f45-20cb-4e54-b49f-9821fec54cc7';

    /**
     * onesignal Rest Key,use to connect to rest server
     * @var string
     */
    protected $restKey  = 'OWU4OGI3Y2MtODlhOS00ZmNlLWEzMGMtODJmNDQ5ZmJkNjI3';


    /**
     *
     * example: $data = array('order_id' => 10,'user_id' => 12);
     *
     * @param $data
     * @return array
     */
    private function storeCancelOrder($data){
        $fields = array(
            'app_id' => $this->appId,
            'included_segments' => array('All'),
            'data' => array(
                'msg_type' => 'order_cancel',
                'order_id' => $data['order_id'],
                'orderRebate' => 0
            ),
            'headings' => [
                'en' => '訂單取消通知',
                'zh-Hant' => '您的訂單已取消'
            ],
            'contents' => [
                'en' => '真遺憾，您的訂單已取消',
                'zh-Hant' => '真遺憾，您的訂單已取消'
            ],
            'small_icon' => 'ic_stat_onesignal_default',
            'ios_badgeType' => 'Increase',
            'ios_badgeCount' => 1,
            'filters' => array(
                array(
                    'field' => 'tag',
                    'key' => 'id',
                    'relation' => '=',
                    'value' => $data['user_id']
                )
            )
        );
        return $fields;
    }

    /**
     *
     * example: $data = ['order_id' => 1,'user_id' => 12];
     *
     * @param $data
     * @return array
     */
    private function storeCompleteOrder($data){
        $noticeDesc = sprintf(
            config('memecoins.noticeTemplate.walletRebate.description'),
            $data['nickname'],
            date('Y-m-d'),
            $data['name'],
            $data['amount'],
            $data['probability'].'%',
            $data['order_rebate']);
        $fields = array(
            'app_id' => $this->appId,
            'included_segments' => array('All'),
            'data' => array(
                'msg_type' => 'order_success',
                'order_id' => $data['order_id'],
                'orderRebate' => $data['order_rebate']
            ),
            'headings' => [
                'en' => '訂單已處理',
                'zh-Hant' => '您的訂單已處理'
            ],
            'contents' => [
                'en' => '您的訂單已處理：'.$noticeDesc,
                'zh-Hant' => '您的訂單已處理：'.$noticeDesc
            ],
            'small_icon' => 'ic_stat_onesignal_default',
            'ios_badgeType' => 'Increase',
            'ios_badgeCount' => 1,
            'filters' => array(
                array(
                    'field' => 'tag',
                    'key' => 'id',
                    'relation' => '=',
                    'value' => $data['user_id']
                )
            )
        );
        return $fields;
    }

    private function memberAddCoupons($data){
        $fields = array(
            'app_id' => $this->appId,
            'included_segments' => array('All'),
            'data' => array(
                'msg_type' => 'coupons_add',
                'member_id' => $data['member_id'],
            ),
            'headings' => [
                'en' => '恭喜獲得優惠券',
                'zh-Hant' => '恭喜獲得優惠券'
            ],
            'contents' => [
                'en' => $data['msg'],
                'zh-Hant' => $data['msg']
            ],
            'small_icon' => 'ic_stat_onesignal_default',
            'ios_badgeType' => 'Increase',
            'ios_badgeCount' => 1,
            'filters' => array(
                array(
                    'field' => 'tag',
                    'key' => 'id',
                    'relation' => '=',
                    'value' => $data['member_id']
                )
            )
        );
        return $fields;
    }

    public function sendProcessNotification($data,$type='cancel'){
        $fields = array();
        if($type == 'cancel'){
            $fields = $this->storeCancelOrder($data);
        }else if($type == 'complete'){
            $fields = $this->storeCompleteOrder($data);
        }else if($type == 'coupons'){
            $fields = $this->memberAddCoupons($data);
        }
        self::sendNotification($fields);
    }
    /**
     * push notification to onesignal by Rest api
     */
    public static function sendNotification($fields){
        $notificationService = new NotificationService();
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