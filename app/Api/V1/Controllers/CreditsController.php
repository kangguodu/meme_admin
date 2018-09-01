<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/25
 * Time: 10:44
 */

namespace App\Api\V1\Controllers;
use Illuminate\Http\Request;

use App\Api\V1\Repositories\CreditsRepository;
use App\Api\V1\Transformers\CreditsTransformer;

class CreditsController extends BaseController
{
    protected $repository;

    public function __construct(CreditsRepository $creditsRepository)
    {
        $this->repository = $creditsRepository;
    }

    public function index(Request $request){
        $user = $this->auth->user();
        $member_id = $user->id;
        if(!$this->getAuthUserStatus($member_id)){
            return $this->noneLoginResponse();
        }
        $per_page = $request->get('per_page',10);
        $type = $request->get('type',0);
        $data = $this->repository->Credit($member_id,$per_page,$type);
        $data->appends('per_page',$per_page);
        if($type){
            $data->appends('type',$type);
        }
        return $this->response()->paginator($data,new CreditsTransformer());
    }

    public function dataCount(Request $request){
        $user = $this->auth->user();
        $member_id = $user->id;
        if(!$this->getAuthUserStatus($member_id)){
            return $this->noneLoginResponse();
        }
        $data = $this->repository->calculate($member_id);
        return $this->responseSuccess($data);
    }


}