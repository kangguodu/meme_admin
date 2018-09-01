<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/29
 * Time: 9:58
 */

namespace App\Api\V1\Repositories;

use App\Goods;

class GoodsRepository
{
    protected $model;
    public function __construct(Goods $goods)
    {
        $this->model = $goods;
    }

    public function query($orderby,$lat,$lng,$per_page,$type){
        $query = $this->model->join('store','store_id','=','store.id')
            ->select([
                'goods.id',
                'goods.goods_name',
                'goods.store_id',
                'goods.store_name',
                'goods.image',
                'goods.price',
                'goods.prom_price',
                'goods.level',
                'goods.number',
            ])
            ->addSelect(\DB::raw("acos(cos(".$lat."*pi()/180)*cos(lat*pi()/180)*cos(".$lng."*pi()/180-lng*pi()/180)+sin(".$lat."*pi()/180)*sin(lat * pi()/180)) * 6367000 AS distance"));

        switch($type){

            case 1:

                break;

            case 2:
                $query = $query->whereBetween('created_at',[date('Y-m').'-01',date('Y-m-t')]);
                break;

            case 3:



        }
        switch($orderby){

            case 1:
                $data =  $query ->orderBy('distance','ASC')->paginate($per_page);
                break;


            case 2:
                $data =  $query->orderBy('distance','DESC')->paginate($per_page);
                break;


            case 3:
                $data =  $query->orderBy('level','DESC')->paginate($per_page);
                break;

            case 4:
                $data =  $query->orderBy('prom_price','ASC')->paginate($per_page);
                break;

            case 5:
                $data =  $query->orderBy('prom_price','DESC') ->paginate($per_page);
                break;

            default :
                $data =  $query->orderBy('created_at','DESC')->paginate($per_page);
                break;
        }

        return $data;

    }


}