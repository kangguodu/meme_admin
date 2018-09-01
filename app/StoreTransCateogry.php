<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StoreTransCateogry extends Model
{
    protected $table = 'store_transcateogry';
    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $guarded = ['id'];
}
