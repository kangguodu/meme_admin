<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/19
 * Time: 16:49
 */

namespace App\Api\V1\Transformers;

use App\Store;
use League\Fractal\TransformerAbstract;
use App\Comment;
use App\Collection;
use App\Api\V1\Services\BaseService;

class StoreTransformer extends TransformerAbstract
{
    public function transform(Store $object){
        $servicetime = BaseService::getservicetime($object->id,$object->routine_holiday,$object->special_holiday,$object->special_business_day);
        $result = [
            'id'  => $object->id,
            'store_id'=>$object->store_id,
            'name'=>$object->name,
            'branchname'=>$object->branchname,
            'image' => BaseService::image($object->image),
            'level'  => $object->level>4.5 ? $object->level : 4.5,
            'type_name'=> $object->type_name,
            'avg_cost_low'=> $object->avg_cost_low,
            'avg_cost_high'=>$object->avg_cost_high,
            'avg_cost_status'=>$object->avg_cost_status,
            'phone'=> $object->phone,
            'city'=> $object->city,
            'district'=> $object->district,
            'address'=> $object->address,
            'service_status'=> $servicetime['status'],
            'service_time'=> $servicetime['time'],
            'routine_holiday'=> $object->routine_holiday ? $object->routine_holiday : 0,
            'special_holiday'=> ($object->special_holiday && $object->special_holiday != '0000-00-00') ? $object->special_holiday : '',
            'special_business_day'=> ($object->special_business_day && $object->special_business_day != '0000-00-00') ? $object->special_business_day : '',
            'distance'=>round($object->distance),
            'lat'=>$object->lat,
            'lng'=>$object->lng,
            'collection_total'=>$object->collect_number ? $object->collect_number : 0,
            'number'=>$object->number ? $object->number : 0,
            'recommend_rank'=>$object->recommend_rank ? $object->recommend_rank : 0,
            'comment_number'=>$object->comment_number ? $object->comment_number : 0,
            'click_number'=>$object->click_number ? $object->click_number : 0,
            'probability'=>($object->is_return) ? (($object->feature_probability_time<=time() && $object->feature_probability_time>0) ? $object->feature_probability : $object->probability) : 0,
            'comments' => $object->comments,
            'goods'=> $object->goods,
        ];

        return $result;

    }




}