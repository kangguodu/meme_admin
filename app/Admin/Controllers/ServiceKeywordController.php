<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\ServiceKeyword;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;

class ServiceKeywordController extends Controller
{
    use ModelForm;

    public function index()
    {
        Permission::check('service');

        return Admin::content(function (Content $content){
            $content->header('客服-關鍵字');
            $content->description(trans('lang.list'));
            $content->body($this->grid());
        });
    }

    public function create()
    {
        Permission::check('service');

        return Admin::content(function (Content $content){
            $content->header('客服-關鍵字');
            $content->description(trans('lang.create'));
            $content->body($this->form());
        });
    }

    public function destroy($id)
    {
        Permission::check('service');

        $ids = explode(',',$id);
        if (ServiceKeyword::whereIn('id',$ids)->delete())
            return response()->json([
                'status'  => true,
                'message' => trans('lang.delete_succeeded'),
            ]);
        return response()->json([
            'status'  => false,
            'message' => trans('lang.delete_failed'),
        ]);
    }

    public function edit($id)
    {
        Permission::check('service');

        return Admin::content(function (Content $content) use ($id){
            $content->header('客服-關鍵字');
            $content->description(trans('lang.edit'));
            $content->body($this->form()->edit($id));
        });
    }

    public function update($id)
    {
        Permission::check('service');

        return $this->form()->update($id);
    }

    public function form()
    {
        return ServiceKeyword::form(function (Form $form){
            $form->display('id', 'ID');
            $form->text('keyword', '關鍵詞')->rules('required|max:255');
            $form->textarea('content', '回復')->rules('required|max:255');
            $form->hidden('type')->default('text');
        });
    }

    public function grid()
    {
        return ServiceKeyword::grid(function (Grid $grid){
            $grid->model();
            $grid->id('ID');
            $grid->keyword('關鍵詞');
            $grid->content('回復');

            $grid->disableExport();
            $grid->filter(function (Grid\Filter $filter){
                $filter->disableIdFilter();
                $filter->like('keyword', '關鍵詞');
            });
        });
    }
}
