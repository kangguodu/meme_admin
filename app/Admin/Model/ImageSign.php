<?php

namespace App\Admin\Model;

use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Traits\AdminBuilder;
use Encore\Admin\Traits\ModelTree;

class ImageSign extends Model
{
    use ModelTree, AdminBuilder;

    protected $table = 'image_sign';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function setImageConfigAttribute($value)
    {
        $this->attributes['image_config'] = json_encode([
            "background" => $value,
            "qr_code_size" => 202,
            "qr_code_position_x" => 20,
            "qr_code_position_y" => 329,
            "store_code_font_size" => 12,
            "store_code_position_x" => 135,
            "store_code_position_y" => 559,
            "store_name_font_size" => 12,
            "store_name_position_x" => 240,
            "store_name_position_y" => 515,
            "store_code_font" => "/upload/download/wryh.ttf",
            "logo_path" => "/upload/download/logo.png",
            "logo_size" => 70
        ]);
    }
    public function getImageConfigAttribute($value)
    {
        $data = json_decode($value,true);
        return $data['background'];
    }
}
