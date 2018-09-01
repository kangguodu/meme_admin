<?php
/**
 * Created by PhpStorm.
 * User: zzq
 * Date: 18-7-9
 * Time: 上午10:56
 */

namespace App\Admin\Controllers;


use App\Admin\Provider\AdminHelpers;
use App\Admin\Provider\NavBar;
use App\Admin\Repository\StoreHandle;
use App\Api\V1\Controllers\ToolsController;
use App\Api\V1\Repositories\OtherRepository;
use App\Api\V1\Repositories\ToolsRepository;
use App\Http\Controllers\Controller;
use App\PromoCode;
use App\RegionsList;
use App\Store;
use App\StoreAccount;
use App\StoreBankAccount;
use App\StoreBanner;
use App\StoreData;
use App\StoreTrans;
use App\StoreType;
use App\StoreUser;
use Encore\Admin\Auth\Permission;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Widgets\Box;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

//use Encore\Admin\Widgets\Form;

class StoreController extends Controller
{
    use ModelForm;

    private $rule = [
        'company_name'=>'required|max:150',
        'company_tax_no'=>'required|max:20',
        'name'=>'required|max:150',
        'branchname'=>'max:50',
        'city'=>'max:50',
        'district'=>'max:50',
        'address'=>'required|max:50',
        'phone'=>'required|max:50',
        'type'=>'required',
        'mobile'=>'required|max:20|unique:store_user,mobile',
    ];

    public function index()
    {
        Permission::check('store');
        return Admin::content(function ($content){
            $content->header('店鋪列表');
            $content->description(trans('lang.list'));
            $content->body($this->grid());
        });
    }
    public function create(NavBar $bar)
    {
        Permission::check('store');
        return Admin::content(function ($content)use ($bar){
            $content->header('店铺入驻');
            $content->description(trans('lang.create'));
            $content->row(function ($row){
                $form = $this->form();
                $row->Column(12,$form);
            });
        });
    }
    public function show( $id ,Request $request)
    {
    	Permission::check('store');
    	if ($request->handle == 'charge'&&is_numeric($request->amount)){
    		$log = null;
		    $amount = round($request->amount,2);

		    $storeAccount = StoreAccount::where('store_id',$id)->first();
			if (!$storeAccount)
				return response()->json([
					'status' => false, 'message' => '店鋪不存在',
				]);
			DB::transaction(function () use ($storeAccount,$amount,&$log){
				$log = $this->chargeLog($storeAccount,$amount);

				$storeAccount->return_credits += $amount;
				$storeAccount->save();
			});
			if ($log){
				return response()->json([
					'status' => true, 'message' => '儲值成功',
				]);
			}else{
				return response()->json([
					'status' => false, 'message' => '儲值失敗，請稍後重新嘗試',
				]);
			}
	    }elseif($id == "mimi"){
            return Store::where('recommend_rank',"<=", "3")->pluck('name', 'recommend_rank');
        }else{
		    return response()->json([
			    'status' => false, 'message' => '操作有誤',
		    ]);
	    }
    }
    private function chargeLog($storeAccount,$amount)
    {
	    $data['store_id'] = $storeAccount->store_id;
	    $data['trans_type'] = 1;            //收入
	    $data['trans_category'] = 1;
	    $data['trans_category_name'] = '蜂幣回贈儲值金';
	    $data['trans_description'] = '紅包預存 '.$amount;
	    $data['trans_date'] = date('Y-m-d');
	    $data['trans_datetime'] = $data['created_at'] = date('Y-m-d H:i:s');
	    $data['amount'] = $amount;
	    $data['balance'] = $storeAccount->return_credits;
	    $data['created_by'] = Admin::user()->id;
	    $data['created_name'] = '系統';

	    return StoreTrans::create($data);
    }

