<?php

namespace App\Api\Merchant\Transformers;
use App\Api\Merchant\Services\TransService;
use League\Fractal\TransformerAbstract;

class TransListTransFormer  extends TransformerAbstract
{
    public function transform($object){
        $result = [
            'id'=>$object->id,
            'trans_type'=>$object->trans_type,
            'trans_type_text' => TransService::getTransTypesText($object->trans_type),
            'trans_category' => $object->trans_category,
            'trans_category_name' => $object->trans_category_name,
            'trans_description' => $object->trans_description,
            'trans_date' => $object->trans_date,
            'trans_datetime' => $object->trans_datetime,
            'amount' => $object->amount,
            'balance' => $object->balance,
            'created_at' => $object->created_at,
            'created_by' => $object->created_by,
            'created_name' => $object->created_name,
        ];
        return $result;
    }
}