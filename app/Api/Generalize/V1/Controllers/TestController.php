<?php
/**
 * Created by PhpStorm.
 * User: zzq
 * Date: 18-6-21
 * Time: 下午4:38
 */

namespace App\Api\Generalize\V1\Controllers;

use Illuminate\Http\Request;

class TestController
{
    public function test(Request $request)
    {
        return config('auth.providers');
    }
}