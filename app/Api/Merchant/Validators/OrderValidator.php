<?php
namespace App\Api\Merchant\Validators;

use Prettus\Validator\LaravelValidator;
use Prettus\Validator\Contracts\ValidatorInterface;

class OrderValidator extends LaravelValidator
{
    protected $rules = [
        'reply_comment' => [
            'id' => 'required',
            'reply_content' => 'required'
        ],
    ];
}