	public function edit($id)
    {
        Permission::check('store');
        return Admin::content(function ($content) use ($id) {
            $content->header('店鋪管理');
            $content->description(trans('lang.edit'));
            $form = Admin::form(Store::class,function (Form $form){
                $form->method('UPDATE');
                $form->type('新增');
                $form->setAction(admin_url('store/update'));
                $form->display('id','ID');
                $form->hidden('id');
                $form->text('company_name','公司名稱');
                $form->text('company_tax_no','統一編號');
                $form->text('name','店铺名称');
                $form->text('branchname','分店名称')
                    ->help('该项可留空');
                $form->image('image','店家Logo');
                $form->text('city','市');
                $form->text('district','區');
                $form->text('address','詳細地址');
                $form->text('phone','電話');
                $form->select('type','業態類型')
                    ->options(StoreType::pluck('name', 'id'))
                    ->help("需到業態類型管理處預先設置！");
                $form->switch('avg_cost_status','平均消費狀態')->states([
                    'on'  => ['value' => 1, 'text' => '開啟'],
                    'off' => ['value' => 0, 'text' => '關閉']]);
                $form->text('avg_cost_low','最低消費')->rules("integer");
                $form->text('avg_cost_high','最高消費')->rules("integer");
                $form->text('facebook','facebook鏈接');
                $form->text('instagram','instagram鏈接');
                $form->text('google_keyword','谷歌搜索關鍵詞');
                $form->display('is_return','是否回贈');
                $form->display('recommend_rank','蜜蜜推薦位置') ->help("999999999為不在推薦位");
                $form->text('routine_holiday','例行休息日');
                $form->date('special_holiday','特休日');
                $form->date('special_business_day','特別營業日');
            })->edit($id);
            $content->row($form);
        });
    }
    public function store(Request $request)
    {
        Permission::check('store');
        $data = $request->only(['id','company_name','company_tax_no','name','branchname','city','district','address','phone','type','mobile']);
        $rule = $this->rule;
        $validator = Validator::make($data,$rule);
        if ($validator->fails()){
            admin_toastr($validator->errors()->first(),'error');
            return redirect()->back()->withInput($data);
        }
        if ($storeId = $this->storeTransaction($data)){
        	$request->id = $storeId;
        	//生成分享連接
        	$tool = new ToolsController(new ToolsRepository());
        	$tool->create($storeId);

        	//生成未讀消息
            (new StoreHandle())->unreadActivity($storeId);
            admin_toastr('入駐成功','success');
            return redirect()->action(
                '\App\Admin\Controllers\StoreController@edit',['id'=>$storeId]
            );
        }else{
            admin_toastr('入駐失敗，請檢查後重新嘗試','error');
            return redirect()->back()->withInput($data);
        }
    }
    public function update($id,Request $request)
    {
        Permission::check('store');
        $data = $request->all();
        $rule = $this->rule;
        unset($rule['mobile']);
        $validator = Validator::make($data,$rule);
        if ($validator->fails()){
            admin_toastr($validator->errors()->first(),'error');
            return redirect()->back()->withInput($data);
        }
        if ($store = Store::find($data['id'])){
            $data['type_name'] = $this->getStoreTypeNameById($data['type']);
	        $lg = new OtherRepository;
	        $result = $lg->getLatAndLng($data['city'].$data['district'].$data['address']);
	        $data['lat'] = $result['lat'];
	        $data['lng'] = $result['lng'];
	        $data['coordinate'] = implode(',',$result);
            $store->update($data);
            $tool = new ToolsController(new ToolsRepository());
            $tool->create($data['id']);
            admin_toastr('更新成功','success');
            return redirect()->action(
                '\App\Admin\Controllers\StoreController@index'
            );
        }else{
            admin_toastr('修改店鋪信息失敗！','error');
            return redirect()->back()->withInput($data);
        }
    }

