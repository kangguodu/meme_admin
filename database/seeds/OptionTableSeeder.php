<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class OptionTableSeeder extends Seeder
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
        \DB::table('options')->delete();
        $options = array(
            array(
                'option_name' => 'platform_probability',
                'option_value' => 4,
                'is_autoload' => 1
            ),
            array(
                'option_name' => 'platform_income_percent',
                'option_value' => 50,
                'is_autoload' => 1
            ),
            array(
                'option_name' => 'generalize_income_percent',
                'option_value' => 50,
                'is_autoload' => 1
            ),

        );

        foreach ($options as $value){
            (new \App\Options())->insert($value);
        }

        Model::reguard();
    }
}
