<?php

namespace App\Admin\Controllers;

use App\Activity;
use App\Http\Controllers\Controller;
use App\Notice;
use Carbon\Carbon;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class NoticeController extends Controller
{
    use ModelForm;
    public function index()
    {
        Permission::check('notice');

        return Admin::content(function ($content){
            $content->header('推送管理');
            $content->description(trans('lang.list'));
            $content->body($this->grid());
        });
    }

    public function show($handle, Request $request)
    {
        $data = [
            ['first_name' => 'John', 'last_name' => 'Doe', 'age' => 'twenties'],
            ['first_name' => 'Fred', 'last_name' => 'Ali', 'age' => 'thirties'],
            ['first_name' => 'Alex', 'last_name' => 'Cho', 'age' => 'thirties'],
            ['first_name' => 'Fred', 'last_name' => '朱朱', 'age' => 'thirties'],
            ['first_name' => 'Alex', 'last_name' => '彭彭', 'age' => 'thirties'],
        ];
        return collect($data)->where('age', 'thirties')
            ->sortByDesc('last_name')
            ->map(function ($item){
                return $item['last_name']."=>".$item['age'];
            })
            ->implode("<br>");


        Permission::check('notice');
        switch ($handle){
            case 'activity':
                return $this->activityNotice($request);
        }
    }

    private function activityNotice(Request $request)
    {
        $activity = Activity::find($request->activity_id);
        return Notice::form(function ($form){
            $form->text('测试');
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
            $grid->icon("ICON")->value(function ($icon){
                $url = url((string)$icon);
                return empty($icon)?'':"<img src='{$url}' style='height: 40px;width: auto'>";
            });
            $grid->url("URL");
            $grid->point_id('會員ID');
            $grid->member_id('Member ID');
            $grid->created_at('推送時間')->value(function ($time){
                return date('Y-m-d H:i:s',$time);
            });
        });
    }
}
