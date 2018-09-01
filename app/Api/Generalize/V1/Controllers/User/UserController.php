<?php
/**
 * Created by PhpStorm.
 * User: zzq
 * Date: 18-6-22
 * Time: 下午5:56
 */

namespace App\Api\Generalize\V1\Controllers\User;


use App\Api\Generalize\V1\Controllers\BaseController;
use App\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends BaseController
{
    public function get($handle,Request $request)
    {
        switch ($handle)
        {
            case 'info':
                return $this->userInfo();
            case 'bankaccount':
                return $this->bankAccount();
            default:
                return $this->responseError('404 Not Found',404001,404);
        }
    }
    public function post($handle,Request $request)
    {
        switch ($handle)
        {
            case 'alterinfo':
                return $this->alterUserInfo($request);

            default:
                return $this->responseError('404 Not Found',404001,404);
        }
    }
    private function userInfo()
    {
        $user = $this->getAuthUser();
        return $this->success($user);
    }
    private function alterUserInfo(Request $request)
    {
        empty($request->nickname)?:$data['nickname']=$request->nickname;
        if (!empty($gender = $request->gender))
            $gender==1||$gender==2?$data['gender'] = $gender:null;
        empty($request->avatar)?:$data['avatar'] = $request->avatar;
        empty($request->birthday)?:$data['birthday'] = $request->birthday;
        if (empty($data))
            return $this->responseError('send no data');
        $id = $this->getAuthUserId();
        Member::find($id)->update($data);
        return $this->success('alter success');
    }
}