    public function destroy($id)
    {
        Permission::check('store');
        if (\request('handle')=='is_return')
            return $this->changeStoreStatus($id);
        $ids = explode(',', $id);
        if (Store::whereIn('id',$ids)->delete()) {
            StoreUser::whereIn('store_id', $ids)->delete();
            StoreAccount::whereIn('store_id', $ids)->delete();
            StoreData::whereIn('store_id', $ids)->delete();
            StoreBanner::whereIn('store_id', $ids)->delete();
            StoreBankAccount::whereIn('store_id', $ids)->delete();
            return response()->json([
                'status'  => true,
                'message' => trans('lang.delete_succeeded'),
            ]);
        } else {
            return response()->json([
                'status'  => false,
                'message' => trans('lang.delete_failed'),
            ]);
        }
    }
    public function handle($handle, Request $request, StoreHandle $base)
    {
        Permission::check('store');

        switch ($handle) {
            case "mimi":
                return $base->mimiUpdate($request);
        }
    }
    public function getDistrict(Request $request)
    {
        $q = $request->get('q', null);
        $region = RegionsList::where('region_name', $q)->firstOrFail(['region_id']);
        return RegionsList::where('parent_id', $region->region_id)
            ->get([
                DB::raw('region_name as id'),
                DB::raw('region_name as text')
            ]);
    }
    public function form()
    {
        return Admin::form(Store::class,function (Form $form){
            $form->method('POST');
            $form->type('新增');
            $form->setAction(admin_url('store'));
            $form->display('id','ID');
            $form->text('company_name','公司名稱');
            $form->text('company_tax_no','統一編號');
            $form->text('name','店铺名称');
            $form->text('branchname','分店名称')
                ->help('该项可留空');
            $form->image('image','店家Logo');
            $form->select('city', '市')
                ->options(RegionsList::getCityOption())
                ->load('district', admin_url('store/district/get'));
            $form->select('district','區');
            $form->text('address','詳細地址');
            $form->text('phone','電話');
            $form->display('code','二維碼代碼');
            $form->select('type','業態類型')
                ->options(StoreType::pluck('name', 'id'))
                ->help("需到業態類型管理處預先設置！");
            $form->text('mobile','入駐擁有人號碼');
            $form->switch('avg_cost_status','平均消費狀態')->states([
                'on'  => ['value' => 1, 'text' => '開啟'],
                'off' => ['value' => 0, 'text' => '關閉']]);
            $form->text('avg_cost_low','最低消費')->rules("integer");
            $form->text('avg_cost_high','最高消費')->rules("integer");
            $form->text('facebook','facebook鏈接');
            $form->text('instagram','instagram鏈接');
            $form->text('google_keyword','谷歌搜索關鍵詞');
            $form->display('is_return','是否回贈');
            $form->display('recommend_rank','蜜蜜推薦位置') ->help("999999999為不在推薦位");
            $form->text('routine_holiday','例行休息日');
            $form->date('special_holiday','特休日');
            $form->date('special_business_day','特別營業日');
        });
    }
    private function changeStoreStatus($id)
    {
        $status = \request('status');
        if ($status==1||$status==0)
            Store::where('id', $id)->update(['is_return' => $status]);
        return AdminHelpers::jsonResponse('操作成功！');
    }
    private function grid()
    {
        return Store::grid(function (Grid $grid){
            $isReturn = (request('is_return') == null)?1:request('is_return');
            $grid->model()->where('is_return', $isReturn)->orderBy('id','DESC');

            $grid->id('ID')->sortable();
            $grid->super_uid('店鋪用戶ID')->value(function ($superUid){
                $url = admin_url("store_user/{$superUid}/edit");
                return "<a href='{$url}'>{$superUid}</a>";
            });
            $grid->name('店鋪名');
            $grid->branch_name(' ');
            $grid->phone('電話');
            $grid->type_name('業態');
            $grid->account()->return_credits('可回贈額度');
	        $grid->account()->business_income('營業收入');
            $grid->account()->credits_income('蜂幣余額');
	        $grid->created_at('入駐時間')->sortable();

            $grid->disableExport();
            $grid->disableBatchDeletion();
            $grid->disableRowSelector();
            $grid->tools(function (Grid\Tools $tools){
                $mimiBtn = "<a class='btn btn-sm btn-default' href='javascript:pushMiMi()'> 蜜蜜推荐</a>";
                $tools->append($mimiBtn);
            });
            $grid->actions(function ($actions)use ($isReturn){
            	$id = $actions->getkey();

                if ($isReturn){
                    //停权
                    $stopBtn = "<a href='javascript:handleStore({$id}, 0)' title='停權'><i class='fa fa-times-circle'></i> </a>";
                    $actions->prepend($stopBtn);
                }else{
                    //啟動
                    $startBtn = "<a href='javascript:handleStore({$id}, 1)' title='恢復經營'><i class='fa fa-check-circle'></i> </a>";
                    $actions->prepend($startBtn);
                }
            	//充值
            	$chargeBtn = "<a href='javascript:chargeStore({$id})' title='充值'><i class='fa fa-money'></i> </a>";
            	$actions->prepend($chargeBtn);
            	//立牌下載
                $lipaiUrl = admin_url("store_lipai_download?store_id={$id}");
                $lipaiBtn = "<a href='{$lipaiUrl}' title='立牌下載'><i class='fa fa-file-photo-o'></i> </a>";
                $actions->prepend($lipaiBtn);
                //銀行卡
                $bankCardUrl = admin_url('store_bank_account?store_id='.$id);
                $bankCard = "<a href='{$bankCardUrl}' title='銀行卡'><i class='fa fa-cc-visa'></i> </a>";
                $actions->prepend($bankCard);
                //推薦食品
                $foodUrl = admin_url("store_food_list?store_id={$id}");
                $foodList = "<a href='{$foodUrl}' title='推薦食品'><i class='fa fa-cutlery'></i> </a> ";
                $actions->prepend($foodList);
                //资料管理
                $storeInfoUrl = admin_url('store_info?store_id='.$id);
                $storeInfoAction = "<a href='{$storeInfoUrl}' title='資料設定'><i class='fa fa-folder'></i> </a> ";
                $actions->prepend($storeInfoAction);

            });
            $grid->filter(function (Grid\Filter $filter) {
                $filter->disableIdFilter();
                $filter->like('name','店鋪名稱');
//                $filter->like('company_name','公司名稱');
                $filter->is('type','業態')->select(StoreType::pluck('name', 'id'));
                $filter->is('is_return', '是否回贈')->select([
                    0 => '否',
                    1 => '是',
                ]);
                $filter->is('status', '店鋪狀態')->select([
                    0 => '停權',
                    1 => '使用',
                    -1=> '下架'
                ]);
                //$filter->useModal();
            });
        });
    }

