<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Traits\AdminBuilder;
use Encore\Admin\Traits\ModelTree;

class Banner extends Model
{
    use ModelTree, AdminBuilder;

    protected $table = 'banner';
    protected $primaryKey = 'id';
    public $timestamps = false;

    public function setImageUrlAttribute($image_url){
        \Log::debug("image url {$image_url}");
        $this->image_url = $image_url;
    }
}
