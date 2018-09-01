<?php

use Illuminate\Database\Seeder;

use App\StoreUser;
use App\Store;
use Illuminate\Database\Eloquent\Model;


class StoreUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        $store_id = 9999;
        $user_id = 9999;
        \DB::table('store_user')->where('id','=',$user_id)->delete();
        \DB::table('store')->where('id','=',$store_id)->delete();
        \DB::table('store_account')->where('store_id','=',$store_id)->delete();
        \DB::table('store_trans')->where('store_id','=',$store_id)->delete();
        //1. 初始化店鋪
        $store = [
            'id' => $store_id,
            'super_uid' => $user_id,
            'name' => '十四甲菜頭粿',
            'branchname' => '塞狗',
            'city' => '嘉義縣',
            'district' => '民雄鄉',
            'address' => '秀林村林子尾62號',
            'phone' => '0925128798',
            'email' => '',
            'company_name' => 'XXX股份有限公司',
            'company_tax_no' => '51318064',
            'type' => 1,
            'type_name' => '餐飲',
            'image' => 'upload/images/test.png',
            'service_status' => 1,
            'level' => 0,
            'avg_cost_status' => 0,
            'avg_cost_low' => 10,
            'avg_cost_high' => 100,
            'facebook' => '',
            'instagram' => '',
            'google_keyword' => '十四甲菜頭粿-塞狗',
            'is_return' => 1,
            'coordinate' => '23.4344730000,120.6242550000',
            'search_keyword' => '',
            'created_at' => date('Y-m-d H:i:s'),
            'code' => 'AAA001',
            'lat' => '23.4344730000',
            'lng' => '120.6242550000'
        ];
        (new Store())->insert($store);
        //2. 初始化店鋪用戶
        $storeUser = [
            'id' => $user_id,
           'store_id' => $store_id,
            'nickname' => 'Hello',
            'email' => '',
            'mobile' => '0925128798',
            'zone' => '886',
            'password' => '',
            'permission' => 'ALL',
            'email_status' => '',
            'gender' => 'male',
            'super_account' => 1
        ];
        $user_id = (new StoreUser())->insert($storeUser);
        //\DB::update("update store set super_uid=? where id=?",[$user_id,$store_id]);
        //3. 初始化 店家資金
        $storeAccount =  [
            'store_id' => $store_id,
            'business_income' => 0,
            'credits_income' => 0,
            'return_credits' => 15000,
            'probability' => 10,
            'fixed_probability' => 0,
            'feature_probability' => 10,
            'feature_probability_time' => 0,
            'feature_fixed_probability' => 0,
            'feature_fixed_probability_time' => 0,
        ];
        (new \App\StoreAccount())->insert($storeAccount);

        $storeTransInit = array(
            'store_id' => $store_id,
            'trans_type' => 1,
            'trans_category' => 1,
            'trans_category_name' => '積分回贈儲值金',
            'trans_description' => '紅包預存 15000',
            'trans_date' => date('Y-m-d'),
            'trans_datetime' => date('Y-m-d H:i:s'),
            'amount' => 15000,
            'balance' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $user_id,
            'created_name' => 'Hello',
            'custom_field1' => ''
        );
        (new \App\StoreTrans())->insert($storeTransInit);
        Model::reguard();
    }
}
