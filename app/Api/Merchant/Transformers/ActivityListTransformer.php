<?php

namespace App\Api\Merchant\Transformers;

use App\Activity;
use League\Fractal\TransformerAbstract;
use Cache;
use Carbon\Carbon;

class ActivityListTransformer extends TransformerAbstract
{
    protected $store_id = 0;
    public function __construct($store_id)
    {
        $this->store_id = $store_id;
    }

    public function transform($object){
        $result = [
            'id'=>$object->id,
            'title'=>$object->title,
            'description' => $object->description,
            'type' => $object->type,
            'created_at' => $object->created_at,
            'created_at_date' => date('Y-m-d',strtotime($object->created_at)),
            'checked' => $object->checked,
            'read_status' => $this->getReadStatus($object->id)
        ];
        return $result;
    }

    private function getReadStatus($activity_id){
        $store_id = $this->store_id;
        $count = Cache::get('store_activity_red_'.$this->store_id.'_'.$activity_id,function() use ($store_id,$activity_id){
            $count = \DB::table('activity_store')->where('activity_id','=',$activity_id)
                ->where('store_id','=',$this->store_id)->count();
            $expireAt = Carbon::now()->addHour(2);
            if($count > 1){
                Cache::put('store_activity_red_'.$store_id.'_'.$activity_id,$count,$expireAt);
            }
            return $count;
        });
        return $count?true:false;
    }

}