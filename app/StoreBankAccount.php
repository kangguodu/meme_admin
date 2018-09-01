<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Traits\AdminBuilder;
use Encore\Admin\Traits\ModelTree;

class StoreBankAccount extends Model
{
    use AdminBuilder,ModelTree;

    protected $table = 'store_bank_account';
    protected $primaryKey = 'id';
    public $timestamps = false;
}
