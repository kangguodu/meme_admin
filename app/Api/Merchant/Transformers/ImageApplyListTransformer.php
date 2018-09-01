<?php


namespace App\Api\Merchant\Transformers;

use League\Fractal\TransformerAbstract;

class ImageApplyListTransformer extends TransformerAbstract
{
    public function transform($object){
        $result = [
            'id'=>$object->id,
            'status' => $object->status,
            'status_text' => $this->getStatusText( $object->status),
            'other_remark' => $object->other_remark,
            'updated_at' => $object->updated_at,
            'cancel_reason' => $object->cancel_reason?$object->cancel_reason:'',
            'created_at' => $object->created_at,
            'address' => $object->address?$object->address:'',
            'imagesign_carriage' => $object->imagesign_carriage,
        ];
        if(isset($object->items)){
            $result['items'] = $object->items;
        }
        return $result;
    }

    private function getStatusText($status){
        $statuses = array(
            '1' => '待處理',
            '2' => '處理中',
            '3' => '處理完成',
            '4' => '取消'
        );
        return isset($statuses[$status])?$statuses[$status]:'';
    }
}