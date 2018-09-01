<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Member;
use App\MemberCredits;
use App\MemberCreditsLog;
use App\Store;
use App\StoreAccount;
use App\StoreTrans;
use App\StoreTransCateogry;
use App\StoreUser;
use App\Withdrawl;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WithdrawController extends Controller
{
    use ModelForm;

    private $type;
    private $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
        if ($request->type){
            $this->type = $request->type;
            session(['withdraw_type' => $this->type]);
        }else{
            $this->type = session('withdraw_type');
        }
    }

    public function index()
    {
        Permission::check('withdraw');
        return Admin::content(function ($content){
            $content->header($this->type==1?'店家提現':'網紅提現');
            $content->description(trans('lang.list'));
            $content->body($this->grid());
        });
    }

    public function show($handle)
    {
        Permission::check('withdraw');
        switch ($handle){
            case 'pass':
                return $this->passWithdraw();
            case 'reject':
                return $this->rejectWithdraw();
        }
    }

    public function destroy($id)
    {
        Permission::check('withdraw');
        if ($member = Withdrawl::find($id)){
            $member->update(['status' => 0]);
            return response()->json([
                'status' => true,
                'message' => '凍結賬號成功',
            ]);
        }
        return response()->json([
            'status' => false,
            'message' => '凍結賬號失敗',
        ]);
    }
    private function rejectWithdraw()
    {
        $id = $this->request->id;
        $handleNote = $this->request->handle_note;
        DB::transaction(function () use (&$id, $handleNote) {
            $withdraw = Withdrawl::find($id);
            if ($withdraw->type == 2){
                $credits = MemberCredits::where('member_id',$withdraw->uid)->first();
                if ($withdraw->amount>$credits->freeze_credits)
                    return;
                $credits->freeze_credits -= $withdraw->amount;
                $credits->promo_credits += $withdraw->amount;
                $credits->save();
            }
            $withdraw->update([
                'status' => 2,
                'handle_note' => $handleNote,
                'handle_date' => date('Y-m-d H:i:s'),
            ]);
            $id = TRUE;
        });
        if ($id === TRUE)
            return response()->json(['status' => true,'message' => '操作成功！',]);
        return response()->json(['status' => false,'message' => '操作失败！',]);
    }
    private function passWithdraw()
    {
        $id = $this->request->id;
        DB::transaction(function () use (&$id){
            $withdraw = Withdrawl::find($id);
            if ($withdraw){
                if ($withdraw->type == 1){
                    if (!$this->storeTrans($withdraw))return;
                }else{
                    if (!$this->memberTrans($withdraw))return;
                }
                $withdraw->update([
                    'status' => 1,
                    'handle_date' => date('Y-m-d H:i:s')
                ]);
            }else{
                return;
            }
            $id = TRUE;
        });
        if ($id === TRUE)
            return response()->json(['status' => true,'message' => '操作成功！',]);
        return response()->json(['status' => false,'message' => '操作失败！',]);
    }
    private function memberTrans($withdraw)
    {
        $credit = MemberCredits::where('member_id',$withdraw->uid)->first();
        if ($credit->freeze_credits<$withdraw->amount)
            return false;
        $data['type'] = 2;
        $data['trade_type'] = '请款支出';
        $data['date'] = date('Y-m-d');
        $data['log_date'] = date('Y-m-d H:i:s');
//        $data['log_no'] = ;
        $data['amount'] = $withdraw->amount;
        $data['balance'] = $credit->freeze_credits+$credit->promo_credits;
        $data['status'] = 1;
        $data['remark'] = '请款支出';
        $data['member_id'] = $withdraw->uid;
        MemberCreditsLog::insert($data);

        $credit->freeze_credits -= $withdraw->amount;
        $credit->save();
        return true;
    }
    private function storeTrans($withdraw)
    {
        $storeAccount = StoreAccount::where('store_id',$withdraw->store_id)->first();
        $store = Store::find($withdraw->store_id);
        $storeUser = StoreUser::find($store->super_uid);
        if (!$storeAccount)
            return false;
        if ($storeAccount->credits_income<$withdraw->amount)
            return false;

        $transCate = StoreTransCateogry::find(5);
        $data['store_id'] = $withdraw->store_id;
        $data['trans_type'] = 2;
        $data['trans_category'] = 5;
        $data['trans_category_name'] = $transCate->name;
        $data['trans_description'] = $transCate->description.',帳務管理費:'.$withdraw->service_charge;
        $data['trans_date'] = date('Y-m-d');
        $data['trans_datetime'] = $data['created_at'] = date('Y-m-d H:i:s');
        $data['amount'] = $withdraw->amount;
        $data['balance'] = $storeAccount->credits_income;
        $data['created_by'] = $storeUser->id;
        $data['created_name'] = $storeUser->nickname;
        StoreTrans::insert($data);

        $storeAccount->credits_income -= $withdraw->amount;
        $storeAccount->save();
        return true;
    }
    private function grid()
    {
        return Withdrawl::grid(function (Grid $grid){
            $flag = null;
            if (!$this->request->status)
                $cond['status'] = 0;
            if ($this->type==1){
                $cond['type'] = 1;
                $grid->model()->where($cond)->orderBy('id','DESC');
                $grid->id('ID')->sortable();
                $grid->store_id('店家ID');
            }else{
                $cond['type'] = 2;
                $grid->model()->where($cond)->orderBy('id','DESC');
                $grid->id('ID')->sortable();
                $grid->uid('網紅ID');
            }
            $grid->amount('金額');
            $grid->bank_name('銀行');
            $grid->receiver_name('收款人');
            $grid->bank_account('銀行賬戶');
            $grid->bank_phone('預留手機號');
            $grid->status('提現狀態')->value(function ($status) use (&$flag){
                $statusArr = [0=>'提現中',1=>'提現完成',2=>'提現失敗'];
                $flag = $status;
                return $statusArr[$status];
            });
            $grid->created_at('申請日期')->sortable();

            $grid->disableCreation();
            if ($this->request->status>0){
                $grid->disableActions();
            }else{
                $grid->actions(function ($actions) use ($flag){
                    $id = $actions->getkey();
                    $checkBtn = "<a href='javascript:passWithdraw({$id},this)' title='完成'><i class='fa fa-check'></i> </a>";
                    $removeBtn = "<a href='javascript:rejectWithdraw({$id},this)' title='失敗'><i class='fa fa-remove'></i> </a>";
                    $actions->prepend($removeBtn);
                    $actions->prepend($checkBtn);
                    $actions->disableEdit();
                    $actions->disableDelete();
                });
            }

            $grid->filter(function ($filter) {
                if ($this->type==1){
                    $filter->is('store_id','店家ID');
                }else{
                    $filter->is('uid','網紅ID');
                }
                $filter->like('bank_name','銀行');
                $filter->like('receiver_name','收款人');
                $filter->like('bank_phone','預留手機號');
                $filter->is('status','提現狀態')->select([
                    0=>'提現中',1=>'提現完成',2=>'提現失敗'
                ]);
                $filter->useModal();
            });
        });
    }
}
