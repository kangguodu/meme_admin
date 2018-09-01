<?php


namespace App\Api\Merchant\Transformers;

use League\Fractal\TransformerAbstract;
class BankListTransformer extends TransformerAbstract
{
    public function transform($object){
        $result = [
            'id' => $object->id,
            'bank_name'=>$object->bank_name,
            'receiver_name'=>$object->receiver_name,
            'bank_account'=>$object->bank_account,
            'bank_phone'=>$object->bank_phone,
            'created_at'=>$object->created_at,
        ];
        return $result;
    }
}