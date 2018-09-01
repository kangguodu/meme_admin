<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Traits\AdminBuilder;
use Encore\Admin\Traits\ModelTree;

class Goods extends Model
{
    use ModelTree, AdminBuilder;

    protected $table = 'goods';
    protected $primaryKey = 'id';
    public $timestamps = false;
}
