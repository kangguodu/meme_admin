<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/14
 * Time: 10:41
 */

namespace App\Api\V1\Controllers;
use Illuminate\Http\Request;
use App\Api\V1\Repositories\NoticeRepository;
use App\Api\V1\Transformers\NoticeTransformer;


class NoticeController extends BaseController
{
    protected $repository;
    public function __construct(NoticeRepository $noticeRepository)
    {
        $this->repository = $noticeRepository;
    }

    public function index(Request $request){
        $per_page = $request->get('per_page',10);
        $member_id = $request->get('member_id',0);
        $type = intval($request->get('type',0));
        $result = $this->repository->query($per_page,$member_id,$type);
        $result->appends('per_page',$per_page);
        return $this->response()->paginator($result,new NoticeTransformer());
    }

    public function view(Request $request){
        $id = $request->get('id',0);
        $member_id = $request->get('member_id',0);
        $result = $this->repository->view($id,$member_id);
        return $this->response()->item($result,new NoticeTransformer());
    }

    public function notice_total(Request $request){
        $user = $this->auth->user();
        $id = $user->id;
        if(!$this->getAuthUserStatus($id)){
            return $this->noneLoginResponse();
        }
        $data = $this->repository->unread_notice($id);
        return $this->responseSuccess($data);
    }

}