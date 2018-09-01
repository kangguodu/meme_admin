<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Credits extends Model
{
    protected $table = 'member_credits';
    protected $primaryKey = 'id';
    public $timestamps = false;
}
