<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Traits\AdminBuilder;
use Encore\Admin\Traits\ModelTree;

class ServiceAutoReply extends Model
{
    use ModelTree, AdminBuilder;

    protected $table = 'service_auto_reply';
    protected $primaryKey = 'id';
    public $timestamps = false;

}
