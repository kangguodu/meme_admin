<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;

class ActivityTableSeeder extends Seeder
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
        \DB::table('activity')->where('created_by','=','tester')->delete();
        $active = [
            [
                'title' => 'memecoins App发布上线啦',
                'content' => 'memecoins App发布上线啦',
                'description' => 'memecoins App发布上线啦',
                'type' => 1,
                'created_at' => time(),
                'created_by' => 'tester',
                'checked' => 1,
                'platform_type' => 1,
                'discount' => 0,
                'posters_pictures' => ''
            ],
            [
                'title' => 'memecoins App发布上线啦2',
                'content' => 'memecoins App发布上线啦2',
                'description' => 'memecoins App发布上线啦2',
                'type' => 1,
                'created_at' => time(),
                'created_by' => 'tester',
                'checked' => 1,
                'platform_type' => 1,
                'discount' => 0,
                'posters_pictures' => ''
            ],
            [
                'title' => 'memecoins App推荐教程',
                'content' => 'memecoins App推荐教程',
                'description' => 'memecoins App推荐教程',
                'type' => 1,
                'created_at' => time(),
                'created_by' => 'tester',
                'checked' => 1,
                'platform_type' => 2,
                'discount' => 0,
                'posters_pictures' => ''
            ],
            [
                'title' => 'memecoins App参加推荐活动教程',
                'content' => 'memecoins App参加推荐活动教程',
                'description' => 'memecoins App参加推荐活动教程',
                'type' => 1,
                'created_at' => time(),
                'created_by' => 'tester',
                'checked' => 1,
                'platform_type' => 2,
                'discount' => 0,
                'posters_pictures' => ''
            ],
            [
                'title' => 'memecoins Boss 新功能公告',
                'content' => 'memecoins Boss 新功能公告',
                'description' => 'memecoins Boss 新功能公告',
                'type' => 1,
                'created_at' => time(),
                'created_by' => 'tester',
                'checked' => 1,
                'platform_type' => 3,
                'discount' => 0,
                'posters_pictures' => ''
            ],
            [
                'title' => 'memecoins Boss 店家后台管理教学',
                'content' => 'memecoins Boss 店家后台管理教学',
                'description' => 'memecoins Boss 店家后台管理教学',
                'type' => 1,
                'created_at' => time(),
                'created_by' => 'tester',
                'checked' => 1,
                'platform_type' => 3,
                'discount' => 0,
                'posters_pictures' => ''
            ],
        ];
        foreach ($active as $value){
            \DB::table('activity')->insert($value);
        }
        Model::reguard();
    }
}
