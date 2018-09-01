<?php


namespace App\Api\Merchant\Validators;
use Prettus\Validator\LaravelValidator;

class SyncOrderValidator extends LaravelValidator
{
    protected $rules = [
        'get_order' => [
            'date' => 'required',
        ],
        'sync_order' => [
            'order_id' => 'required',
            'cycle_status' => 'required',
            'cycle_days_remain' => 'required',
            'interest_ever' => 'required',
            'interest_remain' => 'required',
            'current_rebate' => 'required', //当前返利
        ]
    ];
}