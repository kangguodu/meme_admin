<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/14
 * Time: 11:29
 */

namespace App\Api\V1\Transformers;

use App\Comment;
use League\Fractal\TransformerAbstract;
use App\Api\V1\Services\BaseService;

class CommentsTransformer extends TransformerAbstract
{
    public function transform(Comment $object){
        $object->avatar = empty($object->avatar)?url('/images/avatar/').'/avatar.png': BaseService::image($object->avatar);
        return [
            'id' => $object->id,
            'store_id' => $object->store_id,
             'content' => $object->content,
            'level' => $object->level,
            'is_reply' => $object->is_reply,
            'reply_content' => $object->reply_content,
            'nickname' => $object->nickname,
            'image' => $object->image ? json_decode($object->image) : [],
            'avatar' => $object->avatar,
            'created_at' => date('Y-m-d H:i:s',$object->created_at),
            'time' => $this->transTime($object->created_at)
        ];
    }
    private function transTime($created_at){
        $yesterday = strtotime(date("Y-m-d",strtotime("-1 day")));
        $two = strtotime(date("Y-m-d",strtotime("-2 day")));
        $time = '3天前';
        if (date('Y-m-d') == date('Y-m-d',$created_at)) {
            $time = '今天';
        }
        if($created_at>=$yesterday && $created_at<$yesterday+24*3600-1){
            $time = '1天前';
        }
        if($created_at>=$two && $created_at<$two+24*3600-1){
            $time = '2天前';
        }
       return $time;

    }

}