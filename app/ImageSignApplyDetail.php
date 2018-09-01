<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Traits\AdminBuilder;
use Encore\Admin\Traits\ModelTree;

class ImageSignApplyDetail extends Model
{
    use ModelTree, AdminBuilder;

    protected $table = 'image_sign_apply_detail';
    protected $primaryKey = 'id';


    public function imageSign()
    {
        return $this->hasOne(\App\Admin\Model\ImageSign::class, 'id', 'image_sign_id');
    }
    public function apply()
    {
        return $this->hasOne(ImageSignApply::class, 'id', 'apply_id');

    }
}
