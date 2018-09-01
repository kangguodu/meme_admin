<?php
namespace App\Api\Merchant\Transformers;

use League\Fractal\TransformerAbstract;
class WithDrawlListTransformer extends TransformerAbstract
{
    public function transform($object){
        $result = [
            'id'=>$object->id,
            'amount'=>$object->amount,
            'status' => $object->status,
            'remark'=>$object->remark,
            'service_charge' => $object->service_charge,
            'bank_name'=>$object->bank_name,
            'receiver_name'=>$object->receiver_name,
            'bank_account'=> $object->bank_account,
            'bank_phone' => $object->bank_phone,
            'handle_note' => $object->handle_note,
            'handle_date' => $object->handle_date,
            'created_at' => $object->created_at,
        ];
        return $result;
    }
}