<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/25
 * Time: 15:12
 */

namespace App\Api\V1\Transformers;

use App\Order;
use League\Fractal\TransformerAbstract;
use App\Api\V1\Services\BaseService;

class OrderTransformer extends TransformerAbstract
{
    public function transform(Order $object){
        return [
            'id'   => $object->id,
            'order_no' =>$object->order_no,
            'date'=> $object->date,
            'store_id' => $object->store_id,
            'store_name'=> $object->store_name,
            'amount'=> $object->amount,
            'created_at'=> $object->created_at,
            'credits' => $object->credits,
            'coupons_money'=> $object->coupons_money,
            'image' => BaseService::image($object->image),
            'status' => $object->status,
            'is_evaluate' => $object->is_evaluate
        ];
    }


}