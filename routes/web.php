<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', [
    'uses' => '\App\Common\Controllers\BaseController@defaultRequest'
]);
//
//Route::post('/store/recharge-return', [
//    'uses' => '\App\Http\Controllers\Test\RechargeController@index'
//]);

