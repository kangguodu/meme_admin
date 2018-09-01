<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

use JWTAuth;
use JWTFactory;
use Carbon\Carbon;

class LoginTest extends BaseTest
{
    use DatabaseMigrations; //每次测试后重置数据库

    public function testLoginValidationFail(){
        $this->post('api/store/login')
            ->assertStatus(422);
    }

    public function testLoginErrorAccount(){
        $this->createWithPasswordUser();
        $data = [
            'account' => '110',
            'password' => '666666',
            'type' => 'phone'
        ];
        $this->post('api/store/login',$data)
            ->assertStatus(400);
    }

    public function testLoginSuccess(){
        $this->createWithPasswordUser();
        $data = [
            'account' => '0925128798',
            'password' => '123456',
            'type' => 'phone'
        ];
        $this->post('api/store/login',$data)
            ->assertStatus(200);
    }
}