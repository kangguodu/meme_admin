<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Invitelog extends Model
{
    protected $table = 'invitelog';
    protected $primaryKey = 'id';
    public $timestamps = false;
}
