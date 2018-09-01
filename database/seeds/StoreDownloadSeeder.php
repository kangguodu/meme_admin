<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class StoreDownloadSeeder extends Seeder
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
        \DB::table('image_sign')->delete();
        $options = array(
            array(
                'title' => '店家專屬結帳立牌',
                'description' => '若店內有有提供座位,可放置於各個座位上，或是放置在櫃檯前，謝謝。',
                'cover' => 'download/example1.png',
                'image' => '',
                'start_at' => '2018-01-01',
                'end_at' => '2099-12-31',
                'created_at' => date('Y-m-d H:i:s')
            ),
        );

        foreach ($options as $value){
            \DB::table('image_sign')->insert($value);
        }
        Model::reguard();
    }
}
