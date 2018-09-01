<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/14
 * Time: 15:56
 */

namespace App\Api\Merchant\Transformers;

use League\Fractal\TransformerAbstract;
use App\NoticeLog;

class NoticeLogTransformer extends TransformerAbstract
{
    protected $store_id = 0;
    public function __construct($store_id)
    {
        $this->store_id = $store_id;
    }
    public function transform($object){
        $result = [
            'id'=>$object->id,
            'title'=> $object->title,
            'status' => $this->getStatus($object->id,$this->store_id),
            'description' => $object->description,
            'created_at_date' =>date('Y-m-d', strtotime($object->created_at))
        ];
        return $result;
    }

    private function getStatus($id,$store_id){
       $data = (new \App\NoticeBoss())->where('notice_id',$id)->where('store_id',$store_id)->first();
       if($data){
           return 1;
       }
       return 0;
    }

}