<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Traits\AdminBuilder;
use Encore\Admin\Traits\ModelTree;

class Notice extends Model
{
    use AdminBuilder,ModelTree;
    protected $table = 'notice';
    protected $primaryKey = 'id';
//    public $timestamps = false;
}
