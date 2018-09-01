<?php
namespace App\Api\Merchant\Services;


class AuthService
{
    /**
     * 修改個人資料時，可以修改的字段
     * @return array
     */
    public static function getUpdateProfileFields(){
        return [
            'nickname',
            'email',
            'gender'
        ];
    }

    public static function getUpdateProfileEmailStatus($user,$updateData){
        if(isset($updateData['email']) && $user->email != $updateData['email']){
            $updateData['email_status'] = 'unverified';
        }
        return $updateData;
    }

    /**
     * 獲取個人資料時，需要返回的字段
     * @return array
     */
    public static function getProfileFields(){
        return [
            'id',
            'nickname',
            'store_id',
            'password',
            'mobile',
            'email',
            'email_status',
            'permission',
            'token',
            'gender',
            'super_account'
        ];
    }

}