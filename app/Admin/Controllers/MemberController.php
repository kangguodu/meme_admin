<?php

namespace App\Admin\Controllers;

use App\Http\Controllers\Controller;
use App\Member;
use App\PromoCode;
use App\StoreUser;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;
use Illuminate\Http\Request;
use App\Api\V1\Services\BaseService;
use Illuminate\Support\Facades\DB;

class MemberController extends Controller
{
    use ModelForm;

    public function index()
    {
        Permission::check('member');
        return Admin::content(function ($content){
            $content->header('會員管理');
            $content->description(trans('lang.list'));
            $content->body($this->grid());
        });
    }

    public function edit($id)
    {
        Permission::check('member');
        return Admin::content(function ($content) use ($id){
            $content->header('會員管理');
            $content->description(trans('lang.edit'));
            $content->row(Member::form(function ($form)use($id){
                $form->method('UPDATE');
                $form->display('id','ID');
                $form->hidden('id','ID');
                $form->text('phone','PHONE')->rules("required|numeric|unique:member,phone,{$id}");
                $form->text('email','EMAIL')->rules('email');
                $form->text('username','用戶名')->rules('max:255');
                $form->text('nickname','昵稱')->rules('max:50');
                $form->radio('gender','性別')->values([
                    1 => '男',
                    2 => '女',
                ])->rules('between:1,2');
                $form->image('avatar','頭像');
                $form->date('birthday','生日')->rules('date');
                $form->radio('user_type','賬戶類型')->values([
                    1 => '個人',
                    2 => '網紅',
                ])->rules('required','min:1','max:2');
                $form->switch('status','賬戶狀態');
                $form->display('invite_count','邀請人數');
                $form->display('created_at','注冊時間');
            })->edit($id));
        });
    }

    public function form()
    {
        $id = \request('id');
        return Member::form(function ($form)use ($id){
            $form->method('UPDATE');
            $form->display('id','ID');
            $form->text('phone','PHONE')->rules("required|numeric|unique:member,phone,".$id);
            $form->text('email','EMAIL')->rules('nullable|email');
            $form->text('username','用戶名')->rules('max:255');
            $form->text('nickname','昵稱')->rules('max:50');
            $form->radio('gender','性別')->values([
                1 => '男',
                2 => '女',
            ])->rules('between:1,2');
            $form->image('avatar','頭像');
            $form->date('birthday','生日')->rules('nullable|date');
            $form->radio('user_type','賬戶類型')->values([
                1 => '個人',
                2 => '網紅',
            ])->rules('required','min:1','max:2');
            $form->switch('status','賬戶狀態');
            $form->display('invite_count','邀請人數');
            $form->display('created_at','注冊時間');
        });
    }

    public function update($id,Request $request)
    {
        Permission::check('member');

        if ($request->user_type == 2)
            $this->setCode($id);
        return $this->form()->update($id);

    }

    private function setCode($id)
    {
        $user = Member::find($id);
        if (empty($user->invite_code)){
            DB::transaction(function ()use ($user){
                $codeObj = PromoCode::where('used', 0)
                    ->orderBy(\DB::raw('RAND()'))
                    ->first();
                $user->invite_code = $codeObj->code;
                $user->save();
                $codeObj->used = 1;
                $codeObj->save();
            });
        }
    }

    public function destroy($id)
    {
        Permission::check('member');
        $ids = explode(',',$id);
        if ($member = Member::whereIn('id',$ids)->update(['status' => 0]))
            return response()->json([
                'status' => true,
                'message' => '凍結賬號成功',
            ]);
        return response()->json([
            'status' => false,
            'message' => '凍結賬號失敗',
        ]);
    }

    private function grid()
    {
        return Member::grid(function (Grid $grid){
            $grid->model()->orderBy('id','DESC');

            $grid->id('ID')->sortable();
            $grid->avatar('頭像')->value(function ($avatar){
                $url = $avatar ? BaseService::image($avatar) : url('/images/avatar/').'/avatar.png';
                return "<a href='{$url}' target='_blank'><img src='{$url}' style='max-height: 30px;max-width: 30px;'/></a>";
            });
            $grid->username('用戶名');
            $grid->nickname('昵稱');
            $grid->phone('PHONE');
            $grid->email('EMAIL');
            $grid->gender('性別')->value(function ($gender){
                $arr = [1=>'男',2=>'女'];
                return empty($arr[$gender])?'':$arr[$gender];
            });
            $grid->account()->total_credits('現有蜂幣');
            $grid->account()->grand_total_credits('累計返利蜂幣');
            $grid->status('賬戶狀態')->value(function ($status){
                return $status?'正常':'關閉';
            });
            $grid->user_type('用戶類型')->value(function ($userType){
                return $userType!=2?'個人':'網紅';
            });
            $grid->created_at('注冊時間')->sortable();

            $grid->disableCreation();
            $grid->disableExport();
            $grid->filter(function ($filter) {
                $filter->like('phone','PHONE');
                $filter->is('user_type','用戶類型')->select([
                        1=>'個人',
                        2=>'網紅',
                    ]);
                $filter->like('username','用戶名');
                $filter->is('status','查詢凍結賬戶')->select([
                    0 =>'是',
                    1 => '否',
                ]);
                $filter->useModal();
            });
        });
    }
}
