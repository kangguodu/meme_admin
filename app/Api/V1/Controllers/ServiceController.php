<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/4
 * Time: 10:40
 */

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;
use App\Api\V1\Repositories\ServiceRepository;


class ServiceController extends BaseController
{
    protected $repository;
    public function __construct(ServiceRepository $serviceRepository)
    {
        $this->repository = $serviceRepository;
    }

    public function getBotQA(Request $request){
        $root_id = $request->get('root_id',3);
        $data['keyword'] = $this->repository->keyword();
        $data['autoreply'] = $this->repository->autoreply($root_id);
        return $this->responseSuccess($data);
    }



}