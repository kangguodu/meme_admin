<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Traits\AdminBuilder;
use Encore\Admin\Traits\ModelTree;

class Activity extends Model
{
    use ModelTree, AdminBuilder;

    protected $table = 'activity';
    protected $primaryKey = 'id';
    protected $fillable = ['title','content','description','type','created_at','created_by','expire_at','checked','platform_type','posters_pictures','discount','url'];
    public $timestamps = false;
}
