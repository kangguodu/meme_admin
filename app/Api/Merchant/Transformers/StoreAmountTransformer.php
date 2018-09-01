<?php
namespace App\Api\Merchant\Transformers;

use League\Fractal\TransformerAbstract;
use App\StoreAccount;
class StoreAmountTransformer extends TransformerAbstract
{
    public function transform($object){
        $result = [
            'return_credits'=>$object->return_credits,
            'probability'=>$object->probability,
            'fixed_probability' => $object->fixed_probability,
            'feature_probability' => $object->feature_probability,
            'feature_probability_time' => $object->feature_probability_time?date('Y-m-d H:i:s',$object->feature_probability_time):0,
            'feature_fixed_probability' => $object->feature_fixed_probability,
            'feature_fixed_probability_time' => $object->feature_fixed_probability_time?date('Y-m-d H:i:s',$object->feature_fixed_probability_time):0,
            'is_return' => $this->getStatus($object->store_id)
        ];
        return $result;
    }

    private function getStatus($store_id){
       $data = (new \App\Store())->where('id',$store_id)->first(['is_return']);
       return $data->is_return;
    }
}