<?php
$json = '{"background":"\/upload\/download\/qrcodev2.png","qr_code_size":503,"qr_code_position_x":92,"qr_code_position_y":943,"qr_code_rotate":0,"store_code_font_size":50,"store_code_position_x":0,"store_code_position_y":0,"store_name_font_size":12,"store_name_position_x":0,"store_name_position_y":0,"store_code_font":"\/upload\/download\/wryh.ttf","logo_path":"\/upload\/download\/logo.png","logo_size":200}';

echo "<pre>";
var_export(json_decode($json,TRUE));
echo "</pre>";

exit();
$config = array (
    'background' => '/upload/download/lipaiv2.png',
    'qr_code_size' => 560,
    'qr_code_position_x' => 284,
    'qr_code_position_y' => 1502,
    'qr_code_rotate' => 5,
    'store_code_font_size' => 50,
    'store_code_position_x' => 1260,
    'store_code_position_y' => 1592,
    'store_name_font_size' => 12,
    'store_name_position_x' => 0,
    'store_name_position_y' => 0,
    'store_code_font' => '/upload/download/wryh.ttf',
    'logo_path' => '/upload/download/logo.png',
    'logo_size' => 200,
);

echo json_encode($config);
exit();
function hex_to_rgb($color) {
    if($color[0] == '#')
        $color = substr($color, 1);

    if(strlen($color) == 6) {
        list($r, $g, $b) = array(
            $color[0].$color[1],
            $color[2].$color[3],
            $color[4].$color[5]
        );
    } elseif (strlen($color) == 3) {
        list($r, $g, $b) = array(
            $color[0].$color[0],
            $color[1].$color[1],
            $color[2].$color[2]
        );
    } else {
        return array('red' => 255, 'green' => 255, 'blue' => 255);
    }

    $r = hexdec($r); $g = hexdec($g); $b = hexdec($b);
    return array('red' => $r, 'green' => $g, 'blue' => $b);
}


// this file writes the image into the http response,
// so we cant have any output other than headers and the file data
ob_start();

$filename       = 'qrcode.png'; //my qrcode background color is white
$degrees        = 7;

// open the image file
$im = imagecreatefrompng( $filename );

// create a transparent "color" for the areas which will be new after rotation
// r=255,b=255,g=255 ( white ), 127 = 100% transparency - we choose "invisible black"
$transparency = imagecolorallocatealpha( $im,255,255,255,0 );

// rotate, last parameter preserves alpha when true
$rotated = imagerotate( $im, $degrees, $transparency, 1);

//maybe there have make white color is transparent
$background = imagecolorallocate($rotated , 255,  255,  255);
imagecolortransparent($rotated,$background);

// disable blendmode, we want real transparency
imagealphablending( $rotated, false );
// set the flag to save full alpha channel information
imagesavealpha( $rotated, true );

// now we want to start our output
ob_end_clean();
// we send image/png
header( 'Content-Type: image/png' );

imagepng( $rotated );
// clean up the garbage
imagedestroy( $im );
imagedestroy( $rotated );

exit();
$json2 = '{
	"background": "/upload/download/lipaiv2.png",
	"qr_code_size": 500,
	"qr_code_position_x": 500,
	"qr_code_position_y": 700,
	"store_code_font_size": 12,
	"store_code_position_x": 135,
	"store_code_position_y": 559,
	"store_name_font_size": 12,
	"store_name_position_x": 240,
	"store_name_position_y": 515,
	"store_code_font": "/upload/download/wryh.ttf",
	"logo_path": "/upload/download/logo.png",
	"logo_size": 70
}';
echo "<pre>";
var_export(json_decode($json2,TRUE));
echo "</pre>";
exit();
$default = array(
	array(
		'title' => '結帳管理',
		'img' => 'assets/imgs/menu-1.png',
		'type' => 1,
		'checked' => true
	),
	array(
		'title' => '資料設定',
		'img' => 'assets/imgs/menu-2.png',
		'type' => 2,
		'checked' => true
	),
	array(
		'title' => '我的帳戶',
		'img' => 'assets/imgs/menu-3.png',
		'type' => 3,
		'checked' => true
	),
	array(
		'title' => '下載專區',
		'img' => 'assets/imgs/menu-4.png',
		'type' => 4,
		'checked' => true
	),
	array(
		'title' => '聯繫客服',
		'img' => 'assets/imgs/menu-5.png',
		'type' => 5,
		'checked' => true
	),
	array(
		'title' => '帳號管理',
		'img' => 'assets/imgs/menu-6.png',
		'type' => 6,
		'checked' => true
	),
);



echo(json_encode($default));