<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/2
 * Time: 16:45
 */

namespace App\Api\V1\Transformers;
use App\Order;
use League\Fractal\TransformerAbstract;

class RebateTransformer extends TransformerAbstract
{
    public function transform(Order $object){
        return [
            'id'   => $object->id,
            'order_id'=> $object->order_id,
            'member_id'=> $object->member_id,
            'cycle_point'=> $object->cycle_point,
            'cycle_start'=> $object->cycle_start,
            'cycle_end'=> $object->cycle_end,
            'interest_remain'=> $object->interest_remain,
            'cycle_days_remain'=> $object->cycle_days_remain,
            'cycle_status'=> $object->cycle_status,
            'interest_ever'=> $object->interest_ever,
            'cycle_days'=> $object->cycle_days,
            'store_id'=> $object->store_id,
            'store_name'=> $object->store_name,
            'amount'=> $object->amount,
            'credits'=> $object->credits,
            'coupons_money'=> $object->coupons_money,
            'created_at'=> $object->created_at
        ];
    }

}