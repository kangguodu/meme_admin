<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/8/8
 * Time: 17:50
 */

namespace App\Api\V1\Validators;
use Prettus\Validator\LaravelValidator;
use Prettus\Validator\Contracts\ValidatorInterface;

class CouponsValidator extends LaravelValidator
{
    protected $rules = [
        'release' => [
            'title' => 'required',
            'use_type' => 'required',
            'valid_time' => 'required',
            'conditions' => 'required',
            'type' => 'required',
            'number' => 'required',
            'limit_number' => 'required',
            'limit_receive_days' => 'required',
            'limit_receive' => 'required',
        ]
    ];

    protected $attributes = [
        'title' => '優惠券主題',
        'use_type' => '優惠券類型',
        'valid_time' => '有效時間',
        'conditions' => '滿減條件',
        'type' => '優惠券使用類型',
        'number' => '發行數量',
        'limit_receive' => '限制領取次數',
        'limit_receive_days' => '每日限制領取數',
        'limit_number' => '每日限制使用數目',
    ];

}