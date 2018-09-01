<?php

namespace App\Admin\Controllers;

use App\Hotword;
use App\Http\Controllers\Controller;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;

class HotWordController extends Controller
{
    use ModelForm;
    public function index()
    {
        Permission::check('hot_word');
        return Admin::content(function ($content){
            $content->header('熱搜詞管理');
            $content->description(trans('lang.list'));
            $content->body($this->grid());
        });
    }
    public function create()
    {
        Permission::check('hot_word');
        return Admin::content(function ($content){
            $content->header('熱搜詞管理');
            $content->description(trans('lang.edit'));
            $content->body($this->form());
        });
    }
    public function edit($id)
    {
        Permission::check('hot_word');
        return Admin::content(function ($content)use ($id){
            $content->header('熱搜詞管理');
            $content->description(trans('lang.edit'));
            $content->body($this->form()->edit($id));
        });
    }
    public function store()
    {
        Permission::check('hot_word');

        return $this->form()->store();
    }
    public function destroy($id)
    {
        Permission::check('hot_word');

        $ids = explode(',',$id);
        if (Hotword::whereIn('id',$ids)->delete())
            return response()->json([
                'status'  => true,
                'message' => trans('lang.delete_succeeded'),
            ]);
        return response()->json([
            'status'  => false,
            'message' => trans('lang.delete_failed'),
        ]);
    }

    public function update($id)
    {
        Permission::check('hot_word');

        return $this->form($id)->update($id);
    }

    public function form($id = null)
    {
        return Hotword::form(function ($form) use ($id){
            $form->display('id','ID');
            $form->text('hot_word','熱搜詞')->rules('required|max:255|unique:hot_word,hot_word,'.$id);
            $form->text('number','熱搜次數')->rules('required|integer');
        });
    }
    private function grid()
    {
        return Hotword::grid(function (Grid $grid){
            $bool = null;
            $grid->model()->orderBy('number','DESC');

            $grid->id('ID')->sortable();
            $grid->hot_word('熱搜詞');
            $grid->number('熱搜次數');

            $grid->filter(function ($filter){
                $filter->like('hot_word','热搜词');
                $filter->usemodal();
            });
            $grid->disableExport();
//            $grid->disableFilter();
//            $grid->disableActions();
//            $grid->disableCreation();
            $grid->disableBatchDeletion();
            $grid->disableRowSelector();
        });
    }
}
