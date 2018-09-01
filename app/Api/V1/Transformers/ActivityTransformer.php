<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/20
 * Time: 12:08
 */

namespace App\Api\V1\Transformers;

use App\Activity;
use League\Fractal\TransformerAbstract;
use App\Api\V1\Services\BaseService;

class ActivityTransformer extends TransformerAbstract
{
    public function transform(Activity $object){
        return [
            'id'  => $object->id,
            'title' => $object->title,
            'type' => $object->type,
            'content' => $object->content,
            'description' => $object->description,
            'posters_pictures'=>$object->posters_pictures ? BaseService::image($object->posters_pictures) : '',
            'created_at' =>  $object->created_at ? $object->created_at : '',
            'start_at' =>  $object->start_at ? date('Y-m-d H:i',strtotime($object->start_at)) : '',
            'expire_at' => $object->expire_at ? date('Y-m-d H:i',strtotime($object->expire_at)): '',
            'url' => $object->url,
            'status' => $object->status
        ];
    }


}