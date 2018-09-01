<?php

namespace App\Api\Merchant\Requests;

use Dingo\Api\Http\FormRequest;

class VerifyAccountRequest extends FormRequest
{
    public function authorize()
    {
        return false;
    }

    public function rules()
    {
        return [
            'phone'      => 'required'
        ];
    }
}