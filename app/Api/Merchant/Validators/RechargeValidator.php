<?php
namespace App\Api\Merchant\Validators;

use Prettus\Validator\LaravelValidator;
use Prettus\Validator\Contracts\ValidatorInterface;

class RechargeValidator extends LaravelValidator
{
    protected $rules = [
        'apply_transfer' => [
            'accounts_no' => 'required',
            'amount' => 'required',
            'attachment' => 'required',
        ],
        'apply_transferby' => [
            'amount' => 'required',
            'type' => 'required',
        ],

    ];
}