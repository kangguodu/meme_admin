<?php

namespace App\Admin\Controllers;

use App\Admin\Provider\AdminHelpers;
use App\Http\Controllers\Controller;
use App\StoreType;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;


class StoreTypeController extends Controller
{
    use ModelForm;

    public function index()
    {
        Permission::check('store');
        return Admin::content(function ($content){
            $content->header('店鋪業態');
            $content->description(trans('lang.list'));
            $content->body($this->grid());
        });
    }

    public function create()
    {
        Permission::check('store');
        return Admin::content(function ($content){
            $content->header('店鋪業態');
            $content->description(trans('lang.create'));
            $content->body($this->form());
        });
    }

    public function store()
    {
        Permission::check('store');

        return $this->form()->store();
    }

    public function edit($id)
    {
        Permission::check('store');

        return Admin::content(function ($content) use ($id){
            $content->header('店鋪業態');
            $content->description(trans('lang.create'));
            $content->body($this->form()->edit($id));
        });
    }

    public function update($id)
    {
        Permission::check('store');

        return $this->form()->update($id);
    }

    public function destroy($id)
    {
        Permission::check('store');

        $ids = explode(',', $id);
        if (StoreType::whereIn('id', $ids)->delete())
            return AdminHelpers::jsonResponse("刪除數據成功！");
        return AdminHelpers::jsonResponse("刪除數據失敗！", false);
    }

    private function form()
    {
        return StoreType::form(function (Form $form){
            $form->text('name', '業態類型')->rules('required|max:50');
            $form->radio('published', '是否使用')->options([
                0 => '停用',
                1 => '使用',
            ])->rules('required|integer|between:0,1')
                ->default(1);
        });
    }

    private function grid()
    {
        return StoreType::grid(function (Grid $grid) {
            $grid->model()->orderBy('id', 'DES');

            $grid->id("ID")->sortable();
            $grid->name("業態類型");
            $grid->published("是否使用")->value(function ($status){
                return $status?"使用":"停用";
            });
            $grid->disableExport();
            $grid->filter(function (Grid\Filter $filter){
                $filter->disableIdFilter();
                $filter->like('name','業態類型');
            });
        });
    }

    public function getStoreTypes(){
        $result = (new StoreType())->where('published','=',1)
            ->select([
                'id',
                'name'
            ])->get();
        if($result->isEmpty()){
            return response()->json([]);
        }else{
            return response()->json($result);
        }
    }
}
