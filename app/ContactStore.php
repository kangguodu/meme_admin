<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ContactStore extends Model
{
    protected $table = 'contact_store';
    protected $primaryKey = 'id';
    public $timestamps = false;
}
