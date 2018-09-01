<?php
namespace App\Api\V1\Validators;

use Prettus\Validator\LaravelValidator;
use Prettus\Validator\Contracts\ValidatorInterface;

class AuthValidator extends LaravelValidator
{
    protected $rules = [
        'login' => [
            'phone' => 'required',
            'password' => 'required'
        ],
       
        'sign_up' => [
            'phone' => 'required|unique:member,phone',
            'code' => 'required|min:6'
        ],
        'fill' =>[
            'phone' => 'required|unique:member,phone',
//            'gender' => 'required|integer',
            'password' => 'required|min:6',
        ],
        'check' =>[
            'phone' => 'required',
            'code' => 'required'
        ],
        'forget'=>[
            'password' => 'required|min:6',
        ],
        'paypassword'=>[
            'secure_password' => 'required',
        ],


    ];

    protected $attributes = [
        'phone' => '手機',
        'code' => '驗證碼',
        'secure_password' => '安全碼',
        'password' => '密碼',
        'gender' => '性別',
        'nickname' => '暱稱'
    ];

}