<?php

namespace App\Admin\Controllers;

use App\Admin\Provider\AdminHelpers;
use App\Admin\Repository\LiPaiHandle;
use App\Api\V1\Services\BaseService;
use App\Http\Controllers\Controller;
use App\ImageSignApply;
use App\ImageSignApplyDetail;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LiPaiApplyController extends Controller
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

    public function show($id)
    {
        Permission::check('lipai');
        return Admin::content(function ($content) use ($id){
            $content->header('立牌管理');
            $content->description(trans('lang.show'));
            $content->body(
                ImageSignApplyDetail::grid(function (Grid $grid) use($id){
                    $grid->model()->where('apply_id', $id);

                    $grid->apply_id("訂單ID");
                    $grid->apply()->store_id("店鋪ID");
                    $grid->image_sign_id("立牌ID");
                    $grid->imageSign()->title('立牌標題');
                    $grid->imageSign()->cover("封面圖片")->value(function ($cover){
                        $url = BaseService::image($cover);
                        return "<img src='{$url}' style='max-height: 40px;'>";
                    });
                    $grid->imageSign()->image_config("背景圖片")->value(function ($img){
                        $url = BaseService::image($img);
                        return "<img src='{$url}' style='max-height: 40px;'>";
                    });
                    $grid->count("數量");

                    $grid->actions(function (Grid\Displayers\Actions $actions) {
                        $id = $actions->getKey();
                        $url = admin_url("lipai_apply/{$id}/edit");
                        $actions->append("<a href='{$url}'><i class='fa fa-cloud-download'></i></a>");


                        $actions->disableDelete();
                        $actions->disableEdit();
                    });

                    $grid->disableCreation();
                    $grid->disableExport();
                    $grid->disableFilter();
                    $grid->disableBatchDeletion();
                    $grid->disableRowSelector();
                })
            );
        });
    }

    public function edit($id)
    {
        Permission::check('lipai');
        $applyDetail = ImageSignApplyDetail::findOrFail($id);
        $apply = ImageSignApply::findOrFail($applyDetail->apply_id);
        $storeId = $apply->store_id;
        session(['store_id' => $storeId]);
        $imageSignId = $applyDetail->image_sign_id;

        return (new StoreLiPaiController())->show($imageSignId);
    }

    public function destroy($id)
    {
        Permission::check('lipai');

        $ids = explode(',', $id);
        if (ImageSignApply::whereIn('id', $ids)->delete())
            return AdminHelpers::jsonResponse('刪除成功！');
        return AdminHelpers::jsonResponse('刪除失敗', false);
    }

    public function store(Request $request, LiPaiHandle $handle)
    {
        Permission::check('lipai');
        $rules = [
            'id' => 'required|integer|min:1',
            'handle' => 'required',
            'note' => 'nullable|max:200',
        ];
        $data = $request->only(['id', 'handle', 'note']);
        $validator = Validator::make($data, $rules);
        if ($validator->fails())
            return AdminHelpers::jsonResponse($validator->errors()->first(), false);
        return $handle->doHandle($data);
    }

    private function grid()
    {
        return ImageSignApply::grid(function (Grid $grid){
            $status = request('status')?request('status'):1;
            $grid->model()->where('status', $status)->orderBy('id','DESC');

            $grid->id('ID')->sortable();
            $grid->store_id('店鋪ID');
            $grid->other_remark('備註');
            $grid->status('處理狀態')->value(function ($status){
                $statusArr = [
                    1 => '待處理',
                    2 => '處理中',
                    3 => '完成',
                    4 => '取消',
                ];
                return isset($statusArr[$status])?$statusArr[$status]:'';
            });
            if (request('status') == 4)
                $grid->cancel_reason('取消原因');
            $grid->created_at('申請時間')->sortable();

            $grid->actions(function (Grid\Displayers\Actions $actions){
                $id = $actions->getKey();
                $url = admin_url("lipai_apply/{$id}");
                $actions->append("<a href='{$url}' title='訂單詳情'><i class='fa fa-list'></i> </a>");

                $actions->append((new LiPaiHandle())->handleBtn($id));

                $actions->disableEdit();
                $actions->disableDelete();
            });
            $grid->filter(function (Grid\Filter $filter){
                $filter->disableIdFilter();
                $filter->is('store_id', "店鋪ID");
                $filter->is('status', '狀態')->select([
                    1 => '待處理',
                    2 => '處理中',
                    3 => '完成',
                    4 => '取消',
                ]);
//                $filter->useModal();
            });
            $grid->disableExport();
            $grid->disableCreation();
        });
    }


}
