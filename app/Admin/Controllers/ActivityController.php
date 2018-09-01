<?php

namespace App\Admin\Controllers;

use App\Activity;
use App\Admin\Provider\AdminHelpers;
use App\Http\Controllers\Controller;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;
use Encore\Admin\Widgets\Box;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ActivityController extends Controller
{
    use ModelForm;

    public function index()
    {
        Permission::check('activity');
        return Admin::content(function ($content){
            $content->header('活动管理');
            $content->description(trans('lang.list'));
            $content->body($this->grid());
        });
    }
    public function show($handle, Request $request)
    {
        switch ($handle){
            case 'getActivity':
                $activity = Activity::where('id',$request->activity_id)->first(['title','description']);
                if ($activity)
                    return response()->json($activity->toArray());
                return;
            case 'pushActivity':
                return $this->pushNotice($request);
        }
        return response()->json(['title'=>'title','description'=>'content']);
    }

    public function create(Request $request)
    {
        Permission::check('activity');
        if (empty($request->type))
            return $this->selectType();

        switch ($request->type){
            case 'text':
                $form = $this->textForm();
                break;
            case 'html':
                $form = $this->htmlForm();
                break;
            default:
                return redirect()->action('\App\Admin\Controllers\ActivityController@index');
        }
        return Admin::content(function ($content) use ($form){
            $content->header('活动管理');
            $content->description(trans('lang.create'));
            $content->body($form);
        });
    }

    public function edit($id)
    {
        Permission::check('activity');
        $activity = Activity::find($id);
        if ($activity->type==1){
            $form = $this->textForm()->edit($id);
        }else{
            $form = $this->htmlForm()->edit($id);
        }
        return Admin::content(function ($content)use ($form){
            $content->header('活动管理');
            $content->description(trans('lang.edit'));
            $content->body($form);
        });
    }
    public function store(Request $request)
    {
        Permission::check('activity');
        if ($request->type==1){
            return $this->textForm()->store();
        }else{
            return $this->htmlForm()->store();
        }
    }
    public function update($id)
    {
        Permission::check('activity');
        $activity = Activity::find($id);
        if ($activity->type==1){
            return $this->textForm()->update($id);
        }else{
            return $this->htmlForm()->update($id);
        }
    }

    public function destroy($id)
    {
        Permission::check('activity');
        $ids = explode(',',$id);
        if (Activity::whereIn('id',$ids)->delete()){
            return response()->json([
                'status'  => true,
                'message' => trans('lang.delete_succeeded'),
            ]);
        } else {
            return response()->json([
                'status'  => false,
                'message' => trans('lang.delete_failed'),
            ]);
        }
    }

    private function pushNotice(Request $request)
    {
        $rules = [
            'id' => 'required|integer|min:1',
            'title' => 'required',
            'content' => 'required',
        ];
        $data = $request->only(['id','title','content']);
        $validator = Validator::make($data, $rules);
        if ($validator->fails())
            return AdminHelpers::jsonResponse($validator->errors()->first(),false);
    }
    private function textForm()
    {
        return Activity::form(function ($form){
            $form->method('UPDATE');
            $form->select('platform_type','平臺類型')->options([1 => '會員',2 => '商家',3 => '网红']);
            $form->text('title','標題')->rules('required|max:255');
            $form->hidden('type')->default(1);
            $form->hidden('created_by')->default(Admin::user()->name);
            $form->textarea('description','描述')->rules('required|max:255');
            $form->number('discount','折扣');
            $form->datetime('show_time','推出時間')->rules('required|date');
            $form->datetime('start_at','開始時間')->rules('required|date');
            $form->datetime('expire_at','過期時間')->rules('required|date');
            $form->ueditor('content','內容')->rules('required');
            $form->image('posters_pictures','海報')->rules('required_if:platform_type,1|required_if:platform_type,3');
        });
    }

    private function htmlForm()
    {
        return Activity::form(function ($form){
            $form->method('UPDATE');
            $form->select('platform_type','平臺類型')->options([1 => '會員',2 => '商家',3 => '网红']);
            $form->text('title','標題')->rules('required|max:255');
            $form->hidden('type')->default(2);
            $form->hidden('created_by')->default(Admin::user()->name);
            $form->textarea('description','描述')->rules('required|max:255');
            $form->number('discount','折扣');
            $form->datetime('show_time','推出時間')->rules('required|date');
            $form->datetime('start_at','開始時間')->rules('required|date');
            $form->datetime('expire_at','過期時間')->rules('required|date');
            $form->text('url','URL')->rules('required|max:255');
            $form->image('posters_pictures','海報')->rules('required_if:platform_type,1|required_if:platform_type,3');
        });
    }

    private function selectType()
    {
        return Admin::content(function ($content){
            $content->header('活动管理');
            $content->description(trans('lang.create'));

            $content->row(function ($row){
                $box = new Box();

                $textUrl = admin_url('activity/create?type=text');
                $htmlUrl = admin_url('activity/create?type=html');
                $btn = "<div class='col-md-6'style='margin-top: 200px;margin-bottom: 200px'><a href='{$textUrl}' class='pull-right btn btn-success btn-lg'>新建圖文活動</a></div>";
                $btn .= "<div class='col-md-4'style='margin-top: 200px;margin-bottom: 200px'><a href='{$htmlUrl}' class='btn btn-success btn-lg text-center'>新建HTML活動</a></div>";

                $box->title('選擇活動類型');
                $box->content($btn);
                $row->Column(12,$box);
            });
        });
    }

    private function grid()
    {
        return Activity::grid(function (Grid $grid){
            $grid->model()->orderBy('id','DESC');

            $grid->id('ID')->sortable();
            $grid->posters_pictures('海報')->value(function ($pic){
                $url = url($pic);
                return empty($pic)?'':"<img src='{$url}' style='height: 40px;'>";
            });
            $grid->title('標題');
            $grid->description('描述');
            $grid->type('類型')->value(function ($type){
                $arr = [1 => '圖文',2 => 'URL',];
                return isset($arr[$type])?$arr[$type]:'未選定類型';
            });
            $grid->platform_type('活動平臺')->value(function ($type){
                $arr = [1 => '會員',2 => '商家',3 => '网红'];
                return isset($arr[$type])?$arr[$type]:'未選定類型';
            });
            $grid->created_by('發布者');
            $grid->show_time('推出時間')->sortable();
            $grid->start_at('開始時間')->sortable();
            $grid->expire_at('過期時間')->sortable();
            $grid->created_at('創建時間')->sortable();

            $grid->filter(function ($filter) {
                $filter->disableIdFilter();

                $filter->like('title','標題');
                $filter->is('type','類型')->select([
                    1=>'圖文',
                    2=>'URL',
                ]);
                $filter->is('platform_type','平臺類型')->select([
                    1 => '會員',2 => '商家',3 => '网红'
                ]);
//                $filter->useModal();
            });

            $grid->disableExport();
        });
    }
}
