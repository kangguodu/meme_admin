<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/30
 * Time: 14:47
 */

namespace App\Api\Merchant\Transformers;

use App\Api\Merchant\Services\ImageToolsService;
use App\Api\Merchant\Services\OrderService;
use League\Fractal\TransformerAbstract;

class CommentListTransformer extends TransformerAbstract
{
    public function transform($object){
        $result = [
            'id'=>$object->id,
            'content'=> empty($object->content)?'':$object->content,
            'level' => $object->level,
            'level_text' => OrderService::getCommentStatusText($object->level),
            'is_reply' => $object->is_reply,
            'reply_content' => $object->reply_content,
            'created_at' => $object->created_at?date('Y-m-d H:i:s',$object->created_at):'',
            'updated_at' => $object->updated_at?$object->updated_at:'',
            'nickname' => $object->nickname,
            'avatar' => ImageToolsService::getUrlWithDefaultPath($object->avatar,'member')
        ];
        return $result;
    }
}