<?php


namespace App\Api\Merchant\Services;


class OrderService
{
    public static function orderStatus(){
        return [
            '-1' => '已取消',
            '0'  => '未處理',
            '1' => '已處理',
            '2' => '已退貨'
        ];
    }

    public static function getOrderStatusText($status){
        $orderStatus  = self::orderStatus();
        return isset($orderStatus[$status])?$orderStatus[$status]:'';
    }

    public static function commentStatus(){
        return [
            '1' => '滿意',
            '2' => '普通',
            '3' => '不滿意'
        ];
    }

    public static function getCommentStatusText($level){
        $commentStatus  = self::commentStatus();
        return isset($commentStatus[$level])?$commentStatus[$level]:'';
    }
}