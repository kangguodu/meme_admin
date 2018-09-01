<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RebateOrder extends Model
{
    protected $table = 'rebate_orders';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = ['id'];
}
