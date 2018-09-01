<?php
namespace App\Api\Merchant\Repositories;

use App\Activity;
use App\ActivityStore;
use App\ImageSign;
use App\ImageSignApply;
use App\ImageSignDetail;
use App\Options;
use Cache;
use Carbon\Carbon;

class OtherRepository
{
    public function getSimpleActivity(){
        return (new Activity())
            ->leftJoin('activity_store','activity_store.activity_id','=','activity.id')
            ->where('activity.platform_type','=',2)
            ->where('activity.checked','=',1)
            ->limit(2)
            ->orderBy('id','DESC')
            ->select([
                'activity.id',
                'activity.title',
                'activity.description',
                'activity.type',
                'activity.created_at',
                'activity.checked',
                'activity_store.store_id',
                'activity_store.status',
            ])->get();
    }

    public function getActivityUnreadCount($store_id){
        $total = (new Activity())->where('platform_type','=',2)
            ->where('checked','=',1)
            ->count();
//        $total = Cache::get('store_activity_total_'.$store_id,function() use ($store_id){
//
//            $expireAt = Carbon::now()->addHour(2);
//            Cache::put('store_activity_total_'.$store_id,$total,$expireAt);
//            return $total;
//        });
        $storeReadCount = (new ActivityStore())
            ->leftJoin('activity','activity.id','=','activity_store.activity_id')
            ->where('activity.platform_type','=',2)
            ->where('activity.checked','=',1)
            ->where('activity_store.store_id','=',$store_id)
            ->where('activity_store.status','=',1)
            ->count();
//        $storeReadCount = Cache::get('store_activity_read_'.$store_id,function () use ($store_id){
//
//            $expireAt = Carbon::now()->addHour(2);
//            Cache::put('store_activity_read_'.$store_id,$total,$expireAt);
//            return $total;
//        });
        \Log::debug("msg count: {$total} {$storeReadCount}");
        if($total >= $storeReadCount){
            return intval($total - $storeReadCount);
        }else{
            return 0;
        }
    }


    public function getActivityList($per_page){
        return (new Activity())
            ->where('platform_type','=',2)
            ->where('checked','=',1)
            ->orderBy('id','DESC')
            ->select([
                'id',
                'title',
                'description',
                'type',
                'created_at',
                'checked'
            ])->paginate($per_page);
    }

    public function getActivityDetail($id,$store_id){
        $info =  (new Activity())->where('id','=',$id)
            ->where('platform_type','=',2)
            ->where('checked','=',1)
            ->first([
                'id',
                'title',
                'type',
                'content',
                'created_at'
            ]);
        if($info){
            $count = \DB::table('activity_store')->where('store_id','=',$store_id)
                ->where('activity_id','=',$id)
                ->count();
            if($count <= 0){
                \DB::table('activity_store')->insert([
                    'store_id' => $store_id,
                    'activity_id' => $id,
                    'status' => 1
                ]);
            }else{
                \DB::table('activity_store')->where('store_id','=',$store_id)
                    ->where('activity_id','=',$id)
                    ->update([
                        'status' => 1
                    ]);
            }
            //清空缓存
//            Cache::forget('store_activity_total_'.$store_id);
//            Cache::forget('store_activity_read_'.$store_id);
        }
        return $info;
    }

    public function getImageSignForm(){
        $date = date('Y-m-d');
        $result = (new ImageSign())->whereRaw("'{$date}' between start_at and end_at")
            ->orderBy('id','DESC')->select([
                'id',
                'title',
                'price'
            ])->get();
        if($result->isNotEmpty()){
            foreach ($result as $key=>$value){
                $result[$key] = $value;
            }

        }else{
            $result = array();
        }
        return $result;
    }


    public function getProcessISACount($store_id){
        return  (new ImageSignApply())->where('store_id','=',$store_id)
            ->where('status','=',1)
            ->orWhere('status','=',2)
            ->count();
    }

    public function getImageSignCarriage(){
        try{
            $result =  (new Options())->where('option_name','=','imagesign_carriage')
                ->first([
                    'option_value'
                ]);
            if($result){
                return number_format($result->option_value * 1,2,'.','');
            }else{
                return number_format(0,2,'.','');
            }
        }catch (\Exception $e){
            \Log::error("get option value fail:".$e->getMessage().' '.$e->getFile().' '.$e->getLine());
            return number_format(0,2,'.','');
        }
    }

    public function addImageSignApply($store_id,$params){
        if(!isset($params['items']) || empty($params['items'])){
            return false;
        }
        $items = array();
        if(is_string($params['items'])){
            $items = json_decode($params['items'],true);
            if($items === false || $items === NULL){

                return false;
            }
        }else if(is_array($params['items']) && count($params['items']) <= 0){
            return false;
        }else if(is_array($params['items'])){
            $items = $params['items'];
        }

        \DB::beginTransaction();
        try{
            $data = array(
                'store_id' => $store_id,
                'other_remark' => isset($params['other_remark'])?$params['other_remark']:'',
                'status' => 1,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
                'address' => isset($params['address'])?$params['address']:'',
                'imagesign_carriage' => isset($params['imagesign_carriage'])?$params['imagesign_carriage']:0,
            );
            $id = (new ImageSignApply())->insertGetId($data);
            $imageSigns = $this->getImageSignForm();
            if(count($imageSigns) <= 0 || count($items) <= 0){
                \DB::rollback();
                return false;
            }
            $itemsData = array();
            foreach ($imageSigns as $key=>$value){
                if(count($items) <= 0){
                    break;
                }
                foreach ($items as $itemKey=>$itemVal){
                    if($itemVal['id'] == $value->id){
                        $itemsData[] = array(
                            'apply_id' => $id,
                            'quantity' => $itemVal['quantity'],
                            'amount' => $itemVal['quantity'] * $value->price,
                            'image_sign_id' => $itemVal['id']
                        );
                        unset($items[$itemKey]);
                        break;
                    }
                }
            }

            if(count($itemsData) <= 0){
                \DB::rollback();
                return false;
            }
            (new ImageSignDetail())->insert($itemsData);
            \DB::commit();
        }catch (\Exception $e){
            \Log::error("add image apply fail".$e->getMessage().' '.$e->getFile().' '.$e->getLine());
            return false;
        }

        return true;
    }


    public function getImageSignApply($store_id,$params){
        $result = (new ImageSignApply())->where('store_id','=',$store_id)->where('id','=',$params['id'])->first();

        $items = (new ImageSignDetail())->leftJoin('image_sign','image_sign.id','=','image_sign_apply_detail.image_sign_id')
            ->where('image_sign_apply_detail.apply_id','=',$params['id'])
            ->select([
                'image_sign_apply_detail.quantity',
                'image_sign_apply_detail.amount',
                'image_sign_apply_detail.id',
                'image_sign_apply_detail.image_sign_id',
                'image_sign.title',
                'image_sign.price',
            ])->get();
        $itemsData = array();
        if($items->isNotEmpty()){
            foreach ($items as $key=>$value){
                $itemsData[] = array(
                    'id' => $value->id,
                    'image_sign_id' => $value->image_sign_id,
                    'title' => $value->title,
                    'price' => $value->price,
                    'quantity' => $value->quantity,
                    'amount' => $value->amount,
                );
            }
        }

        if($result){
            $result->items = $itemsData;
            return $result;
        }else{
            return array();
        }
    }

}