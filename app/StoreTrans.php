<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class StoreTrans extends Model
{
    protected $table = 'store_trans';
    protected $primaryKey = 'id';
    protected $fillable = ['store_id','trans_type','trans_category','trans_category_name','trans_description','trans_date','trans_datetime',
        'amount','balance','created_by','created_name'];
    public $timestamps = false;
    protected $guarded = ['id'];
}
