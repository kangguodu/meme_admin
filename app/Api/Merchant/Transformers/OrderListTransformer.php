<?php

namespace App\Api\Merchant\Transformers;

use App\Api\Merchant\Services\OrderService;
use League\Fractal\TransformerAbstract;

class OrderListTransformer extends TransformerAbstract
{
    public function transform($object){
        $result = [
            'id'=>$object->id,
            'order_no'=>$object->order_no,
            'date' => $object->date,
            'member_id' => $object->member_id,
            'amount' => $object->amount,
            'credits' => $object->credits,
            'coupons_money' => $object->coupons_money,
            'status' => $object->status,
            'status_text' => OrderService::getOrderStatusText($object->status),
            'created_at' => $object->created_at,
            'number' => sprintf('%07d',$object->number),
            'nickname' =>$object->nickname,
            'username' =>$object->username,
            'phone'=>$object->phone,
            'pay' => number_format(($object->amount - $object->credits - $object->coupons_money),2,'.','')
        ];
        return $result;
    }
}