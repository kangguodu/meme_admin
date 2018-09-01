<?php

namespace App\Api\Merchant\Validators;

use Prettus\Validator\LaravelValidator;
use Prettus\Validator\Contracts\ValidatorInterface;

class OtherValidator extends LaravelValidator
{
    protected $rules = [
        'create_image_apply' => [
            'items' => 'required',
        ],
        'edit_image_apply' => [
            'id' => 'required',
            'paster_count' => 'required',
            'dm' => 'required',
            'one_type_card' => 'required'
        ],
        'remove_image_apply' => [
            'id' => 'required'
        ]
    ];
}