<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Encore\Admin\Traits\AdminBuilder;
use Encore\Admin\Traits\ModelTree;

class StoreUser  extends Authenticatable
{
    use ModelTree, AdminBuilder, Notifiable;


    public $timestamps = false;
    protected $table = 'store_user';
    protected $primaryKey = 'id';
    protected $guarded = ['id'];
    protected $hidden = [
      'password'
    ];

    /**
     * This mutator automatically hashes the password.
     *
     * @var string
     */
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = \Hash::make($value);
    }

}
