<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Traits\AdminBuilder;
use Encore\Admin\Traits\ModelTree;

class StoreTransfer extends Model
{
    use ModelTree, AdminBuilder;

    protected $table = 'store_transfer';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = ['id'];
}
