<?php
namespace App\Api\Merchant\Services;


class StoreService
{
    public static function StoreAccountInitData($store_id){
        return [
            'store_id' => $store_id,
            'business_income' => 0,
            'credits_income' => 0,
            'return_credits' => 0,
            'probability' => 10,
            'fixed_probability' => 0,
            'feature_probability' => 10,
            'feature_probability_time' => 0
        ];
    }

    public static function editStoreInfoFields(){
        return array(
            array(
                'field' => 'avg_cost_status',
                'default' => 0
            ),
            array(
                'field' => 'avg_cost_low',
                'default' => 0
            ),
            array(
                'field' => 'avg_cost_high',
                'default' => 0
            ),
            array(
                'field' => 'facebook',
                'default' => ''
            ),
            array(
                'field' => 'instagram',
                'default' => ''
            ),
            array(
                'field' => 'google_keyword',
                'default' => ''
            ),
            array(
                'field' => 'email',
                'default' => ''
            ),
            array(
                'field' => 'description',
                'default' => ''
            )
        );
    }

}