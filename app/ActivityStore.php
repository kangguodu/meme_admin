<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ActivityStore extends Model
{
    protected $table = 'activity_store';
    protected $primaryKey = 'id';
    protected $fillable = ['activity_id', 'store_id', 'status'];
    public $timestamps = false;
}
