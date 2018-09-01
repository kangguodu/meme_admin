<?php


namespace App\Api\Merchant\Transformers;

use App\Api\Merchant\Services\ImageToolsService;
use League\Fractal\TransformerAbstract;

class BannerListTransformer  extends TransformerAbstract
{
    public function transform($object){

        $result = [
            'id'=>$object->id,
            'image'=> ImageToolsService::getUrlWithDefaultPath($object->image,'banner'),
        ];
        return $result;
    }
}