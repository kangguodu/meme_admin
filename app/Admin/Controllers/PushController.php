<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Notice;
use Encore\Admin\Admin;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Controllers\ModelForm;

class PushController extends Controller
{
    use ModelForm;

    public function index()
    {
        Permission::check('push');

        return Admin::content(function ($content){
            $content->header($this->type==1?'店家提現':'網紅提現');
            $content->description(trans('lang.list'));
            $content->body($this->grid());
        });
    }

    private function grid()
    {
        return Notice::grid(function ($grid){
            $grid->model()->orderBy('created_at','DES');
            $grid->id('ID')->sortable();
            $grid->title('標題');
            $grid->description('描述');
            $grid->type_id('推送類型');
            $grid->content('內容');
            $grid->icon("ICON");
            $grid->url("URL");
            $grid->point_id('會員ID');
            $grid->member_id('Member ID');
            $grid->created_at('推送時間')->value(function ($time){
                return date('Y-m-d H:i:s',$time);
            });
        });
    }
}
