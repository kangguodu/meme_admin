<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/3
 * Time: 14:33
 */

namespace App\Api\V1\Repositories;
use App\Jobs\SendEmail;

class OtherRepository
{
    /**
     * 商家入駐
     */
    public function create($credentials){

        $data = [
            'name' => trim($credentials['name']),
            'phone'=> trim($credentials['phone']),
            'company_name'=> trim($credentials['company_name']),
            'company_tax_no'=> trim($credentials['company_tax_no']),
            'type_name'=> $credentials['type_name'],
            'city'=> trim($credentials['city']),
            'address'=> trim($credentials['address']),
            'other'=> isset($credentials['other']) ? trim($credentials['other']) : '',
            'created_at' => date('Y-m-d H:i:s'),
            'status' => 0,
        ];

       $id = (new \App\StoreApply())->insertGetId($data);
       if($id){
           $data['flag'] = 1;
           $job = (new SendEmail($data))->onQueue('sendemail')->delay(1);
           dispatch($job);
       }
       return $id;

    }

    /**
     * 反饋
     */
    public function feedback($credentials){
        $data = [
            'store_id' => $credentials['store_id'],
            'content'  => $credentials['content'],
        ];
        if(isset($credentials['description']) && trim($credentials['description'])){
            $data['description'] = trim($credentials['description']);
        }
        return (new \App\Feedback())->insertGetId($data);
    }

    /**
     * 我要合作
     */
    public function cooperation($credentials){
        $data = [
            'username' => trim($credentials['username']),
            'phone'=> trim($credentials['phone']),
            'company_name'=> trim($credentials['company_name']),
            'company_tax_no'=> trim($credentials['company_tax_no']),
            'type_name'=> trim($credentials['type_name']),
            'direction'=> trim($credentials['direction']),
        ];
        $id = (new \App\Cooperation())->insertGetId($data);
        if($id){
            $data['flag'] = 2;
            $job =(new SendEmail($data))->onQueue('sendemail')->delay(1);
            dispatch($job);
       }
       return $id;
    }

    /**
     *媒體聯繫
     */
    public function media($credentials){
        $data = [
            'username' => trim($credentials['username']),
            'phone'=> trim($credentials['phone']),
            'company_name'=> trim($credentials['company_name']),
            'report_content'=> trim($credentials['report_content']),
        ];
        return (new \App\Media())->insertGetId($data);
    }
    //根據地址獲取經緯度
    public function getLatAndLng($address){
        if (!is_string($address))die("All Addresses must be passed as a string");
        $_result = file_get_contents('http://maps.google.cn/maps/api/geocode/json?address='.$address);
        $_coords = json_decode($_result,true);
        if($_coords){
            foreach ($_coords as $key => $value) {
                if($key == 'error_message'){
                    return $this->getLatAndLng($address);
                }

            }
        }
        if(!isset($_coords['results'][0])){
            return array();
        }
        $Lat = $_coords['results'][0]['geometry'];
        $result = [];
        foreach ($Lat as $key => $value) {
            if($key == 'location'){
                $result['lat'] = $value['lat'];
                $result['lng'] = $value['lng'];
            }
        }
        return $result;
    }

    public function region(){
       return (new \App\RegionsList())->where('parent_id',1)->get();
    }
}