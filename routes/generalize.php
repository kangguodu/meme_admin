<?php

// 接管路由
$api = app('Dingo\Api\Routing\Router');
// 配置api版本和路由
$api->version('v1', function ($api) {
    // 授权组'middleware'=>['jwt.generalize.auth']
    $api->group([
        'prefix' => 'generalize/auth',
        'middleware'=>[
            'cors'
        ],
        'namespace' => 'App\Api\Generalize\V1\Controllers'
    ], function ($api) {
        $api->post('test','TestController@test');

        $api->post('login','AuthController@login')->name('generalize.login');
        $api->get('logout','AuthController@logout')->name('generalize.logout');
        $api->post('register', 'AuthController@register')->name('generalize.register');
        $api->post('sms','AuthController@sms');
    });
    //較權組
    $api->group([
        "prefix"=>"generalize",
        "middleware"=>[
            'api.auth',
            'cors'
        ],
        'namespace' => 'App\Api\Generalize\V1\Controllers'
    ], function ($api) {
        $api->get('user/{handle}','User\UserController@get');                   //個人信息獲取
        $api->post('user/{handle}','User\UserController@post');                 //個人信息修改
        $api->get('bank/{handle}','User\BankController@get');                   //銀行信息獲取
        $api->post('bank/{handle}','User\BankController@post');                 //銀行信息修改
        $api->get('ranking/{handle}','Ranking\RankingController@get');          //網紅排行幫
        $api->get('log/{handle}','CreditsLog\DailyLogControllers@get');         //收入日志
        $api->get('withdraw/{handle}','Withdraw\WithdrawController@get');       //提現記錄
        $api->post('withdraw/{handle}','Withdraw\WithdrawController@post');     //提現申請
        $api->get('activity/{handle}','Activity\ActivityController@get');       //活動列表
        $api->get('credit/{handle}','Credit\CreditController@get');             //網紅錢包信息
    });
});