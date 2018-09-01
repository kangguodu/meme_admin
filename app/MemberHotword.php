<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MemberHotword extends Model
{
    protected $table = 'member_hot_word';
    protected $primaryKey = 'id';
    public $timestamps = false;
}
