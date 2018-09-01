<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;

class NoticeGeneralizeController extends Controller
{
    use ModelForm;

    public function index()
    {
        Permission::check('notice');

        return Admin::content(function ($content){
            $content->header('網紅推送管理');
            $content->description(trans('lang.list'));
            $content->body($this->grid());
        });
    }

    public function create(Request $request)
    {
        Permission::check('notice');

        return Admin::content(function ($content) use ($request){
            $content->header('網紅推送管理');
            $content->description(trans('lang.create'));
            $content->body($this->noticeType($request));
        });
    }

    private function noticeType(Request $request)
    {
        $handle = $request->get('type', 'select');
        switch ($handle){
            case 'select':
                return $this->selectType();
            case 'activity':
                return $this->activityNotice($request);
//            case 'url':
//                return $this->urlNotice();
            case 'text':
                return $this->textNotice();
        }
    }
    private function selectType()
    {
        $box = new Box();

        $activityUrl = admin_url('generalize_notice/create?type=activity');
//        $htmlUrl = admin_url('generalize_notice/create?type=url');
        $textUrl = admin_url('generalize_notice/create?type=text');
        $btn = "<div class='col-md-6'style='margin-top: 200px;margin-bottom: 200px'><a href='{$activityUrl}' class='pull-right btn btn-success btn-lg'>活動推送</a></div>";
        $btn .= "<div class='col-md-4'style='margin-top: 200px;margin-bottom: 200px'><a href='{$textUrl}' class='btn btn-success btn-lg text-center'>系統通知</a></div>";
//        $btn .= "<div class='col-md-4'style='margin-top: 200px;margin-bottom: 200px'><a href='{$htmlUrl}' class='btn btn-success btn-lg text-center'>H5推送</a></div>";
//        $btn .= "<div class='col-md-6'style='margin-top: 200px;margin-bottom: 200px'><a href='{$textUrl}' class='pull-right btn btn-success btn-lg'>系統通知</a></div>";

        $box->title('推送類型');
        $box->content($btn);
        return $box;
    }
    private function activityNotice()
    {
//        $activity = Activity::find($request->activity_id);
        return Notice::form(function ($form){
            $form->text('title','標題')->rules('required|max:255');
            $form->text('description')->rules('nullable|max:255');
            $form->select('point_id','活動')
                ->options(
                    Activity::where('platform_type', 3)
                        ->pluck('title', 'id')
                )
                ->rules('required');
//            $form->image('icon','ICON')->rules('required')->default('upload/notice_icon.png');
        });
    }
    private function textNotice()
    {
        return Notice::form(function ($form){
            $form->text('title','標題')->rules('required|max:255');
            $form->text('description')->rules('nullable|max:255');
            $form->textarea('content','內容')->rules('required');
//            $form->image('icon','ICON')->rules('required')->default('upload/notice_icon.png');
        });
    }
}
