<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MemberCredits extends Model
{
    //
    protected $table = 'member_credits';
    protected $fillable = ['freeze_credits','promo_credits'];
    public $timestamps = false;
}
