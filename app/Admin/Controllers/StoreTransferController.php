<?php

namespace App\Admin\Controllers;

use App\Admin\Provider\AdminHelpers;
use App\Api\V1\Services\BaseService;
use App\Http\Controllers\Controller;
use App\StoreTransfer;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;
use Illuminate\Support\Facades\Auth;

class StoreTransferController extends Controller
{
    use ModelForm;

    public function index()
    {
        Permission::check('withdraw');
        return Admin::content(function ($content){
            $content->header('店鋪充值申請');
            $content->description(trans('lang.list'));
            $content->body($this->grid());
        });
    }

    public function update($id)
    {
        Permission::check('withdraw');
        $status = request('status');
        $arr = [
            'pending',
            'processing',
            'cancelled',
            'refunded',
            'completed',
        ];
        if (in_array($status, $arr)){
            StoreTransfer::where('id', $id)
                ->whereNotIn('status', ['cancelled', 'completed'])
                ->update(['status' => $status, 'updated_by' => \auth('admin')->id()]);
            return AdminHelpers::jsonResponse('操作成功！');
        }
        return AdminHelpers::jsonResponse('無效的操作！', false);

    }

    private function grid()
    {
        return StoreTransfer::grid(function (Grid $grid){
            $status = empty(request('status'))?'pending':request('status');
            $grid->model()->where('status', $status)->orderBy('id', 'DESC');
            $grid->store_id('店鋪ID');
            $grid->transfer_date('轉賬日期');
            $grid->account_no('銀行賬號');
            $grid->amount('匯款金額');
            $grid->status('狀態')->value(function ($status){
                $arr = [
                    'pending' => '待處理',
                    'processing' => '處理中',
                    'cancelled' => '已取消',
                    'refunded' => '退還',
                    'completed' => '處理完成',
                ];
                return $arr[$status];
            });
            $grid->attachment('附件')->value(function ($uri){
                $url = BaseService::image($uri);
                return "<img src='{$url}' style='max-height: 40px' onclick='javascript:alertImg(\"{$url}\")'>";
            });
            $grid->created_by('申請人');
            $grid->created_at('申請日期')->sortable();
//            $grid->updated_by('操作人員');
            $grid->disableCreation();
            $grid->disableExport();
            if (in_array($status, ['cancelled', 'completed'])){
                $grid->disableActions();
            }else{
                $grid->actions(function ($actions) use ($status){
                    $id = $actions->getkey();
                    $back = "<a href='javascript:storeTransfer({$id}, \"pending\")' title='重新處理'>重新處理 </a>";
                    $next = "<a href='javascript:storeTransfer({$id}, \"processing\")' title='通過審核'>通過 </a>";
                    $refund = "<a href='javascript:storeTransfer({$id}, \"refunded\")' title='退還'>退還 </a>";
                    $cancel = "<a href='javascript:storeTransfer({$id}, \"cancelled\")' title='取消'>取消 </a>";
                    $complete = "<a href='javascript:storeTransfer({$id}, \"completed\")' title='完成'>完成 </a>";
                    if ($status == 'pending'){
                        $actions->prepend($cancel);
                        $actions->prepend($refund);
                        $actions->prepend($next);
                    }elseif($status == 'processing'){
                        $actions->prepend($back);
                        $actions->prepend($complete);
                    }elseif ($status == 'refunded'){
                        $actions->prepend($back);
                        $actions->prepend($complete);
                    }
                    $actions->disableEdit();
                    $actions->disableDelete();
                });
            }
            $grid->filter(function (Grid\Filter $filter) {
                $filter->disableIdFilter();
                $filter->is('store_id','店鋪ID');
                $filter->is('status','申請狀態')->select([
                    'pending' => '待處理',
                    'processing' => '處理中',
                    'cancelled' => '已取消',
                    'refunded' => '退還',
                    'completed' => '處理完成',
                ]);
            });
        });
    }
}
