<?php
namespace App\Api\Merchant\Validators;

use Prettus\Validator\LaravelValidator;
use Prettus\Validator\Contracts\ValidatorInterface;

class AuthValidator  extends LaravelValidator
{
    protected $rules = [
        ValidatorInterface::RULE_CREATE => [
            'cat_name' => 'required'
        ],
        ValidatorInterface::RULE_UPDATE => [
            'id' => 'required',
            'cat_name' => 'required',
            'parent_id' => 'required'
        ],
        'init_verify_account' => [
            'phone' => 'required'
        ],
        'check_sms_code' => [
            'phone' => 'required',
            'code' => 'required'
        ],
        'init_password' => [
            'password' => 'required'
        ],
        'login' => [
            'account' => 'required',
            'password' => 'required'
        ],
        'forget' =>[
            'phone' => 'required',
            'code' => 'required',
            'password' => 'required|confirmed|min:6',
            'type' => 'required',
        ],
        'email'=>[
            'email' =>'required|email',
            'code' => 'required',
        ],
        'sms'=>[
            'phone' => 'required',
        ],
        'update_permission' => [
            'user_id' => 'required',
            'permission' => 'required',
        ]
    ];
}