<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StoreData extends Model
{
    protected $table = 'store_data';
    protected $primaryKey = 'id';
    public $timestamps = false;
}
