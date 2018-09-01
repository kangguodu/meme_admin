<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StoreBanner extends Model
{
    protected $table = 'store_banner';
    protected $primaryKey = 'id';
    public $timestamps = false;
}
