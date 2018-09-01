<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Encore\Admin\Traits\AdminBuilder;
use Encore\Admin\Traits\ModelTree;

class Member extends Authenticatable
{
    use Notifiable,ModelTree, AdminBuilder;
    //
    protected $table = 'member';

    protected $fillable = ['phone','nickname','gender','avatar','birthday','secure_password','status','email','user_type'];
    protected $hidden = ['pay_password',];

    public function account()
    {
        return $this->hasOne(MemberCredits::class,'member_id','id');
    }

    public function getAvatarAttribute($value)
    {
        return empty($value)?url('images/avatar/avatar.png'):url($value);
    }

    public static function newUserNum()
    {
        return self::where("created_at", ">", Carbon::today()->toDateTimeString())->count();
    }
}
