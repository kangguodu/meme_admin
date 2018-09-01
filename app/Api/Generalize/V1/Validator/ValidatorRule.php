<?php
/**
 * Created by PhpStorm.
 * User: zzq
 * Date: 18-7-2
 * Time: 上午11:26
 */

namespace App\Api\Generalize\V1\Validator;


class ValidatorRule
{
    static $withdrawApply = [
        'amount'=>'required|numeric|min:0.01',
        'bank_card_id'=>'required|integer',
    ];
}