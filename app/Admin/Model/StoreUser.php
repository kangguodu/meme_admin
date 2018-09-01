<?php
/**
 * Created by PhpStorm.
 * User: zzq
 * Date: 18-7-30
 * Time: 上午10:33
 */

namespace App\Admin\Model;


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

    public function setMenusAttribute($value)
    {
//        dd('11');
        $menu = config('adminOption.store_menu');
        $menuArr = array();
        foreach ($value as $key) {
            $arr = $menu[--$key];
            array_push($menuArr, $arr);
        }
        $this->attributes['menus'] = json_encode($menuArr);
    }
    public function getMenusAttribute($value)
    {
        $menus = json_decode($value, true);
        if (is_array($menus)){
            $indexs = array_pluck($menus, 'type');
            return implode(',', $indexs);
        }else{
            return '';
        }
    }

}