<?php
namespace App\Api\Merchant\Services;

/**
 * 财务记录服务类
 * Class TransService
 * @package App\Api\Merchant\Services
 */
class TransService
{
    public static function getTransTypes(){
        return [
            1 => '收入',
            2 => '支出'
        ];
    }

    public static function getTransTypesText($type){
        $types = self::getTransTypes();
        return isset($types[$type])?$types[$type]:'';
    }
}