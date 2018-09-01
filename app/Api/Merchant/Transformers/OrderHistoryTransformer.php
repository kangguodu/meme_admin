<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/30
 * Time: 10:07
 */

namespace App\Api\Merchant\Transformers;

use App\Api\Merchant\Services\OrderService;
use League\Fractal\TransformerAbstract;

class OrderHistoryTransformer extends TransformerAbstract
{
    public function transform($object){
        $result = [
            'id'=>$object->id,
            'order_no'=>$object->order_no,
            'status' => $object->status,
            'status_text' => OrderService::getOrderStatusText( $object->status),
            'amount' => $object->amount,
            'credits' => $object->credits,
            'number' => sprintf('%07d',$object->number),
            'coupons_money' => $object->coupons_money,
            'updated_at' => $object->updated_at,
            'name' => $object->nickname?$object->nickname:'',
            'member_username' => $object->member_username?$object->member_username:'',
            'member_nickname' => $object->member_nickname?$object->member_nickname:'',
            'pay' => number_format(($object->amount - $object->credits - $object->coupons_money),2,'.','')
        ];
        return $result;
    }
}