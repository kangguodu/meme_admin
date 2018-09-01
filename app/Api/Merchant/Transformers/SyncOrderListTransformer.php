<?php
namespace App\Api\Merchant\Transformers;

use League\Fractal\TransformerAbstract;

class SyncOrderListTransformer extends TransformerAbstract
{

    public function transform($object){
        $result = [
            'id'=>$object->id,
            'date'=>$object->date,
            'order_id'=>$object->order_id,
            'member_id'=>$object->member_id,
            'cycle_point'=>$object->cycle_point,
            'cycle_percent' => 0,
            'cycle_month' => date('m'),
            'cycle_start'=>$object->cycle_start,
            'cycle_end'=>$object->cycle_end,
            'cycle_days'=>$object->cycle_days,
            'cycle_status'=>$object->cycle_status,
            'interest_remain'=>$object->interest_remain,
            'cycle_days_remain'=>$object->cycle_days_remain,
            'interest_ever'=>$object->interest_ever,
            'status' => 1,
            'deleted' => 0,
            'platform_name' => 'memecoins'
        ];
        return $result;
    }
}