<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/28
 * Time: 10:56
 */

namespace App\Api\Merchant\Transformers;

use League\Fractal\TransformerAbstract;
class ImageSignFormTransformer extends TransformerAbstract
{
    public function transform($object){
        $result = [
            'id'=>$object->id,
            'title'=> $object->title,
            'price'=> $object->price,
            'quantity' => 0
        ];
        return $result;
    }
}