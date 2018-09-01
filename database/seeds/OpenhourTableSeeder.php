<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class OpenhourTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();
        \DB::table('open_hour_range')->where('store_id','=',9999)->delete();
        $openhoursData = array(
            array(
                'store_id' => 9999,
                'day_of_week' => 1,
                'open_time' => '09:00',
                'close_time' => '21:00'
            ),
            array(
                'store_id' => 9999,
                'day_of_week' => 2,
                'open_time' => '09:00',
                'close_time' => '21:00'
            ),
            array(
                'store_id' => 9999,
                'day_of_week' => 3,
                'open_time' => '09:00',
                'close_time' => '21:00'
            ),
            array(
                'store_id' => 9999,
                'day_of_week' => 4,
                'open_time' => '09:00',
                'close_time' => '21:00'
            ),
            array(
                'store_id' => 9999,
                'day_of_week' => 5,
                'open_time' => '09:00',
                'close_time' => '21:00'
            ),
            array(
                'store_id' => 9999,
                'day_of_week' => 6,
                'open_time' => '09:00',
                'close_time' => '21:00'
            ),
            array(
                'store_id' => 9999,
                'day_of_week' => 7,
                'open_time' => '09:00',
                'close_time' => '21:00'
            ),
        );

        foreach ($openhoursData as $value){
            \DB::table('open_hour_range')->insert($value);
        }

        Model::reguard();
    }
}
