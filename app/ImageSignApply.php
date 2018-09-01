<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Traits\AdminBuilder;
use Encore\Admin\Traits\ModelTree;

class ImageSignApply extends Model
{
    use ModelTree, AdminBuilder;

    protected $table = 'image_sign_apply';
    protected $primaryKey = 'id';
    public $timestamps = false;

//    public function ImageSign()
//    {
//        return $this->hasOne(\App\Admin\Model\ImageSign::class, "id", "")
//    }
}
