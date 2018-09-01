<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/14
 * Time: 10:43
 */

namespace App\Api\V1\Repositories;

use App\Notice;

class NoticeRepository
{
    protected $model;
    public function __construct(Notice $notice)
    {
        $this->model = $notice;
    }

    public function query($per_page,$member_id,$type){
        $model = $this->model->leftJoin('notice_type','type_id','=','notice_type.id')
                        ->select('notice.*','type');
        if($type){
            $model = $model->where('type_id',$type);
        }
        if($member_id){
            $model = $model->where('member_id',$member_id);
        }
       $data = $model->orderBy('created_at','DESC')
                        ->paginate($per_page);

        if(count($data) && $member_id){
            foreach ($data as $v){
                $result =(new \App\NoticeMember())->where(['notice_id'=>$v->id,'member_id'=>$member_id])->first();
                if($result){
                    $v->status = 1;
                }
            }
        }
        return $data;

    }


    public function view($id,$member_id){
        $data = $this->model->where('id',$id)
                    ->first();

        if($data && $member_id){
            $result = (new \App\NoticeMember())->where(['notice_id'=>$id,'member_id'=>$member_id])->first();
            if(!$result){
                (new \App\NoticeMember())->insert(['notice_id'=>$id,'member_id'=>$member_id,'status'=>1]);
            }

        }
        return $data;
    }

    public function unread_notice($member_id){

        $data = $this->model->selectRaw('count(*) as count,type_id')->groupBy('type_id')->orderBy('type_id')->get();

       $read = (new \App\NoticeMember())->join('notice','notice.id','=','notice_id')
                    ->selectRaw('count(*) as count,type_id')
                    ->where(['notice_member.member_id'=>$member_id,'status'=>1])
                    ->get(); //已讀

        if(!empty($data) && !empty($read)){
            foreach ($data as $v){
                foreach ($read as $value){
                    if($v->type_id == $value->type_id){
                        $v->count = $v->count - $value->count;
                    }
                }
            }
        }
       

       return $data;
    }

}