<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/29
 * Time: 9:54
 */

namespace App\Api\V1\Controllers;
use Illuminate\Http\Request;

use App\Api\V1\Repositories\GoodsRepository;
use App\Api\V1\Transformers\Goodsransformer;

class GoodsController extends BaseController
{
    protected $repository;
    public function __construct(GoodsRepository $goodsRepository)
    {
        $this->repository = $goodsRepository;
    }

    public function query(Request $request){
        $orderby = $request->get('orderby','');
        $lat  = $request->get('lat',0);
        $lng  = $request->get('lng',0);
        $per_page  = $request->get('per_page',10);
        $type  = $request->get('type',0);
        $result = $this->repository->query($orderby,$lat,$lng,$per_page,$type);
        if($orderby){
            $result->appends('orderby',$orderby);
        }
        if($lat){
            $result->appends('lat',$lat);
        }
        if($lng){
            $result->appends('lng',$lng);
        }
        $result->appends('per_page',$per_page);
        return $this->response()->paginator($result,new Goodsransformer());

    }

}