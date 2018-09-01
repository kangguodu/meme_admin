<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/19
 * Time: 18:08
 */

namespace App\Api\V1\Repositories;

use App\Collection;
use App\Store;
class CollectionRepository
{
    protected $model;
    public function __construct(Collection $collection)
    {
        $this->model = $collection;
    }
    //搜藏或取消
    public function store($member_id,$store_id){
        $draft = ['member_id'=>$member_id];
        $count = $this->model->where($draft)->whereIn('store_id',$store_id)->count();

       \DB::beginTransaction();
       try{
           $id = 0;
           if($count){
               $id = $this->model->where($draft)->whereIn('store_id',$store_id)->delete();
               $this->updateCollectNumber($store_id,1);
           }else{
               $data = [];
               foreach ($store_id as $v){
                   $data[] = [
                       'member_id' => $member_id,
                       'store_name'  => $this->getStoreNameById($v),
                       'store_id' => $v,
                       'created_at' => time()
                   ];
               }
               $this->model->insert($data);
               $this->updateCollectNumber($store_id,0);
           }

           \DB::commit();

           return $id;

       }catch (\Exception $e){
           \DB::rollback();
           \Log::error("collect fail: ".$e->getMessage().', '.$e->getLine());
           return false;
       }


    }

    //收藏列表
    public function index($member_id,$per_page){
        return $this->model->leftJoin('store','store.id','=','collection.store_id')
                            ->leftJoin('store_data','store_data.store_id','=','collection.store_id')
                            ->select([
                                'collection.id',
                                'collection.store_id',
                                'collection.store_name',
                                'collection.created_at',
                                'branchname',
                                'city',
                                'district',
                                'address',
                                'phone',
                                'email',
                                'type_name',
                                'image',
                                'service_status',
                                'store_data.level',
                                'remark',
                                'avg_cost_status',
                                'avg_cost_low',
                                'avg_cost_high',
                                'routine_holiday',
                                'special_holiday',
                                'special_business_day',
                            ])
                            ->where('member_id',$member_id)
                            ->orderBy('collection.created_at','DESC')
                            ->paginate($per_page);
    }

    private function getStoreNameById($id){
        $data = Store::where('id',$id)->first(['name']);
        if($data){
            return $data->name;
        }
        return '';
    }

    private function updateCollectNumber($store_id,$type){
        if($type==0){
            foreach($store_id as $v){
                $result = (new \App\StoreData())->where('store_id',$v)->first();
                if($result){
                    $result->collect_number =  $result->collect_number + 1;
                    $result->save();
                }else{
                    (new \App\StoreData())->insert(['store_id'=>$v,'collect_number'=>1]);
                }
            }
        }else{
            (new \App\StoreData())->whereIn('store_id',$store_id)->decrement('collect_number',1);
        }


    }
}