<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CouponsReceiveLog extends Model
{
    protected $table = 'coupons_receive_log';
    protected $primaryKey = 'id';
    public $timestamps = false;
}
