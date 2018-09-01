<?php
/**
 * Created by PhpStorm.
 * User: zzq
 * Date: 18-7-27
 * Time: 下午12:17
 */

namespace App\Admin\Repository;


use App\Activity;
use App\ActivityStore;
use App\Store;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class StoreHandle
{
    public function mimiUpdate(Request $request)
    {
        $rule = [
            'first' => 'nullable|integer',
            'second' => 'nullable|integer',
            'third' => 'nullable|integer',
        ];
        $validator = Validator::make($request->only(['first', 'second', 'third']), $rule);
        if ($validator->fails()){
            admin_toastr($validator->errors()->first(), 'error');
            return back();
        }
        if ($request->get('first'))
            $this->setMiMi($request->get('first'), 1);
        if ($request->get('second'))
            $this->setMiMi($request->get('second'), 2);
        if ($request->get('third'))
            $this->setMiMi($request->get('third'), 3);
        admin_toastr("已更改蜜蜜推薦排序");
        return redirect(admin_url("store"));
    }

    private function setMiMi($storeId, $rank)
    {
        $store = Store::find($storeId);
        if ($store){
            DB::transaction(function () use ($storeId, $rank){
                Store::where('recommend_rank', $rank)->update(['recommend_rank' => 99999999]);
                Store::where('id', $storeId)->update(['recommend_rank' => $rank]);
            });
        }
    }

    public function unreadActivity($storeId)
    {
        try{
            $actiIds = Activity::where('platform_type', 2)->get(['id']);
            $data = array();
            foreach ($actiIds as $actiId) {
                $arr = [
                    'activity_id' => $actiId,
                    'store_id' => $storeId,
                    'status' => 0,
                ];
                array_push($data, $arr);
            }
            ActivityStore::insert($data);
        }catch (\ErrorException $e){
            Log::debug($e->getCode().'=>'.$e->getMessage());
        }
    }
}