<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/20
 * Time: 14:45
 */

namespace App\Api\V1\Validators;

use Prettus\Validator\LaravelValidator;
use Prettus\Validator\Contracts\ValidatorInterface;

class OrderValidator extends LaravelValidator
{
    protected $rules = [
        'order' => [
            'store_id'  => 'required',
            'amount'  => 'required',
            'credits' => 'required',
            'cash' => 'required',
        ],
        'refund' => [
            'order_id' => 'required',
        ],
        'comment' => [
            'order_id' => 'required',
            'level' => 'required',
        ]
    ];

    protected $attributes = [
        'order_id'=>'訂單id',
        'level' => '評分',
        'amount' => '消費總金額',
        'credits' => '抵現蜂幣',
        'cach'  => '現金'
    ];
}