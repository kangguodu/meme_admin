<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CouponsRelease extends Model
{
    protected $table = 'coupons_release';
    protected $primaryKey = 'id';
    public $timestamps = false;
}
