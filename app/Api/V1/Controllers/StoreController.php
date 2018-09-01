<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/19
 * Time: 15:16
 */

namespace App\Api\V1\Controllers;
use Illuminate\Http\Request;

use App\Api\V1\Repositories\StoreRepository;
use App\Api\V1\Transformers\StoreTransformer;
use App\Api\V1\Transformers\CommentsTransformer;

class StoreController extends BaseController
{
    protected $repository;

    public function __construct(StoreRepository $storeRepository)
    {
        $this->repository = $storeRepository;
    }

    public function regions(Request $request){
        $data = $this->repository->regions();
        return $this->responseSuccess($data);
    }

    /**
     * 鮮貨報馬仔，熱搜人氣，鄰近店家，響導嚴選,蜜蜜推薦，在地小吃
     */
    public function query(Request $request){
        $per_page = $request->get('per_page',10);
        $orderby = $request->get('orderby','created_at');
        $desc = $request->get('desc','DESC');
        $size = $request->get('size','0');
        $lat = $request->get('lat',0);
        $lng = $request->get('lng',0);
        $type = $request->get('type',0);
        $result = $this->repository->query($lat,$lng,$orderby,$desc,$this->size,$per_page,$type);
        $result->appends('orderby',$orderby);
        $result->appends('desc',$desc);
        $result->appends('per_page',$per_page);
        if($lat){
            $result->appends('lat',$lat);
        }
        if($lng){
            $result->appends('lng',$lng);
        }
        if($size){
            $result->appends('size',$size);
        }
        if($type){
            $result->appends('type',$type);
        }
        return $this->response()->paginator($result,new StoreTransformer());
    }

    /**
     * 关键词搜索
     */
    public function search(Request $request){
        $hot_word = $request->get('keyword','');
        $per_page = $request->get('per_page',10);
        $page = $request->get('page',1);
        $member_id = intval($request->get('member_id',0));
        $data = $this->repository->search($hot_word,$per_page,$page,$member_id);
        if($hot_word){
            $data->appends('keyword',$hot_word);
        }
        $data->appends('per_page',$per_page);
        return $this->response()->paginator($data,new StoreTransformer());
    }

    /*
     * 熱搜詞列表
     */
    public function hot_word(Request $request){
        $member_id = $request->get('member_id',0);
        $data = $this->repository->hot_word($member_id);
        return $this->responseSuccess($data);
    }



    public function view(Request $request){
        $id = $request->get('id',0);
        $lat = $request->get('lat',0);
        $lng = $request->get('lng',0);
        $result = $this->repository->storeById($id,$lat,$lng);
        return $this->responseSuccess($result);
    }

    public function is_collect(Request $request){
        $id = $request->get('id',0);
        $user = $this->auth->user();
        $user_id = $user->id;
        if(!$this->getAuthUserStatus($user_id)){
            return $this->noneLoginResponse();
        }
        $result = $this->repository->is_collect($id,$user_id);
        return ['success'=>$result];
    }

    /**
     * 店家评论
     */
    public function store_comments(Request $request){
        $store_id = $request->get('id',0);
        $per_page = $request->get('per_page',10);
        $result = $this->repository->getStoreComment($store_id,$per_page);
        $result->appends('id',$store_id);
        $result->appends('per_page',$per_page);
        return $this->response()->paginator($result,new CommentsTransformer());
    }



}