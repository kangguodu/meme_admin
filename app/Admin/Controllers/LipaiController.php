<?php

namespace App\Admin\Controllers;

use App\Admin\Model\ImageSign;
use App\Http\Controllers\Controller;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Illuminate\Http\Request;
use App\Api\V1\Services\BaseService;

class LipaiController extends Controller
{
    use ModelForm;
    public function index()
    {
        Permission::check('lipai');
        return Admin::content(function ($content){
            $content->header('立牌管理');
            $content->description(trans('lang.list'));
            $content->body($this->grid());
        });
    }
    public function create()
    {
        Permission::check('lipai');
        return Admin::content(function ($content){
            $content->header('立牌管理');
            $content->description(trans('lang.create'));
            $content->body($this->form());
        });
    }
    public function edit($id)
    {
        Permission::check('lipai');

        return Admin::content(function ($content)use ($id){
            $content->header('立牌管理');
            $content->description(trans('lang.create'));
            $content->body($this->form()->edit($id));
        });
    }
    public function store(Request $request)
    {
        Permission::check('lipai');

        return $this->form()->store();
    }
    public function destroy($id)
    {
        Permission::check('lipai');

        $ids = explode(',', $id);
        if (ImageSign::whereIn('id',$ids)->delete())
            return response()->json([
                'status'  => true,
                'message' => trans('lang.delete_succeeded'),
            ]);
        return response()->json([
            'status'  => false,
            'message' => trans('lang.delete_failed'),
        ]);
    }
    public function form()
    {
        return ImageSign::form(function ($form){
            $form->display('id','ID');
            $form->text('title','標題')->rules('required|max:150');
            $form->text('description','描述')->rules('nullable|max:255');
            $form->date('start_at','開始日期')->rules('required|date');
            $form->date('end_at','結束日期')->rules('required|date');
            $form->image('cover','封面')->rules('required');
            $form->image('image_config','立牌圖片')->rules('required');
            $form->display('created_at','創建時間');
        });
    }
    private function grid()
    {
        return ImageSign::grid(function ($grid){
            $grid->model()->orderBy('id','DESC');

            $grid->id('ID')->sortable();
            $grid->cover('封面')->value(function ($cover){
                $url = $cover ? BaseService::image($cover) : url('/upload/download/').'/example1.png';
                return "<img src='{$url}' style='max-height: 40px;width: auto;'/>";
            });
            $grid->image_config('立牌圖片')->value(function ($cover){
                $url = url((string)$cover);
                return "<img src='{$url}' style='max-height: 40px;width: auto;'/>";
            });
            $grid->title('標題');
            $grid->description('描述');
            $grid->start_at('開始時間')->sortable();
            $grid->end_at('結束時間')->sortable();
//            $grid->created_at('創建時間');
            
            $grid->disableFilter();
            $grid->disableExport();
        });
    }
}
