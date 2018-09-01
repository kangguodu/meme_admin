<?php
/**
 * Created by PhpStorm.
 * User: zzq
 * Date: 18-7-23
 * Time: 上午10:45
 */

namespace App\Admin\Provider;

use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;

class Notification
{
    protected $appId    = [
        'member' => 'dd420f45-20cb-4e54-b49f-9821fec54cc7',
        'merchant' => '013a601c-8836-4d1d-8a90-ffb2ed2e0aef',
        'generalize' => '',
    ];

    protected $restKey  = [
        'member' => 'OWU4OGI3Y2MtODlhOS00ZmNlLWEzMGMtODJmNDQ5ZmJkNjI3',
        'merchant' => 'ZTM2ZDllNjgtZTMyNi00ZjcyLWI1NzYtYTkyYzI5NDZmMTE5',
        'generalize' => '',
    ];

    private $platform = 'member';
    private $filed;

    private function activity($platform = 'member', $head, $data)
    {
        $fields = array(
            'app_id' => $this->appId[$platform],
            'included_segments' => array('All'),
            'data' => array(
                'msg_type' => 'all',
                'type' => 1,
                'activity_id' => $data['id'],
                'activity_type' => $data['type'],
                'activity_url' => $data['type']==2?$data['url']:'',
            ),
            'headings' => [
                'en' => $head['title'],
                'zh-Hant' => $head['title']
            ],
            'contents' => [
                'en' => $head['description'],
                'zh-Hant' => $head['description']
            ],
            'small_icon' => 'ic_stat_onesignal_default',
            'ios_badgeType' => 'Increase',
            'ios_badgeCount' => 1,
        );
        return $fields;
    }

    private function system($platform = 'member', $head, $data)
    {
        $fields = array(
            'app_id' => $this->appId[$platform],
            'included_segments' => array('All'),
            'data' => array(
                'msg_type' => 'all',
                'type' => 3,
                'id' => $data['id'],
            ),
            'headings' => [
                'en' => $head['title'],
                'zh-Hant' => $head['title']
            ],
            'contents' => [
                'en' => $head['description'],
                'zh-Hant' => $head['description']
            ],
            'small_icon' => 'ic_stat_onesignal_default',
            'ios_badgeType' => 'Increase',
            'ios_badgeCount' => 1,
        );
        return $fields;
    }

    /**
     * @param $platform 'member|generalize|merchant'
     * @param $head array(title,description)
     * @param $type 'system|activity'
     * @param null $data 'activity_id'
     * @return $this
     */
    public function prepare($platform, $head, $type, $data = null)
    {
        switch ($type){
            case 'system':
                $filed  = $this->system($platform, $head, $data);
                break;
            case 'activity':
                $filed  = $this->activity($platform, $head, $data);
                break;
            default:
                return false;
        }
        $this->filed = $filed;
        $this->platform = $platform;
        return $this;
    }

    public function send()
    {
//        $notificationService = new NotificationService();
        $fields = count($this->filed)?$this->filed:array();
        $fields = json_encode($fields);
        try{
            $client = new Client();
            $promise = $client->requestAsync('POST','https://onesignal.com/api/v1/notifications',[
                'headers' =>array(
                    'Content-Type'=>'application/json',
                    'Authorization'=>'Basic '.$this->restKey[$this->platform]
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