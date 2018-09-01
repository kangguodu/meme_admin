<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Traits\AdminBuilder;
use Encore\Admin\Traits\ModelTree;

class StoreType extends Model
{
    use ModelTree, AdminBuilder;

    protected $table = 'store_type';
    public $timestamps = false;
}
