<?php
/**
 * Created by PhpStorm.
 * User: zzq
 * Date: 18-7-9
 * Time: 上午10:29
 */

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Http\Response;
use Illuminate\Http\Request;

class AuthimgController extends Controller
{
    use ModelForm;
    public function index(Response $response,Request $request)
    {
        //$num=4;$size=25; $width=0;$height=0;
        $num=4;$size=20; $width=90;$height=35;
        !$width && $width = $num*$size*4/5+5;
        !$height && $height = $size + 10;
        // 去掉了 0 1 O l 等
        $str = "23456789abcdefghijkmnpqrstuvwxyzABCDEFGHIJKLMNPQRSTUVW";
        $code = '';
        for ($i=0; $i<$num; $i++){
            $code.= $str[mt_rand(0, strlen($str)-1)];
        }
        // 画图像
        $im = imagecreatetruecolor($width,$height);
        // 定义要用到的颜色
        $back_color = imagecolorallocate($im, 235, 236, 237);
        $boer_color = imagecolorallocate($im, 118, 151, 199);
        $text_color = imagecolorallocate($im, mt_rand(0,200), mt_rand(0,120), mt_rand(0,120));

        // 画背景
        imagefilledrectangle($im,0,0,$width,$height,$back_color);
        // 画边框
        imagerectangle($im,0,0,$width-1,$height-1,$boer_color);
        // 画干扰线
        for($i=0;$i<10;$i++){
            $font_color = imagecolorallocate($im, mt_rand(0,255), mt_rand(0,255), mt_rand(0,255));
            imagearc($im,mt_rand(-$width,$width),mt_rand(-$height,$height),mt_rand(30,$width*2),mt_rand(20,$height*2),mt_rand(0,360),mt_rand(0,360),$font_color);
        }
        // 画干扰点
        for($i=0;$i<100;$i++){
            $font_color = imagecolorallocate($im, mt_rand(0,255), mt_rand(0,255), mt_rand(0,255));
            imagesetpixel($im,mt_rand(0,$width),mt_rand(0,$height),$font_color);
        }
        // 画验证码
        for ($i=0;$i<$num;$i++){
            @imagefttext($im, $size , rand(-45, 45), $size*$i+5, $size+5, $text_color, public_path('css/simsunb.ttf'),$code[$i]);
        }
        //@imagefttext($im, $size , 0, 10, $size+3, $text_color, public_path('css/simsunb.ttf'),$code);
        // header("Cache-Control: max-age=1, s-maxage=1, no-cache, must-revalidate");
        // header("Content-type: image/png");
        session(['authcode'=>$code]);
        $response
            ->header('Content-Type', 'image/png');
        imagepng($im);
        imagedestroy($im);
        return $response;
    }
}
