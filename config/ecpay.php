<?php
/**
 *  绿界科技 对接Api
 * @link https://www.ecpay.com.tw/Content/files/ecpay_011.pdf
 */

return [
    'returnUrl' => 'https://office.techrare.com/memecoinsapi/public/store/recharge-return',
    'ClientBackURL' => 'https://office.techrare.com/memecoinsapi/public/store/recharge-back',
    'ecpay_host' => 'https://payment-stage.ecpay.com.tw', //sandbox
    'AioCheckout' => '/Cashier/AioCheckOut/V5',
    'MerchantID' => '2000132', //特 店 編 號
    'PlatformID' => '3002599', //平台商編號
    'HashKey' => '5294y06JbISpM5x9',
    'HashIV' => 'v77hoKGq4kWxNNIS',
];