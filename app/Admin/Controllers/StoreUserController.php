<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Admin\Model\StoreUser;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;

class StoreUserController extends Controller
{
    use ModelForm;

    public function index()
    {
        Permission::check('store_user');

        return Admin::content(function ($content){
            $content->header('店鋪用戶');
            $content->description(trans('lang.list'));
            $content->body($this->grid());
        });
    }
    public function edit($id)
    {
        Permission::check('store_user');
        return Admin::content(function ($content)use ($id){
            $content->header('店鋪用戶');
            $content->description(trans('lang.edit'));
            $content->body($this->form()->edit($id));
        });
    }
    public function update($id)
    {
        Permission::check('store_user');
        return $this->form($id)->update($id);
    }
    public function destroy($id)
    {
        Permission::check('store_user');

        $ids = explode(',',$id);
        if (StoreUser::whereIn('id',$ids)->delete())
            return response()->json([
                'status'  => true,
                'message' => trans('lang.delete_succeeded'),
            ]);
        return response()->json([
            'status'  => false,
            'message' => trans('lang.delete_failed'),
        ]);
    }

    public function form($id = null)
    {
        Permission::check('store_user');
        return StoreUser::form(function (Form $form)use ($id){
            $form->display('id','ID');
            $form->display('store_id','店鋪ID');
            $form->text('mobile','MOBILE')->rules('required|unique:store_user,mobile,'.$id);
            $form->text('email','EMAIL')->rules('nullable|email|max:50|unique:store_user,email,'.$id);
            $form->text('nickname','昵稱')->rules('required|max:50');
            $form->multipleSelect('menus', '菜單選項')
                ->options(array_pluck(config('adminOption.store_menu'), 'title', 'type'));
            $form->text('password')->rules('nullable');
        });
    }
    private function grid()
    {
        return StoreUser::grid(function (Grid $grid){
            $grid->model()->orderBy('id','DES');
            $grid->id('ID')->sortable();
            $grid->store_id('店鋪ID');
            $grid->mobile('MOBILE');
            $grid->email('EMAIL');
            $grid->nickname('昵稱');
            $grid->gender('性别')->value(function ($gender){
                $genArr = [
                    'male' => '男',
                    'female' => '女',
                ];
                return isset($genArr[$gender])?$genArr[$gender]:'null';
            });
            $grid->filter(function ($filter){
                $filter->is('store_id','店鋪ID');
                $filter->like('mobile','MOBILE');
                $filter->like('email','EMAIL');
                $filter->like('nickname','昵稱');
                $filter->is('permission','權限')->select([
                    'ALL' => '店主',
                    'ONLYSEE' => '檢視',
                    'NONE' => '無權限',
                ]);
                $filter->usemodal();
            });
            $grid->disableCreation();
            $grid->disableExport();
        });
    }
}
