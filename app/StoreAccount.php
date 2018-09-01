<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Encore\Admin\Traits\AdminBuilder;
use Encore\Admin\Traits\ModelTree;

class StoreAccount extends Model
{
//	use ModelTree, AdminBuilder;

	protected $table = 'store_account';
    protected $primaryKey = 'id';
    protected $fillable = ['credits_income','store_id'];
    public $timestamps = false;
    protected $guarded = ['id'];


//	$this->hasOne(Store::class);

}
