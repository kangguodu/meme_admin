<?php

use Illuminate\Routing\Router;

Admin::registerHelpersRoutes();

Route::group([
    'prefix'        => config('admin.prefix'),
    'namespace'     => Admin::controllerNamespace(),
    'middleware' => ['admin_lang', 'web'],
    ],function (Router $router)
{
    $router->get('auth/login', 'AuthController@getLogin');
    $router->post('auth/login', 'AuthController@postLogin');
    $router->get('authimg','AuthimgController@index');
});

Route::group([
    'prefix'        => config('admin.prefix'),
    'namespace'     => Admin::controllerNamespace(),
    'middleware'    => ['admin_lang', 'web', 'admin'],
], function (Router $router) {

    $router->get('/', 'HomeController@index');
    $router->get('api/store_types', 'StoreTypeController@getStoreTypes');


    //店铺入驻
    $router->resource('store','StoreController');
    //會員管理
    $router->resource('member','MemberController');
    //活动
    $router->resource('activity','ActivityController');
    //提現
    $router->resource('withdraw','WithdrawController');
    //立牌
    $router->resource('lipai','LipaiController');
    //立牌申請
    $router->resource('lipai_apply', 'LiPaiApplyController');
    //Banner
    $router->resource('banner','BannerController');
    //店鋪用戶
    $router->resource('store_user','StoreUserController');
    //熱搜詞
    $router->resource('hot_word','HotWordController');
    //消息通知推送
    $router->resource('notice','NoticeController');
    //会员推送
    $router->resource('member_notice', 'NoticeMemberController', ['only' => ['index','create','store']]);
    //店鋪推送
    $router->resource('merchant_notice', 'NoticeMerchantController');
    //網紅推送
    $router->resource('generalize_notice','NoticeGeneralizeController');
    //店鋪銀行卡管理
    $router->resource('store_bank_account','StoreBankAccountController');
    //店铺功能
    $router->any('store/handle/{handle}', 'StoreController@handle');
    //店鋪食品列表
    $router->resource('store_food_list', "StoreFoodListController");
    //店鋪立牌下載
    $router->resource('store_lipai_download', "StoreLiPaiController");
    //系统设置
    $router->resource('system_option', 'SystemOptionController');
    //商家入駐申請
    $router->resource('storeapply','StoreApplyController');
    $router->post('storeapply/handle','StoreApplyController@handle');
    $router->post('storeapply/delete','StoreApplyController@delete');
    //店铺充值申请
    $router->resource('store_transfer', 'StoreTransferController', ['only' => ["index", "update"]]);
    //我要合作
    $router->resource('cooperation','CooperationController');
    $router->post('cooperation/handle','CooperationController@handle');
    $router->post('cooperation/delete','CooperationController@delete');
    //店鋪業態
    $router->resource("store_type", "StoreTypeController", ["except" => "show"]);
    //市获取区县api
    $router->any('store/district/get', 'StoreController@getDistrict');
    //客服自动回复、关键字
    $router->resource('service_auto_reply', "ServiceAutoReplyController");
    $router->resource('service_keyword', "ServiceKeywordController", ["except" => "show"]);
    //测试
});
