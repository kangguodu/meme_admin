<?php
namespace Tests\Feature;
use Tests\TestCase;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use JWTAuth;
use JWTFactory;
use Carbon\Carbon;

class BaseTest extends TestCase
{

    public function createStore(){
        $store = factory(\App\Store::class)->create([
            'id' => 9999,
            'super_uid' => 9999,
            'name' => '十四甲菜頭粿',
            'city' => '嘉義縣',
            'district' => '民雄鄉',
            'address' => '秀林村林子尾62號',
            'code' => 'AAA001'
        ]);
        return $store;
    }

    public function createWithPasswordUser(){
        $user = factory(\App\StoreUser::class)->create([
            'id' => 9999,
            'store_id' => 9999,
            'mobile' => '0925128798',
            'zone' => '886',
            'password' => '123456'
        ]);
        return $user;
    }

    public function createWithoutPasswordUser(){
        $user = factory(\App\StoreUser::class)->create([
            'id' => 9999,
            'store_id' => 9999,
            'mobile' => '0925128798',
            'zone' => '886',
        ]);
        return $user;
    }

    public function testCrosRequest(){
        $this->get('api/store/testcros')
            ->assertStatus(200);
    }

}