<?php
$api = app('Dingo\Api\Routing\Router');

$api->version('v1', function ($api){

    $api->get('store/testcros', [
        'middleware' => 'cors',
        function () {
            return array('error_code'=>0,'error_msg'=>'test cors success');
        }
    ]);

    $api->group([
        'middleware' => [
            'cors'
        ],
        'namespace' => 'App\Common\Controllers',
    ], function ($api){
        $api->post('common/sendsms','ToolsController@sendSms');

    });

    $api->group([
        'middleware' => [
            'cors'
        ],
        'namespace' => 'App\Api\Merchant\Controllers',
    ], function ($api){
        $api->get('store/test/store_msg','OtherController@testStoreMsg');
        $api->get('store/test/store_order_msg','OtherController@testStoreOrdermsg');

        $api->post('store/verify_account','AuthController@verifyAccount');
        $api->post('store/check_code','AuthController@checkVerificationCode');
        $api->post('store/init_password','AuthController@setInitPassword');
        $api->post('store/login','AuthController@login');
        $api->post('store/resetPassword','AuthController@resetPassword');
        $api->post('sendsms','AuthController@sendsms');

        $api->get('rebate/sync/orders','SyncOrderController@index');
        $api->post('rebate/sync/order','SyncOrderController@syncOrder');

    });

    /**
     * 需要验证才能获取到资料的路由
     */
    $api->group([
        'middleware' => [
            'jwt.api.auth',
            'cors'
        ],
        'namespace' => 'App\Api\Merchant\Controllers',
    ], function ($api) {
        $api->get('store/current/profile','AuthController@profile');
        $api->post('store/current/update_profile','AuthController@updateProfile');
        $api->post('store/current/update_password','AuthController@changePassword');
        $api->get('store/current/simple_user_list','AuthController@userSimpleList');
        $api->get('store/current/user_list','AuthController@userList');
        $api->get('store/current/get_open_hours','StoreController@getOpenHours');
        $api->post('store/current/update_open_hours','StoreController@saveOpenHours');
        $api->post('store/current/update_permission','AuthController@updatePermission');
        $api->post('store/current/add_store_user','StoreController@addStoreUser');
        $api->post('store/current/update_store_user','StoreController@updateStoreUser');
        $api->get('store/current/get_store_user','StoreController@getStoreUser');
        $api->post('store/current/remove_store_user','StoreController@removeStoreUser');
        $api->get('store/current/get_banner_list','StoreController@bannerList');
        $api->post('store/current/add_banner_list','StoreController@addBanner');
        $api->get('store/current/get_download_list','StoreController@downloadAreaList');
        $api->get('store/current/get_download_detail','StoreController@getDownloadDetail');
        $api->get('store/current/inviteNum','StoreController@inviteNum');

        $api->get('store/sign_apply/form','OtherController@getImageApplyForm');
        $api->get('store/sign_apply','OtherController@getImageSignApply');
        $api->get('store/sign_apply/count','OtherController@getImageSignApplyCount');
        $api->post('store/sign_apply/create','OtherController@addImageSignApply');

        $api->get('store/current/bank_list','StoreController@getBankList');
        $api->get('store/current/bank_count','StoreController@getBankCount');
        $api->post('store/current/add_bank','StoreController@addBankAccount');
        $api->post('store/current/delete_bank','StoreController@deleteBankAccount');

        $api->get('store/current/withdrawl_list','StoreController@getWithDrawlList');
        $api->get('store/current/withdrawl_count','StoreController@getWithDrawlCount');
        $api->post('store/current/add_withdrawl','StoreController@addWithDrawl');
        $api->post('store/current/cancel_withdrawl','StoreController@cancelWithDrawl');
        $api->post('store/current/update_logo','StoreController@editStoreLogo');
        $api->get('store/current/logo','StoreController@getStoreLogo');

        $api->get('company_bank','StoreController@CompanyBank');





        $api->post('store/updatePhone','AuthController@updatePhone');
        $api->post('store/updateEmail','AuthController@updateEmail');

        $api->get('store/account/balance','StoreController@getAmount');
        $api->get('store/account/income','StoreController@getInComeAmount');
        $api->get('store/account/recharge_history','StoreController@getAmountBills');
        $api->post('store/account/change_probability','StoreController@editProbability');
        $api->get('store/current/info','StoreController@index');
        $api->post('store/current/update_store','StoreController@saveStoreInfo');
        $api->get('store/current/store_simple_info','StoreController@storeSimpleInfo');

        $api->get('store/return/notice','StoreController@noneReturnCredits');
        $api->get('store/notice','StoreController@notice');
        $api->get('store/notice/view','StoreController@notice_view');


        $api->get('store/order/list','OrderController@index');
        $api->post('store/order/process','OrderController@editOrder');
        $api->get('store/order/history_list','OrderController@historyList');
        $api->get('store/order/comments','OrderController@commentList');
        $api->post('store/order/reply_comment','OrderController@replyComment');
        $api->get('store/account/bills','StoreController@getBills');

        $api->get('store/other/simple_activity','OtherController@simpleActivity');
        $api->get('store/other/activity_list','OtherController@ActivityList');
        $api->get('store/other/activity_detail','OtherController@activityDetail');
        $api->get('store/other/token_check', 'OtherController@tokenCheck');

        $api->get('store/recharge/create_test','RechargeController@createOrder');
        $api->get('store/recharge/transferes','RechargeController@transferList');
        $api->post('store/recharge/create_transfer','RechargeController@createTransfer');
        $api->post('store/recharge/create_transferby','RechargeController@createTransferBy');

    });

});