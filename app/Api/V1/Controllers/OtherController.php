<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/3
 * Time: 14:31
 */

namespace App\Api\V1\Controllers;

use Faker\Provider\Base;
use Illuminate\Http\Request;
use Dotenv\Exception\ValidationException;
use Prettus\Validator\Exceptions\ValidatorException;
use App\Api\V1\Repositories\OtherRepository;
use App\Api\V1\Validators\OtherValidator;
use Illuminate\Support\Facades\Cache;

class OtherController extends BaseController
{
    protected $repository;
    protected $validator;
    public function __construct(OtherRepository $otherRepository,OtherValidator $otherValidator)
    {
        $this->repository = $otherRepository;
        $this->validator = $otherValidator;
    }

    /**
     * 商家入驻
     */
    public function storecreate(Request $request){
        $credentials = $request->all();
        try{
            $this->validator->with($credentials)->passesOrFail('storecreate');
            return $this->repository->create($credentials);

        }catch (ValidatorException $e){
            return $this->responseError($e->getMessageBag()->first(),$this->status_validator_error,422);
        }
    }

    /**
     * 店铺反馈
     */
    public function feedback(Request $request){
        $credentials = $request->all();
        try{
            $this->validator->with($credentials)->passesOrFail('feedback');

            return $this->repository->feedback($credentials);

        }catch (ValidationException $e){
            return $this->responseError($e->getMessageBag()->first(),$this->status_validator_error,422);
        }

    }

    /**
     * 我要合作
     */
    public function cooperation(Request $request){
        $credentials = $request->all();
        try{
            $this->validator->with($credentials)->passesOrFail('cooperation');

            return $this->repository->cooperation($credentials);

        }catch (ValidationException $e){
            return $this->responseError($e->getMessageBag()->first(),$this->status_validator_error,422);
        }

    }

    /**
     * 媒體聯繫
     */

    public function mediacontact(Request $request){
        $credentials = $request->all();
        try{
            $this->validator->with($credentials)->passesOrFail('media');

            return $this->repository->media($credentials);

        }catch (ValidationException $e){
            return $this->responseError($e->getMessageBag()->first(),$this->status_validator_error,422);
        }

    }

    public function getRegion(Request $request){
        return $this->repository->region();
    }

    public function regNum(Request $request){
        $value = Cache::rememberForever('number', function()
        {
            return \DB::table('member')->count();
        });
        return $this->responseSuccess($value);
    }

    public function inviteNum(Request $request){
        $user = $this->auth->user();
        if(!$this->getAuthUserStatus($user->id)){
            return $this->noneLoginResponse();
        }
        return $this->responseSuccess($user->invite_count);
    }
}