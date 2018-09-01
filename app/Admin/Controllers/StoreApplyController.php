<?php

namespace App\Admin\Controllers;

use App\StoreApply;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Widgets\Table;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Auth\Permission;

class StoreApplyController extends Controller
{
    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        $status = $_GET['status'];
        return Admin::content(function (Content $content) use ($status) {
            $content->header('商家入駐申請');
            if($status){
                $content->description('已處理');
            }else{
                $content->description('待審核');
            }
            $this->navbar($content);
            $obj = StoreApply::where('status',$status)->paginate(20);
            $content->row(function ($row)use ($obj,$status){
                $box = new Box;
                $box->title('待審核');

                $this->checkedTable($obj,$box,$status);
                $row->Column(12,$box);
                $row->Column(12,$obj->links());
            });
            $this->writeToken($content);
        });
    }

    public function handle()
    {
        Permission::check('store_apply');
        if (StoreApply::where('id',$_POST['id'])->update(['status'=>1]))
            echo "success";
    }

    public function delete()
    {
        Permission::check('store_apply');
        if (StoreApply::where('id',$_POST['id'])->delete())
            echo "刪除成功";
    }
  
    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(StoreApply::class, function (Form $form) {

            $form->display('id', 'ID');

            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
        });
    }

    private function navbar(&$content)
    {
        $div = '<a href="'.url('admin/storeapply?status=0').'" class="btn btn-default">申請中</a>';
        $div .= '<a href="'.url('admin/storeapply?status=1').'" class="btn btn-default">已處理</a>';
        $content->row($div);
    }

    private function checkedTable($obj,&$box,$status)
    {
        $headers = ['姓名','省份','城市','詳細地址','聯繫電話','公司名稱','統一編號','營業類別','申請時間','其他','操作'];

        $rows = array();
        foreach ($obj as $apply) {
            if($status == 0){
                $handle = '<a href="javascript:apply('.intval($apply->id).')" title="已處理"><i class="fa fa-check"></i></a>';
            }else{
                $handle = '<a href="javascript:destory('.intval($apply->id).')" title="刪除"><i class="fa fa-trash"></i></a>';

            }
            $row = [$apply->name,$apply->province,$apply->city,$apply->address,
                $apply->phone,$apply->company_name,$apply->company_tax_no,$apply->type_name,$apply->created_at,$apply->other,$handle];

            array_push($rows,$row);
        }
        $box->content(new Table($headers,$rows));
    }
    private function writeToken(&$content)
    {
        $content->row('<input name="_token" type="hidden" value="'.csrf_token().'">');
    }
}
