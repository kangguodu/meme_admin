<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/29
 * Time: 10:30
 */

namespace App\Api\V1\Transformers;

use App\Goods;
use League\Fractal\TransformerAbstract;

class Goodsransformer extends TransformerAbstract
{
    public function transform(Goods $object){
        return [
            'id' => $object->id,
            'store_id' => $object->store_id,
            'store_name'=>$object->store_name,
            'goods_name' => $object->goods_name,
            'image' => $object->image,
            'price' => $object->price,
            'prom_price' => $object->prom_price,
            'level' => $object->level,
            'number' => $object->number,
            'distance' => $object->distance,
        ];
    }

}