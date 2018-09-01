<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Traits\AdminBuilder;
use Encore\Admin\Traits\ModelTree;

class ServiceKeyword extends Model
{
    use ModelTree, AdminBuilder;

    protected $table = 'service_keyword';
    protected $primaryKey = 'id';
    public $timestamps = false;
}
