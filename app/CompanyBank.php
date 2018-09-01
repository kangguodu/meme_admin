<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CompanyBank extends Model
{
    protected $table = 'company_bank';
    protected $primaryKey = 'id';
    public $timestamps = false;
}
