<?php

namespace App\Admin\Controllers;

use App\Cooperation;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Widgets\Table;
use Encore\Admin\Widgets\Box;
use Encore\Admin\Auth\Permission;

class CooperationController extends Controller
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
            $content->header('我要合作');
            if($status){
                $content->description('已處理');
            }else{
                $content->description('待處理');
            }
            $this->navbar($content);
            $obj = Cooperation::where('status',$status)->paginate(20);
            $content->row(function ($row)use ($obj,$status){
                $box = new Box;
                $box->title('待處理');

                $this->checkedTable($obj,$box,$status);
                $row->Column(12,$box);
                $row->Column(12,$obj->links());
            });
            $this->writeToken($content);
        });
    }
    public function handle()
    {
        Permission::check('cooperation');
        if (Cooperation::where('id',$_POST['id'])->update(['status'=>1]))
            echo "success";
    }

    public function delete()
    {
        Permission::check('store_apply');
        if (Cooperation::where('id',$_POST['id'])->delete())
            echo "刪除成功";
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(Cooperation::class, function (Form $form) {

            $form->display('id', 'ID');

            $form->display('created_at', 'Created At');
            $form->display('updated_at', 'Updated At');
        });
    }

    private function navbar(&$content)
    {
        $div = '<a href="'.url('admin/cooperation?status=0').'" class="btn btn-default">申請中</a>';
        $div .= '<a href="'.url('admin/cooperation?status=1').'" class="btn btn-default">已處理</a>';
        $content->row($div);
    }

    private function checkedTable($obj,&$box,$status)
    {
        $headers = ['姓名','聯繫電話','公司名稱','統一編號','營業類別','合作方向','申請時間', '操作'];

        $rows = array();
        foreach ($obj as $apply) {
            $row = [$apply->username,$apply->phone,$apply->company_name,$apply->company_tax_no,$apply->type_name,$apply->direction,$apply->created_at];
            if($status == 0){
                $handle = '<a href="javascript:handle('.intval($apply->id).')" title="已處理"><i class="fa fa-check"></i></a>';
            }else{
                $handle = '<a href="javascript:cooperation_delete('.intval($apply->id).')" title="刪除"><i class="fa fa-trash"></i></a>';

            }
            $row[] = $handle;
            array_push($rows,$row);
        }
        $box->content(new Table($headers,$rows));
    }
    private function writeToken(&$content)
    {
        $content->row('<input name="_token" type="hidden" value="'.csrf_token().'">');
    }
}
