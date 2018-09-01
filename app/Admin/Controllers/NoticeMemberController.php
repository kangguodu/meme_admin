<?php

namespace App\Admin\Controllers;

use App\Activity;
use App\Admin\Provider\Notification;
use App\Http\Controllers\Controller;
use App\Notice;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Widgets\Box;
use Illuminate\Http\Request;

class NoticeMemberController extends Controller
{
    use ModelForm;

    public function index()
    {
        Permission::check('notice');

        return Admin::content(function ($content){
            $content->header('會員推送管理');
            $content->description(trans('lang.list'));
            $content->body($this->grid());
        });
    }

    public function create(Request $request)
    {
        Permission::check('notice');

        return Admin::content(function ($content) use ($request){
            $content->header('會員推送管理');
            $content->description(trans('lang.create'));
            $content->body($this->noticeType($request));
        });
    }


    public function store(Request $request)
    {
        Permission::check('notice');
        if ($request->get('point_id', false) !== false){
            return $this->storeActivityNotice($request);
        }elseif ($request->get('url', false) !== false){

            return $this->storeUrlNotice($request);
        }elseif ($request->get('content', false) !== false){
            return $this->storeSystemNotice($request);
        }

    }
    private function storeActivityNotice($request)
    {
        $activity = Activity::findOrFail($request->point_id);
        $response = $this->activityNotice()->store();
        $notice = Notice::where($request->only(['title','description', 'point_id']))
            ->orderBy('id','DES')
            ->first();
        if ($notice){
            $notice = new Notification();
            $notice->prepare('member', $request->only(['title','description', 'point_id']), 'activity', $activity->toArray())
                ->send();
        }
        return $response;
    }
    private function storeSystemNotice($request)
    {
        $response = $this->textNotice()->store();
        $notice = Notice::where($request->only(['title','description', 'content']))
            ->orderBy('id','DES')
            ->first();
        if ($notice){
            $notification = new Notification();
            $notification->prepare('member', $request->only(['title','description']), 'system', $notice->toArray())
                ->send();
        }
        return $response;
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

        $activityUrl = admin_url('member_notice/create?type=activity');
//        $htmlUrl = admin_url('member_notice/create?type=url');
        $textUrl = admin_url('member_notice/create?type=text');
        $btn = "<div style='margin-top: 200px;margin-bottom: 200px;min-height: 300px;' class='text-center'><a href='{$activityUrl}' class='btn btn-success btn-lg'>活動推送</a>";
//        $btn .= "<a href='{$htmlUrl}' class='btn btn-success btn-lg'>H5推送</a>";
        $btn .= "&nbsp;&nbsp;&nbsp;&nbsp;<a href='{$textUrl}' class='btn btn-success btn-lg'>系統通知</a>";
        $btn .= "</div>";
        $box->title('推送類型');
        $box->content($btn);
        return $box;
    }
    private function activityNotice()
    {
        return Notice::form(function (Form $form){
            $form->hidden('type_id')->default(1);
            $form->text('title','標題')->rules('required|max:255');
            $form->text('description')->rules('nullable|max:255');
            $form->select('point_id','活動')
                ->options(
                    Activity::where('platform_type', 1)
                        ->pluck('title', 'id')
                )
                ->rules('required');
            $form->image('icon','ICON')->rules('required');
        });
    }

    private function textNotice()
    {
        return Notice::form(function ($form){
            $form->hidden('type_id')->default(3);
            $form->text('title','標題')->rules('required|max:255');
            $form->text('description','描述')->rules('nullable|max:255');
            $form->textarea('content','內容')->rules('required');
            $form->image('icon','ICON')->rules('required');
        });
    }


    private function grid()
    {
        return Notice::grid(function (Grid $grid){
            $grid->model()->whereIn('type_id', [1,3])->orderBy('created_at','DES');
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
                return empty($time)?'':$time;
            });

            $grid->disableFilter();
            $grid->disableActions();
            $grid->disableExport();
            $grid->disableRowSelector();
            $grid->disableBatchDeletion();
        });
    }

//    public function show($id)
//    {
//        $notice = new Notification();
//        $notice->prepare('member', [
//            'title' => 'title',
//            'description' => '描述'
//        ], 'activity', [
//            'id' =>1,
//            'type' => 2,
//        ])
//            ->send();
//    }

//    private function urlNotice()
//    {
//        return Notice::form(function ($form){
//            $form->text('title','標題')->rules('required|max:255');
//            $form->text('description')->rules('nullable|max:255');
//            $form->text('url','URL')->rules('required|max:255|url');
//            $form->image('icon','ICON')->rules('required');
//        });
//    }
}
