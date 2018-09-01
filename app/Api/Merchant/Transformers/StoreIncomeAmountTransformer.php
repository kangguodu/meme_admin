<?php
namespace App\Api\Merchant\Transformers;
use League\Fractal\TransformerAbstract;

class StoreIncomeAmountTransformer extends TransformerAbstract
{
    public function transform($object){
        $result = [
            'business_income'=>$object->business_income,
            'credits_income'=>$object->credits_income
        ];
        if(isset($object->bank_count)){
            $result['bank_count'] = $object->bank_count;
        }
        if(isset($object->withdrawl_count)){
            $result['withdrawl_count'] = $object->withdrawl_count;
        }
        return $result;
    }
}