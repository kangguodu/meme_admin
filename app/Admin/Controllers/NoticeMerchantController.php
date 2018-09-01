<?php

namespace App\Admin\Controllers;

use App\Admin\Provider\AdminHelpers;
use App\Admin\Provider\Notification;
use App\Http\Controllers\Controller;
use App\NoticeLog;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Illuminate\Http\Request;

class NoticeMerchantController extends Controller
{
    use ModelForm;
    public function index()
    {
        Permission::check('notice');

        return Admin::content(function ($content){
            $content->header('店鋪推送管理');
            $content->description(trans('lang.list'));
            $content->body($this->grid());
        });
    }

    public function create()
    {
        Permission::check('notice');

        return Admin::content(function ($content){
            $content->header('店鋪推送管理');
            $content->description(trans('lang.create'));
            $content->body($this->textNotice());
        });
    }

    public function destroy($id)
    {
        Permission::check('notice');

        $ids = explode(',', $id);
        if (NoticeLog::whereIn('id',$ids)->delete())
            return AdminHelpers::jsonResponse('刪除數據成功！');
        return AdminHelpers::jsonResponse('刪除數據失敗！', false);
    }

    public function store(Request $request)
    {
        Permission::check('notice');

        $response = $this->textNotice()->store();
        if ($response->getTargetUrl() == admin_url('merchant_notice/create'))
            return $response;

        $notice = NoticeLog::where($request->only(['title', 'description', 'content']))
        ->orderBy("id", "DES")
        ->first();
        (new Notification)
            ->prepare('merchant', $request->only(['title', 'description']), 'system', $notice->toArray())
            ->send();
        return $response;
    }

    private function textNotice()
    {
        return NoticeLog::form(function (Form $form){
            $form->text("title", "主题")->rules("required|max:255");
            $form->text("description",'内容')->rules("nullable|max:255");
            $form->textarea("content", "详细")->rules("required|max:255");
            $form->hidden("platform_type")->default(2);
            $form->hidden("type")->default(1);
        });
    }

    private function grid()
    {
        return NoticeLog::grid(function (Grid $grid){
            $grid->model()->where('platform_type', 2);

            $grid->id("ID");
            $grid->title("主題");
            $grid->description("內容");
            $grid->type("類型")->value(function ($value){
                $arr = [
                    1 => '系統',
                    2 => '活動',
                ];
                return empty($arr[$value])?"":$arr[$value];
            });
            $grid->content("詳細");
            $grid->url("URL");
            $grid->point_id("指向ID");
            $grid->created_at("創建時間")->sortable();

            $grid->disableExport();
            $grid->disableFilter();
        });
    }
}
