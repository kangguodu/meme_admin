<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/19
 * Time: 15:00
 */

namespace App\Api\V1\Repositories;


use App\Banner;
use App\Api\V1\Services\BaseService;
use App\Api\Merchant\Services\ImageToolsService;
class BannerRepository
{
    public function index(){
        $banner = Banner::all();
        $data = ['banner'=>[],'advertising'=>[]];
        if($banner->isNotEmpty()){
            $banner = $banner->toArray();
            foreach ($banner as $key=>&$v){
                //$v->image_url = BaseService::image($v->image_url);
                $v['image_url'] = ImageToolsService::getUrlWithDefaultPath($v['image_url'],'banner');
                if($v['use_type'] == 1){
                    $data['banner'][] = $v;
                }else if($v['use_type'] == 2){
                    $data['advertising'][] = $v;
                }
                //$banner[$key] = $v;
            }
            unset($v);
        }

        return $data;
    }

    /**
     * 熱搜人氣
     */
    public function hot($lat,$lng,$size){

        $coordinate = BaseService::getLatAndLngRange($lat,$lng,$size);

        $model = (new \App\Store())->where('store.status',1);
        if($lat && $lng){
            $model = $model->whereBetween('lat',[$coordinate['minLat'],$coordinate['maxLat']])
                ->whereBetween('lng',[$coordinate['minLng'],$coordinate['maxLng']]);
        }
        $data = $model->leftJoin('store_account','store_account.store_id','=','store.id')
            ->leftJoin('store_data','store.id','=','store_data.store_id')
            ->select([
                'store.id',
                'name',
                'branchname',
                'image',
                'type_name',
                'city',
                'district',
                'address',
                'is_return',
                'probability',
                'feature_probability',
                'feature_probability_time',
                'store_data.level',
            ])
            ->orderBy('store_data.number','DESC')
            ->limit(15)
            ->get();

        if($data){
            foreach ($data as $v){
                $v->image = BaseService::image($v->image);
                $v->probability = ($v->is_return) ? (($v->feature_probability_time<=time() && $v->feature_probability_time>0) ? $v->feature_probability : $v->probability) : 0;
                $v->level = $v->level>4.5 ? $v->level : 4.5;
                unset($v->feature_probability_time);
                unset($v->feature_probability);
            }
        }
        return $data;
    }
}