    private function getStoreTypeNameById($id){
        $result = (new StoreType())->where('id','=',$id)
            ->first(['name']);
        if($result != null){
            return $result->name;
        }
        return '';
    }

    private function storeTransaction($data)
    {
        $transaction = false;
        $lg = new OtherRepository;
        $result = $lg->getLatAndLng($data['city'].$data['district'].$data['address']);
        isset($result['lat'])?$data['lat'] = $result['lat']:null;
        isset($result['lng'])?$data['lng'] = $result['lng']:null;
        $data['coordinate'] = implode(',',$result);
        DB::transaction(function ()use ($data,&$transaction){
            $codeObj = PromoCode::where('used', 0)
                ->orderBy(\DB::raw('RAND()'))
                ->first();
            $codeObj->used = 1;
            $codeObj->save();

            $storeData = $data;
            $storeData['created_at'] = date('Y-m-d H:i:s');
            $storeData['type_name'] = $this->getStoreTypeNameById($storeData['type']);
            unset($storeData['mobile']);
            $storeData['code'] = $codeObj->code;
            $store = Store::create($storeData);
            $user = StoreUser::create([
                'nickname' => '店主',
                'store_id' => $store->id,
                'mobile' => $data['mobile'],
                'permission' => 'ALL',
                'super_account' => 1,
                'menus' => json_encode(config('adminOption.store_menu')),
                ]);
            $store->super_uid = $user->id;
            $store->save();
            $transaction = $store->id;
            StoreAccount::create(['store_id'=>$store->id]);
        });

        return $transaction;
    }
}