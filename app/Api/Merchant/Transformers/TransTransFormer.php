<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/28
 * Time: 14:13
 */

namespace App\Api\Merchant\Transformers;
use League\Fractal\TransformerAbstract;
use App\Api\Merchant\Services\TransService;
class TransTransFormer  extends TransformerAbstract
{
    public function transform($object){
        $result = [
            'id'=>$object->id,
            'trans_type'=>$object->trans_type,
            'trans_type_text' => TransService::getTransTypesText($object->trans_type),
            'trans_category' => $object->trans_category,
            'trans_category_name' => $object->trans_category_name,
            'trans_description' => $object->trans_description,
            'trans_datetime' => $object->trans_datetime,
            'amount' => $object->amount
        ];
        return $result;
    }
}