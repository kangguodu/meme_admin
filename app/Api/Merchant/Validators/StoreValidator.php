<?php
namespace App\Api\Merchant\Validators;

use Prettus\Validator\LaravelValidator;

class StoreValidator extends LaravelValidator
{
    protected $rules = [
        'add_store_user' => [
            'mobile' => 'unique:store_user',
            'menus' => 'required',
            'position' => 'required',
            'password' => 'required',
            'code' => 'required'
        ],
        'edit_store_user' => [
            'id' => 'required',
            'mobile' => 'nullable|unique:store_user',
            'menus' => 'required',
            'position' => 'required',
        ],
        'get_store_user' => [
            'id' => 'required',
        ],
        'remove_store_user' =>[
            'id' => 'required'
        ],
        'add_with_drawl' => [
            'amount' => 'required',
            'bank_id' => 'required',
            'type' => 'required',
        ],
        'cancel_with_drawl' =>[
            'id' => 'required'
        ],
        'add_bank' => [
            'bank_name' => 'required',
            'receiver_name' => 'required',
            'bank_account' => 'required',
            'bank_phone' => 'required',
        ],
        'update_logo' => [
            'logo' => 'required'
        ]
    ];

    protected $messages = [
        'mobile' => '手機號碼',
        'position.required'  => '職位不能爲空',
        'menus' => '權限設定',
        'code.required' => '請輸入驗證碼'
    ];
}