<?php

namespace App\Admin\Controllers;

use App\Admin\Provider\AdminHelpers;
use App\Goods;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;

class StoreFoodListController extends Controller
{
    use ModelForm;

    public function index()
    {
        Permission::check('store');

        return Admin::content(function ($content){
            $content->header('店鋪特色菜');
            $content->description(trans('lang.list'));
            $content->body($this->grid());
        });
    }

    public function create()
    {
        Permission::check('store');
        return Admin::content(function ($content) {
            $content->header('店鋪特色菜');
            $content->description(trans('lang.create'));
            $content->body($this->form());
        });
    }

    public function edit($id)
    {
        Permission::check('store');
        return Admin::content(function ($content) use ($id){
            $content->header('店鋪特色菜');
            $content->description(trans('lang.edit'));
            $content->body($this->form()->edit($id));
        });
    }

    public function destroy($id)
    {
        Permission::check('store');

        $ids = explode(',', $id);
        if (Goods::whereIn('id', $ids)->delete())
            return response()->json([
                'status'  => true,
                'message' => trans('lang.delete_succeeded'),
            ]);
        return response()->json([
            'status'  => false,
            'message' => trans('lang.delete_failed'),
        ]);
    }

    private function form()
    {
        $storeId = AdminHelpers::getArgumentFromGetOrSession('store_id');
        return Goods::form(function (Form $form) use ($storeId){
            $form->display("store_id", "店鋪ID")->default($storeId);
            $form->hidden("store_id")->default($storeId)->rules('required|integer|exists:store,id');
            $form->text("goods_name", "菜品名稱")->rules("required|max:255");
            $form->text("price", "原價")->rules("required|numeric");
//            $form->text("prom_price", "優惠價")->rules("required|numeric");
            $form->image('image', "圖片")->rules("required");

            $form->hidden('created_at');
            $form->saving(function (Form $form) {
                $form->created_at = time();
            });
        });
    }

    private function grid()
    {
        $storeId = AdminHelpers::getArgumentFromGetOrSession('store_id');
        return Goods::grid(function (Grid $grid) use ($storeId){
            $grid->model()->where('store_id', $storeId);

            $grid->id("ID");
            $grid->store_id("店鋪ID");
            $grid->image("圖片")->value(function ($image){
                $url = url((string)$image);
                return "<img src='{$url}' style='max-height: 40px'/>";
            });
            $grid->goods_name("食品名稱");
            $grid->price('原價');
//            $grid->prom_price('優惠價');
            $grid->created_at("創建時間")->value(function ($tz){
                return $tz ? Carbon::createFromTimestamp($tz)->toDateTimeString() : '';
            });
            $count = Goods::where('store_id',$storeId)->count();
            if($count >= 3){
                $grid->disableCreation();
            }
            $grid->disableExport();
            $grid->disableFilter();
        });
    }
}
