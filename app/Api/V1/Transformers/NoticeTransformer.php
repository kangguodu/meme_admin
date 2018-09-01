<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/14
 * Time: 10:43
 */

namespace App\Api\V1\Transformers;

use App\Notice;
use League\Fractal\TransformerAbstract;
use App\Api\V1\Services\BaseService;

class NoticeTransformer extends TransformerAbstract
{
    public function transform(Notice $object){
        return [
            'id'  => $object->id,
            'title' => $object->title,
            'description' => $object->description,
            'content' => $object->content,
            'type_id' => $object->type_id,
            'type' => $object->type,
            'point_id'=>$object->point_id,
            'icon'=>BaseService::image($object->icon),
            'url' => $object->url,
            'created_at' =>  ($object->created_at && $object->created_at != '0000-00-00 00:00:00') ? $object->created_at : '',
            'status' => $object->status ? $object->status : 2,
        ];
    }


}