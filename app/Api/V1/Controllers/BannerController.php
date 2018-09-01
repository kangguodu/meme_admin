<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/19
 * Time: 14:59
 */

namespace App\Api\V1\Controllers;

use Illuminate\Http\Request;
use App\Api\V1\Repositories\BannerRepository;

class BannerController extends BaseController
{
    protected $repository;
    public function __construct(BannerRepository $bannerRepository)
    {
        $this->repository = $bannerRepository;
    }

    public function index(Request $request){
        $lat = $request->get('lat',0);
        $lng = $request->get('lng',0);
        $data = $this->repository->index();
        $data['store'] = $this->repository->hot($lat,$lng,$this->size);//事实$size=5
        return $this->responseSuccess($data);
    }


}