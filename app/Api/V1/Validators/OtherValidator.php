<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/7/3
 * Time: 14:36
 */

namespace App\Api\V1\Validators;
use Prettus\Validator\LaravelValidator;
use Prettus\Validator\Contracts\ValidatorInterface;

class OtherValidator extends LaravelValidator
{
    protected $rules = [

        'storecreate' =>[
            'name' => 'required',
            'phone'=> 'required|max:50',
            'company_name'=> 'required|max:120',
            'company_tax_no'=> 'required|max:30',
            'type_name'=> 'required|max:50',
            'city'=> 'required',
            'address'=> 'required',
        ],
        'feedback' => [
            'store_id' => 'required',
            'content' => 'required',
        ],
        'cooperation' =>[
            'username' => 'required',
            'phone'=> 'required|max:50',
            'company_name'=> 'required|max:120',
            'company_tax_no'=> 'required|max:30',
            'type_name'=> 'required|max:50',
            'direction'=> 'required',
        ],
        'media' =>[
            'username' => 'required',
            'phone'=> 'required',
            'company_name'=> 'required',
            'report_content'=> 'required',
        ]

    ];

    protected $attributes = [
        'name' => '姓名',
        'username' => '姓名',
        'phone'=> '電話',
        'company_name'=> '公司名稱',
        'company_tax_no'=> '統一編號',
        'type_name'=> '營業類別',
        'direction'=> '合作方向',
        'content'=>'內容',
        'report_content'=>'報道內容',
        'city' => '城市'
    ];

}