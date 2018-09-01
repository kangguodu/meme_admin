<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class BankAccount extends Model
{
    //
    protected $table = 'bank_account';
    protected $fillable = ['member_id','receiver_name','bank_name','bank_account',
        'bank_phone','bank_account_name','created_at'];
    public $timestamps = false;
}
