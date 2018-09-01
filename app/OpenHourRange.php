<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class OpenHourRange extends Model
{
    protected $table = 'open_hour_range';
    protected $primaryKey = 'id';
    public $timestamps = false;
}
