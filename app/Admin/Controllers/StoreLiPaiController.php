<?php

namespace App\Admin\Controllers;

use App\Admin\Model\ImageSign;
use App\Admin\Provider\AdminHelpers;
use App\Api\Merchant\Repositories\StoreRepository;
use App\Api\V1\Services\BaseService;
use App\Http\Controllers\Controller;
use App\Store;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Grid;

class StoreLiPaiController extends Controller
{
    use ModelForm;

    public function index()
    {
        Permission::check('store');

        return Admin::content(function ($content){
            $content->header($this->header());
            $content->description(trans('lang.list'));
            $content->body($this->grid());
        });
    }

    public function show($id)
    {
        Permission::check('store');

        $storeId = AdminHelpers::getArgumentFromGetOrSession('store_id');
        if (!$storeId)
            return back();
        $fileName = public_path('upload/image_sign/tv2'.md5($storeId.$id).'.png');
//        \Log::debug(" aa {$fileName}");
        if (!file_exists($fileName)){
            (new StoreRepository())->getDownloadAreaDetail($storeId, $id);
        }

        return response()->download($fileName);

    }

    private function grid()
    {
        return ImageSign::grid(function (Grid $grid){
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
            $grid->actions(function (Grid\Displayers\Actions $actions){
                $id = $actions->getKey();

                $actions->disableDelete();
                $actions->disableEdit();

                $downUrl = admin_url("store_lipai_download/{$id}");
                $download = "<a href='{$downUrl}'><i class='fa fa-cloud-download'></i></a>";
                $actions->prepend($download);
            });

            $grid->disableCreation();
            $grid->disableFilter();
            $grid->disableExport();
        });
    }

    private function header()
    {
        $storeId = AdminHelpers::getArgumentFromGetOrSession('store_id');
        return Store::where('id', $storeId)->first(['name'])->name;
//         $store->name."(<small>{$store->branchname}</small>)";
    }
}
