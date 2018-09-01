<?php
/**
 * Created by PhpStorm.
 * User: zzq
 * Date: 18-6-23
 * Time: 上午11:36
 */

namespace App\Api\Generalize\V1\Functions;


class Functions
{
    static function checkPhone($phone)
    {

        $length = strlen($phone);
        if (is_numeric($phone)){
            return true;
        }else{
            return false;
        }
    }
    static function sendSms($phone)
    {

    }
}