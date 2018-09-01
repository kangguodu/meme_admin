<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PromoCode extends Model
{
    //
    protected $table = 'promo_codes';
    protected $fillable = ['code','used'];

    public $timestamps = false;
}
