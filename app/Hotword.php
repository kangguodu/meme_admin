<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Traits\AdminBuilder;
use Encore\Admin\Traits\ModelTree;

class Hotword extends Model
{
    use ModelTree, AdminBuilder;

    protected $table = 'hot_word';
    protected $primaryKey = 'id';
    protected $fillable = ['hot_word','number'];
    public $timestamps = false;
}
