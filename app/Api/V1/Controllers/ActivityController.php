<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/20
 * Time: 11:58
 */

namespace App\Api\V1\Controllers;

use App\Api\V1\Repositories\ActivityRepository;
use App\Api\V1\Transformers\ActivityTransformer;
use Illuminate\Http\Request;


class ActivityController extends BaseController
{
    protected $repository;
    public function __construct(ActivityRepository $activityRepository)
    {
        $this->repository = $activityRepository;
    }

    public function index(Request $request){
        $per_page = $request->get('per_page',10);
//        $type = $request->get('type',2);
        $result = $this->repository->query($per_page);
        $result->appends('per_page',$per_page);
        return $this->response()->paginator($result,new ActivityTransformer());
    }
    

}