<?php
/**
 * Created by PhpStorm.
 * User: zzq
 * Date: 18-7-20
 * Time: 下午4:03
 */

namespace App\Admin\Provider;


use Illuminate\Http\Request;

class AdminHelpers
{
    public static function jsonResponse($data, $status=true)
    {
        return response()->json([
            'status' => $status,
            'message' => $data,
        ]);
    }

    public static function getArgumentFromGetOrSession($argName)
    {
        if ($arg = request($argName)){
            session([
                $argName => $arg
            ]);
            return $arg;
        }elseif (session($argName)){
            return session($argName);
        }else {
            return false;
        }
    }
}