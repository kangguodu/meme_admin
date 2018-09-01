<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/19
 * Time: 18:07
 */

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Api\V1\Repositories\CollectionRepository;
use App\Api\V1\Transformers\CollectionTransformer;


class CollectionController extends BaseController
{
    public function __construct(CollectionRepository $collectionRepository){
        $this->repository = $collectionRepository;

    }

    public function add(Request $request){
        $user = $this->auth->user();
        $user_id =$user->id;
        if(!$this->getAuthUserStatus($user_id)){
            return $this->noneLoginResponse();
        }
        $credentials = $request->only('store_id','goods_id');
        $store_id = json_decode($credentials['store_id']);
        if(!$store_id || !is_array($store_id)){
            return $this->responseError('摻入數據格式錯誤');
        }
        $result = $this->repository->store($user_id,$store_id);
        return $this->responseSuccess($result);
    }

    public function index(Request $request){
        $user = $this->auth->user();
        $user_id = $user->id;
        if(!$this->getAuthUserStatus($user_id)){
            return $this->noneLoginResponse();
        }
        $per_page = $request->get('per_page',10);
        $result = $this->repository->index($user_id,$per_page);
        $result->appends('per_page',$per_page);
        return $this->response()->paginator($result,new CollectionTransformer());

    }

}