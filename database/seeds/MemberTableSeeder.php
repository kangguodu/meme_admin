<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

use App\Api\Merchant\Services\MemberCreditsService;

class MemberTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        //初始化交易類型
        $memberInfo = (new \App\Member())->where('phone','=','099222222')->first();
        if($memberInfo){
            \DB::table('member')->where('id','=',$memberInfo->id)->delete();
            \DB::table('member_credits')->where('member_id','=',$memberInfo->id)->delete();
            \DB::table('orders')->where('member_id','=',$memberInfo->id)->delete();
            \DB::table('member_credits_log')->where('member_id','=',$memberInfo->id)->delete();
        }


        //初始化會員資料
        $member = array(
            'phone' => '099222222',
            'zone' => '886',
            'password' => \Hash::make('123456'),
            'username' => 'test',
            'nickname' => 'tester',
            'email' => 'test@test.com',
            'gender' => 1,
            'avatar' => '',
            'birthday' => '1970-01-01',
            'id_card' => '',
            'status' => 1,
            'user_type' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        );
        $member_id = (new \App\Member())->insertGetId($member);

        //初始化會員積分紅包
        $creditsData = array(
            'member_id' => $member_id,
            'total_credits' => 50,
            'grand_total_credits' => 0,
            'wait_total_credits' => 0,
            'freeze_credits' => 0,
            'promo_credits' => 0,
            'promo_credits_total' => 0
        );
        (new \App\MemberCredits())->insert($creditsData);

        //初始化積分會員充值記錄
        $creditsLog = array(
            'member_id' => $member_id,
            'type' => 1,
            'trade_type' => '活動',
            'log_date' => date('Y-m-d H:i:s'),
            'log_no' =>  MemberCreditsService::generateLogNo(),
            'amount' => 50,
            'balance' => 0,
            'status' => 1,
            'remark' => '活動 獲得 50 積分',
            'order_id' => 0,
            'order_sn' => ''
        );

        (new \App\MemberCreditsLog())->insert($creditsLog);
        //初始化會員測試訂單
        $testOrder = [
            'order_no' => uniqid().mt_rand(1,8),
            'order_sn' => 1,
            'date' => date('Y-m-d'),
            'store_id' => 9999,
            'store_name' => '十四甲菜頭粿',
            'member_id' => $member_id,
            'amount' => 500,
            'credits' => 5,
            'coupons_id' => 0,
            'coupons_money' => 0,
            'status' => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        (new \App\Order())->insert($testOrder);


        Model::reguard();
    }
}
