<?php

use Illuminate\Database\Seeder;

use Illuminate\Database\Eloquent\Model;

class StoresSeeder extends Seeder
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
        \DB::update("TRUNCATE TABLE store_transcateogry");
        $transCategory = array(
            array(
                'id' => 1,
                'name' => '積分回贈儲值金',
                'description' => '紅包預存 %s'
            ),
            array(
                'id' => 2,
                'name' => '營業收入',
                'description' => '消費者使用紅包抵折'
            ),
            array(
                'id' => 3,
                'name' => '積分額度',
                'description' => '送積分'
            ),
            array(
                'id' => 4,
                'name' => '積分收入',
                'description' => '積分回贈'
            ),
            array(
                'id' => 5,
                'name' => '请款支出',
                'description' => '请款支出'
            ),
        );

        foreach ($transCategory as $value){
            (new \App\StoreTransCateogry())->insert($value);
        }

        Model::reguard();
    }
}
