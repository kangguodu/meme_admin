<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Traits\AdminBuilder;
use Encore\Admin\Traits\ModelTree;

class Withdrawl extends Model
{
    //
    use ModelTree, AdminBuilder;
    protected $table = 'withdrawl';
    protected $fillable = [
        'uid','type','amount','status','remark','bank_name','receiver_name',
        'bank_account','bank_phone','handle_note','handle_date','created_at'
    ];
    public $timestamps = false;
}
