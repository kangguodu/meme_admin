<?php
namespace App\Api\Merchant\Transformers;

use League\Fractal\TransformerAbstract;
class ActivityDetailTransformer extends TransformerAbstract
{
    public function transform($object){
        $result = [
            'id'=>$object->id,
            'title'=>$object->title,
            'content' => $object->content,
            'type' => $object->type,
            'created_at' => $object->created_at,
        ];
        return $result;
    }
}