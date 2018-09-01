<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class MemberCreditsLog extends Model
{

    protected $table = 'member_credits_log';
    protected $primaryKey = 'id';
    public $timestamps = false;
}
