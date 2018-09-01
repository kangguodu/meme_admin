<?php

namespace App\Admin\Controllers;

use App\Admin\Provider\AdminHelpers;
use App\Http\Controllers\Controller;
use App\Store;
use App\StoreBankAccount;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Illuminate\Http\Request;

class StoreBankAccountController extends Controller
{
    use ModelForm;

    public $storeId;
    public $store;

    public function index()
    {
        Permission::check('store');
        if (!$this->storeId = $storeId = AdminHelpers::getArgumentFromGetOrSession('store_id'))
            return redirect(admin_url(''));
        $this->store = $store = Store::findOrFail($storeId);
        return Admin::content(function ($content) use ($store){
            $content->header("{$store->name}—銀行卡管理");
            $content->description(trans('lang.list'));
            $content->body($this->grid());
        });
    }

    public function create()
    {
        Permission::check('store');
        return Admin::content(function ($content){
            $content->header("銀行卡管理");
            $content->description(trans('lang.create'));
            $content->body($this->form());
        });
    }

    public function destroy($id)
    {
        Permission::check('store');

        $ids = explode(',', $id);
        if (StoreBankAccount::whereIn('id', $ids)->delete())
            return AdminHelpers::jsonResponse('數據刪除成功！', true);
        return AdminHelpers::jsonResponse('數據刪除失敗！', false);
    }

    public function edit($id)
    {
        Permission::check('store');
        return Admin::content(function ($content) use ($id){
            $content->header("銀行卡管理");
            $content->description(trans('lang.edit'));
            $content->body($this->form()->edit($id));
        });
    }

    public function form()
    {
        $storeId = AdminHelpers::getArgumentFromGetOrSession('store_id');
        return StoreBankAccount::form(function (Form $form) use ($storeId){
            $form->hidden('store_id')->default($storeId);
            $form->text('bank_name', '銀行')->rules('required|max:50');
            $form->text('receiver_name', '收款人')->rules('required|max:50');
            $form->text('bank_account', '銀行賬戶')->rules('required|max:250');
            $form->text('bank_phone', '電話號碼')->rules('required|max:50');
            $form->text('region', '地區')->rules('nullable|max:50');
            $form->text('branch_name', '支行')->rules('nullable|max:250');
        });
    }

    public function store()
    {
        Permission::check('store');

        return $this->form()->store();
    }

    public function update($id)
    {
        Permission::check('store');

        return $this->form()->update($id);
    }

    private function grid()
    {
        return StoreBankAccount::grid(function (Grid $grid){
            $grid->model()->where('store_id', $this->storeId)->orderBy('id','DES');

            $grid->id('ID');
            $grid->bank_name('銀行');
            $grid->receiver_name('收款人');
            $grid->bank_account('銀行賬戶');
            $grid->bank_phone('電話號碼');
            $grid->region('地區');
            $grid->branch_name('支行');
            $grid->created_at('創建時間');

            $grid->disableExport();
            $grid->disableFilter();
            $grid->disablePagination();
        });
    }
}
