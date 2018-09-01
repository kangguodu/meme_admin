<?php
namespace App\Api\Merchant\Transformers;

use App\Api\Merchant\Repositories\StoreRepository;
use App\Api\Merchant\Services\ImageToolsService;
use League\Fractal\TransformerAbstract;

class DownloadListTransformer extends TransformerAbstract
{

    public function transform($object){
        $result = [
            'id'=>$object->id,
            'title'=> $object->title,
            'description'=> $object->description,
            'cover' => ImageToolsService::getUrlWithDefaultPath($object->cover,'download'),
            'image' => $object->image,
        ];

        return $result;
    }
}