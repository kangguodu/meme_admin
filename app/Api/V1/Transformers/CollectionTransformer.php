<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/19
 * Time: 18:18
 */

namespace App\Api\V1\Transformers;

use App\Collection;
use League\Fractal\TransformerAbstract;
use App\Api\V1\Services\BaseService;

class CollectionTransformer extends TransformerAbstract
{
    public function transform(Collection $object){
        $servicetime = $this->getservicetime($object->id,$object->routine_holiday,$object->special_holiday,$object->special_business_day);
        return [
//            'id'   => $object->id,
            'id' => $object->store_id,
            'name' => $object->store_name,
            'created_at' => date('Y-m-d',$object->created_at),
            'branchname'=>$object->branchname,
            'city' =>$object->city,
            'district'=>$object->district,
            'address'=>$object->address,
            'phone'=>$object->phone,
            'email'=>$object->email,
            'type_name'=>$object->type_name,
            'image'=>BaseService::image($object->image),
            'service_status'=> $servicetime['status'],
            'service_time'=> $servicetime['time'],
            'level'=>$object->level ? $object->level : 0.0,
            'remark'=>$object->remark,
            'avg_cost_status'=>$object->avg_cost_status,
            'avg_cost_low'=>$object->avg_cost_low,
            'avg_cost_high' => $object->avg_cost_high,
            'collection_total' => $this->collection_total($object->store_id)
        ];
    }
    private function collection_total($store_id){
        return Collection::where('store_id',$store_id)->count();
    }

    private function getservicetime($store_id,$routine_holiday,$special_holiday,$special_business_day){
        $w = date('w');
        $d = date('d');
        $date = date('Y-m-d');
        $data = (new \App\OpenHourRange())->where('store_id',$store_id)
            ->where('day_of_week',$w)
            ->get();
        $result['status'] = 0;

        if(count($data)){
            foreach ($data as $k=>$v){
                $result['time'] .= date('H:i',strtotime($v->open_time)).' - '.date('H:i',strtotime($v->close_time));
                if($k != count($data)-1){
                    $result['time'] .= ',';
                }
            }
        }else{
            $result['time'] = '休息中';
            $result['status'] = 0;
        }

        if($routine_holiday == $d){
            $result['time'] = '休息中';
            $result['status'] = 0;
        }
        if($special_holiday == $date){
            $result['time'] = '休息中';
            $result['status'] = 0;
        }
        if($special_business_day == $date){
            if(count($data)){
                foreach ($data as $v){
                    $result['time'][] = date('H:i',strtotime($v->open_time)).' - '.date('H:i',strtotime($v->close_time));
                    $result['status'] = 1;
                }
            }else{
                $result['time'] = '特別營業日';
                $result['status'] = 0;
            }

        }
        return $result;
    }

}