<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class NoticeMember extends Model
{
    protected $table = 'notice_member';
    protected $primaryKey = 'id';
    public $timestamps = false;
}
