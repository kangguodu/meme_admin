<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
class ImageSignSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Model::unguard();

        \DB::update("TRUNCATE TABLE image_sign");

        $data = array(
            array(
                'title' => '結帳立牌A4',
                'description' => '若店內有有提供座位,可放置於各個座位上，或是放置在櫃檯前，謝謝。',
                'cover' => 'download/example1.png',
                'image_config' => json_encode(
                    array (
                        'background' => '/upload/download/lipaiv2.png',
                        'qr_code_size' => 560,
                        'qr_code_position_x' => 284,
                        'qr_code_position_y' => 1502,
                        'qr_code_rotate' => 5,
                        'store_code_font_size' => 50,
                        'store_code_position_x' => 1260,
                        'store_code_position_y' => 1592,
                        'store_name_font_size' => 12,
                        'store_name_position_x' => 0,
                        'store_name_position_y' => 0,
                        'store_code_font' => '/upload/download/wryh.ttf',
                        'logo_path' => '/upload/download/logo.png',
                        'logo_size' => 200,
                    )
                ),
                'start_at' => '2018-07-01',
                'end_at' => '2099-12-31',
                'created_at' => date('Y-m-d H:i:s'),
                'price' => 100
            ),
            array(
                'title' => '結帳立牌A6',
                'description' => '若店內有有提供座位,可放置於各個座位上，或是放置在櫃檯前，謝謝。',
                'cover' => 'download/example1.png',
                'image_config' => json_encode(
                    array (
                        'background' => '/upload/download/qrcodev2.png',
                        'qr_code_size' => 503,
                        'qr_code_position_x' => 92,
                        'qr_code_position_y' => 943,
                        'qr_code_rotate' => 0,
                        'store_code_font_size' => 50,
                        'store_code_position_x' => 0,
                        'store_code_position_y' => 0,
                        'store_name_font_size' => 12,
                        'store_name_position_x' => 0,
                        'store_name_position_y' => 0,
                        'store_code_font' => '/upload/download/wryh.ttf',
                        'logo_path' => '/upload/download/logo.png',
                        'logo_size' => 200,
                    )
                ),
                'start_at' => '2018-07-01',
                'end_at' => '2099-12-31',
                'created_at' => date('Y-m-d H:i:s'),
                'price' => 100,
            )
        );
        foreach ($data as $value){
            \DB::table('image_sign')->insert($value);
        }
        Model::reguard();
    }
}
