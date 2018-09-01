<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Options extends Model
{
    protected $table = 'options';
    protected $primaryKey = 'id';
    protected $fillable = ['option_value'];
    public $timestamps = false;
}
