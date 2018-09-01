<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class RebateTestSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        $t1 = microtime(true);

        \DB::update("TRUNCATE TABLE orders");
        \DB::update("TRUNCATE TABLE rebate_orders");
        \DB::update("TRUNCATE TABLE member_credits");
        \DB::update("TRUNCATE TABLE member_credits_log");
        \DB::update("alter table orders AUTO_INCREMENT = 1");
        \DB::update("alter table rebate_orders AUTO_INCREMENT = 1");
        \DB::update("alter table member_credits AUTO_INCREMENT = 1");
        \DB::update("alter table member_credits_log AUTO_INCREMENT = 1");
        $length = 2000;
        for($i = 0;$i < $length;$i++){
            $member_rate = mt_rand(0.45,60);
            $member_id = mt_rand(1,200000);
            $order_id = ($length * 2) + $i;
            \DB::table('orders')->insert([
                'id' => $order_id,
                'order_no' => uniqid(),
                'order_sn' => ($i +1),
                'month' => date('Y-m'),
                'date' => date('Y-m-d'),
                'store_id' => 9999,
                'store_name' => '十四甲菜頭粿',
                'member_id' => $member_id,
                'amount' => 1000,
                'credits' => 0,
                'coupons_id' => 0,
                'prate' => 4,
                'mfixedrate' => 10,
                'mrate' => $member_rate,
                'promoreate' => 0,
                'status' => 1,
                'updated_by' => 9999,
                'checkout_user_id' => 9999,
                'checkout_at' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            //從當前時間開始計算開始時間和結束時間
            $start_date = Carbon::create(date('Y'),date('m'),date('d'),date('H'),date('i'),date('s'))->addDay(0)
                ->addMinute(1); //處理訂單之後，次日開始週期返利
            $end_date = Carbon::create(date('Y'),date('m'),date('d'),date('H'),date('i'),date('w'))
                ->addDay(0)->addMinute(45);
            $start_data = array(
                'order_id' => $order_id,
                'member_id' => $member_id,
                'cycle_point' => $member_rate,
                'cycle_start' => $start_date->toDateTimeString(),
                'cycle_end' => $end_date->toDateTimeString(),
                'cycle_days' => 10 ,
                'interest_remain' => $member_rate,
                'cycle_days_remain' => 10,
                'cycle_status' => 1, //待返利
            );

            \DB::table('rebate_orders')->insert($start_data);
            $credits = array(
                'member_id' => $member_id,
                'total_credits' => 0,
                'grand_total_credits' => 0,
                'wait_total_credits' => 0,
                'freeze_credits' => 0,
                'promo_credits' => 0,
                'promo_credits_total' => 0,
            );
            \DB::table('member_credits')->insert($credits);
        }
        $t2 = microtime(true);

        echo "运行了".round($t2-$t1,3)."秒\r\n";
        echo 'Now memory_get_usage: ' . memory_get_usage() . "\r\n";
        Model::reguard();
    }
}
