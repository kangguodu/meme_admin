<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\ServiceAutoReply;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Layout\Content;
use Encore\Admin\Tree;
use Encore\Admin\Widgets\Box;

class ServiceAutoReplyController extends Controller
{
    use ModelForm;

    public function index()
    {
        Permission::check('service');

        return Admin::content(function (Content $content){
            $content->header('客服-自動回復');
            $content->description(trans('lang.list'));
            $content->row(function ($row){
                $row->Column(6, $this->tree());

                $form = new \Encore\Admin\Widgets\Form();
                $form->action(admin_url('service_auto_reply'));
                $form->select('parent_id', '父級標題')
                    ->options(ServiceAutoReply::selectOptions())
                    ->rules("required");
                $form->textarea('title', "內容")
                    ->rules('required|max:255')
                    ->help('輸入對上級標題的回復，或創建新的標題！');
                $row->Column(6, new Box('創建', $form));
            });
        });
    }

    public function edit($id)
    {
        Permission::check('service');

        return Admin::content(function (Content $content) use ($id){
            $content->header('客服-自動回復');
            $content->description(trans('lang.edit'));
            $content->body($this->form()->edit($id));
        });
    }

    public function update($id)
    {
        Permission::check('service');

        return $this->form()->update($id);
    }

    public function form($url = null)
    {
        return ServiceAutoReply::form(function (Form $form){
            $form->select('parent_id', '父級標題')
                ->options(ServiceAutoReply::selectOptions())
                ->rules("required");
            $form->textarea('title', "內容")
                ->rules('required|max:255')
                ->help('輸入對上級標題的回復，或創建新的標題！');
        });
    }

    public function destroy($id)
    {
        Permission::check('service');

        $ids = explode(',',$id);
        $this->deleteCirculate($ids);
        if (ServiceAutoReply::whereIn('id',$ids)->delete())
            return response()->json([
                'status'  => true,
                'message' => trans('lang.delete_succeeded'),
            ]);
        return response()->json([
            'status'  => false,
            'message' => trans('lang.delete_failed'),
        ]);
    }

    private function deleteCirculate($ids)
    {
        $kid = ServiceAutoReply::whereIn('parent_id', $ids)->get(['id']);
        if (!$kid->isEmpty()){
            $this->deleteCirculate($kid->toArray());
        }
        ServiceAutoReply::whereIn('parent_id', $ids)->delete();
    }


    private function tree()
    {
        return ServiceAutoReply::tree(function (Tree $tree){
            $tree->branch(function ($branch){
                $icon = isset($branch['children'])?'thumb-tack':'pencil';
                $payload = "<i class='fa fa-{$icon}'></i> <strong>{$branch['title']}</strong>";
                return $payload;
            });
            $tree->disableCreate();
        });
    }
}
