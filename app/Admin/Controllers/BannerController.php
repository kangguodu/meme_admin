<?php

namespace App\Admin\Controllers;

use App\Banner;
use App\Http\Controllers\Controller;
use App\Store;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Widgets\Box;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BannerController extends Controller
{
    use ModelForm;
    private $bannerType;

    private $rule = [
        'type'=>'required',
    ];

    public function __construct(Request $request)
    {
        if ($bannerType = $request->get('banner_type')){
            $this->bannerType = $bannerType;
            session(['banner_type'=>$bannerType]);
        }elseif(session('banner_type')){
            $this->bannerType = session('banner_type');
        }else{
            $this->bannerType = 1;
            session(['banner_type'=>1]);
        }
    }

    public function index(Request $request)
    {
        Permission::check('banner');
        $this->bannerType = $request->get('banner_type',1);

        return Admin::content(function ($content){
            $content->header($this->getHeader());
            $content->description(trans('lang.list'));
            $content->body($this->grid());
        });
    }
    public function show($id)
    {
        if ($id=='store_list'){
            return Store::orderBy('id','DES')->paginate(null,['id','name as text']);
            $storeObj = Store::paginate(20,['id','name','branchname']);
            $arr = array();
            foreach ($storeObj as $store){
                array_push($arr,['id'=>$store->id,'text'=>$store->name.$store->branchname]);
            }
            return response()->json($arr);
        }
        return redirect()->action('\App\Admin\Controllers\BannerController@index');
    }
    public function create()
    {
        Permission::check('banner');
//        if (empty($request->get('type')))
//            return $this->selectType();
        return Admin::content(function ($content){
            $content->header('BANNER管理');
            $content->description(trans('lang.create'));
            $content->row($this->form());
        });
    }
    public function edit($id)
    {
        Permission::check('banner');
        return Admin::content(function ($content) use ($id){
            $content->header('BANNER管理');
            $content->description(trans('lang.edit'));
            $content->row($this->form()->edit($id));
        });
    }

//    public function edit($id)
//    {
//        Permission::check('banner');
//        return Admin::content(function ($content) use ($id) {
//            $content->header('BANNER管理');
//            $content->description(trans('lang.edit'));
//
//            $content->row(Banner::form(function ($form){
//                $form->method('UPDATE');
//                $form->display('id','ID');
//                $form->hidden('id','ID');
//                $form->hidden('use_type','use_type');
//                $form->select('type','類型')->options([
//                    0 => '選擇BANNER類型',
//                    1 => '活動',
//                    2 => '店鋪',
//                    3 => '宣傳',
//                    4 => 'App 內頁'
//                ])->rules('required|not_in:0');
//
//                $storeObj = Store::all(['id','name','branchname']);
//                $arr = array(
//                    '0' => '請選擇店鋪'
//                );
//                foreach ($storeObj as $store){
//                    $arr[$store->id] = $store->name;
//                    if(!empty($store->branchname)){
//                        $arr[$store->id] .= '('.$store->branchname.')';
//                    }
//                }
//                $form->select('store_id','店鋪ID')->options($arr)->help('類型選擇為店鋪時店鋪ID不能為空！');
//                $form->text('url','活動URL')->help('類型選擇為活動時活動URL不能為空！');
//                $form->text('app_page','App 內頁標識')->help('類型選擇為App 內頁時App 內頁標識不能為空！');
//                $form->image('image_url','BANNER圖')->removable();
//            })->edit($id));
//        });
//    }

    public function destroy($id)
    {
        Permission::check('banner');

        $ids = explode(',',$id);
        if (Banner::whereIn('id',$ids)->delete())
            return response()->json([
                'status'  => true,
                'message' => trans('admin::lang.delete_succeeded'),
            ]);
        return response()->json([
            'status'  => false,
            'message' => trans('admin::lang.delete_failed'),
        ]);
    }
    public function form()
    {

        return Banner::form(function ($form){
            $form->display('id','ID');
            $form->hidden('use_type')->default(1)->rules('required|integer');
//            $form->hidden('type')->default($type)->rules('required|integer');
//            $form->select('use_type','位置')->options([
//                0 => '選擇BANNER位置',
//                1 => '首頁大圖',
//                2 => '首頁小圖',
//            ])->rules('required|not_in:0');
            $form->select('type','類型')->options([
                0 => '選擇BANNER類型',
                1 => '活動',
                2 => '店鋪',
                3 => '宣傳',
                4 => 'App 內頁'
            ])->rules('required|not_in:0');
//            $form->text('store_id','店鋪ID')->rules('required_if:type,2');

            $storeObj = Store::all(['id','name','branchname']);
            $arr = array(
                '0' => '請選擇店鋪'
            );
            foreach ($storeObj as $store){
                $arr[$store->id] = $store->name;
                if(!empty($store->branchname)){
                    $arr[$store->id] .= '('.$store->branchname.')';
                }
            }
            $form->select('store_id','店鋪ID')->options($arr)->help('類型選擇為店鋪時店鋪ID不能為空！');
            $form->text('url','活動URL')->rules('nullable:required_if:type,1')->help('類型選擇為活動時活動URL不能為空！');
            $form->text('app_page','App 內頁標識')->value("")->rules('nullable:required_if:type,4')->help('類型選擇為App 內頁時App 內頁標識不能為空！');
            $form->image('image_url','BANNER圖')->uniqueName()->rules('required');
            $form->saving(function(Form $form){
                if($form->type==4){
                    $form->store_id = 0;
                    $form->url = '';
                }else{
                    $form->app_page = '';
                }
            });
        });
    }

//    public function update($id,Request $request)
//    {
//        Permission::check('banner');
//        return $this->form()->update($id);
//    }

    private function selectType()
    {
        return Admin::content(function ($content){
            $content->header('BANNER管理');
            $content->description(trans('lang.create'));

            $content->row(function ($row){
                $box = new Box();

                $textUrl = admin_url('banner/create?type=2');
                $htmlUrl = admin_url('banner/create?type=1');
                $btn = "<div class='col-md-6'style='margin-top: 200px;margin-bottom: 200px'><a href='{$textUrl}' class='pull-right btn btn-success btn-lg'>新建店鋪BANNER</a></div>";
                $btn .= "<div class='col-md-4'style='margin-top: 200px;margin-bottom: 200px'><a href='{$htmlUrl}' class='btn btn-success btn-lg text-center'>新建活動BANNER</a></div>";

                $box->title('選擇BANNER類型');
                $box->content($btn);
                $row->Column(12,$box);
            });
        });
    }
    private function grid()
    {
        return Banner::grid(function (Grid $grid){
            $bool = null;
            $grid->model()->where('use_type',$this->bannerType)->orderBy('id','DESC');

            $grid->id('ID')->sortable();
            $grid->image_url('BANNER')->value(function ($arg){
                $url = url((string)$arg);
                return "<a href='{$url}' target='_blank'><img src='{$url}' style='height: 40px;'/></a>";
            });
//            $grid->use_type('位置')->value(function ($type){
//               $arr = [
//                   1 => '首頁大圖',
//                   2 => '首頁小圖',
//               ];
//               return isset($arr[$type])?$arr[$type]:'錯誤類型';
//            });
            $grid->type('BANNER類型')->value(function ($type)use (&$bool){
                $bool = $type;
                $typeArr = [
                    1 => '活動',
                    2 => '店鋪',
                    3 => '宣傳',
                    4 => 'App 內頁'
                ];
                return isset($typeArr[$type])?$typeArr[$type]:'錯誤類型';
            });
            $grid->url('URL')->value(function ($url){
                if (empty($url)){
                    return '未設定';
                }
                return "<a href='{$url}' target='_blank'>{$url}</a>";
            });
            $grid->store_id('店鋪ID')->value(function ($id){
                if (empty($id))
                    return 'NULL';
                $url = admin_url("store/{$id}/edit");
                return "<a href='{$url}'>{$id}</a>";
            });

            $grid->filter(function ($filter){
                $filter->is('type','類型')->select([
                    1 => '活動',
                    2 => '店鋪',
                    3 => '宣傳',
                    4 => 'App 內頁'
                ]);
                $filter->usemodal();
            });

            $grid->disableExport();
            if ($this->bannerType==2){
                $grid->disableCreation();
                $grid->actions(function (Grid\Displayers\Actions $actions){
                    $actions->disableDelete();
                });
            }
//            $grid->disableFilter();
//            $grid->disableBatchDeletion();
//            $grid->disableRowSelector();
        });
    }
    private function getHeader()
    {
        return $this->bannerType == 1?'大BANNER管理':'小BANNER管理';
    }
}
