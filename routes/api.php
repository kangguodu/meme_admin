<?php

use Illuminate\Http\Request;
//推廣API
//require app_path('Api/Generalize/api.php');
$api = app('Dingo\Api\Routing\Router');


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

//Route::middleware('auth:api')->get('/user', function (Request $request) {
//    return $request->user();
//});



$api->version('v1', function ($api){
//	$api->get('testcros', [
//        'middleware' => 'cors',
//        function () {
//            return array('error_code'=>0,'error_msg'=>'');
//        }
//    ]);

     
    $api->group([
        'middleware' => [
            'cors'
        ],
       'namespace' => 'App\Api\V1\Controllers',
    ], function ($api){
        $api->get('share', 'ToolsController@share');
        $api->get('version', 'ToolsController@version');
        $api->get('delete/image', 'ToolsController@deleteimage');

        $api->post('sms','ToolsController@sendSms');
        $api->post('upload','ToolsController@upload');
        $api->post('upload_base64','ToolsController@upload_base64');

        $api->post('user/signup','UserController@signUp');
        $api->post('user/fill','UserController@fillInfo');
        $api->post('user/login','UserController@login');
        $api->post('user/resetpassword','UserController@resetpassword');
        $api->post('user/changepassword','UserController@changepassword');
        $api->get('user/logout','UserController@logout');
        $api->post('checkCode','UserController@checkCode');
        $api->post('check/phone', 'UserController@checkphone');
        $api->post('vertify/phone', 'UserController@vertifyphone');


        $api->get('index','BannerController@index');

        $api->get('regions','StoreController@regions');

        $api->get('store/query','StoreController@query');
        $api->get('store/search','StoreController@search');
        $api->get('store/hot_word','StoreController@hot_word');
        $api->get('store/view','StoreController@view');
        $api->get('store/comment','StoreController@store_comments');


        $api->get('activity','ActivityController@index');
        $api->get('notice','NoticeController@index');
        $api->get('notice/view','NoticeController@view');

        $api->post('store/create', 'OtherController@storecreate');
        $api->post('store/feedback', 'OtherController@feedback');
        $api->post('cooperation', 'OtherController@cooperation');
        $api->post('media/contact', 'OtherController@mediacontact');
        $api->get('store/region', 'OtherController@getRegion');

        $api->get('getBotQA', 'ServiceController@getBotQA');

        $api->get('member/number', 'OtherController@regNum');

        $api->get('coupons/reg/receive','CouponsController@register_after_receive');
        $api->get('coupons/share/receive','CouponsController@share_after_receive');
        $api->post('coupons/release','CouponsController@release_coupons');

        $api->get('coupons/check','CouponsController@check');
        $api->get('receive','CouponsController@login_after_receive');
    });

    /**
     * 需要验证才能获取到资料的路由
     */
    $api->group([
        'middleware' => [
            'api.auth',
            'cors'
        ],
        'namespace' => 'App\Api\V1\Controllers',
    ], function ($api) {

        $api->get('tools/token_check', 'ToolsController@tokenCheck');

       $api->get('user/userinfo', 'UserController@userInfo');
        $api->post('user/update', 'UserController@updateInfo');
        $api->get('isSetPayPassword', 'UserController@isSetPayPassword');
        $api->post('verifyPayPassword', 'UserController@verifyPayPassword');
        $api->post('update/phone', 'UserController@updatePhone');
        $api->post('update/password', 'UserController@updatePayPassword');
        $api->get('user/qrcode', 'UserController@getQrCode');
       
        $api->get('user/bind', 'UserController@bindInvite');


        $api->post('collection/add','CollectionController@add');
        $api->get('collection/list','CollectionController@index');
        $api->get('is_collect','StoreController@is_collect');

        $api->post('order/create','OrderController@createOrder');
        $api->get('order/cancel','OrderController@cancel');
        $api->post('comment/add','OrderController@comment');
        $api->post('order/refund','OrderController@refund');
        $api->get('user/promo','UserController@view_promo');


        $api->get('order/list','OrderController@orderList');
        $api->get('order/store/details','OrderController@details');
        $api->get('order/view','OrderController@view');
        $api->get('order/check','OrderController@checkOrder');
        $api->get('order/rebate','OrderController@rebateOrder');

        $api->get('coupons/index','CouponsController@index');
//        $api->get('receive/coupons','CouponsController@change');  //暫時注釋掉不用
        $api->get('coupons/receive','CouponsController@receive');



        $api->get('credits/index','CreditsController@index');
        $api->get('credits/data','CreditsController@dataCount');
        $api->get('credits/usage','OrderController@usage');

        
        $api->get('notice/total','NoticeController@notice_total');
        $api->get('member/invite', 'OtherController@inviteNum');
    });

});