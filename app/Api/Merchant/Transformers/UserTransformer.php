<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/27
 * Time: 15:47
 */

namespace App\Api\Merchant\Transformers;

use League\Fractal\TransformerAbstract;

class UserTransformer extends TransformerAbstract
{
    public function transform($object){
        $result = [
            'id'=>$object->id,
            'store_id'=>$object->store_id,
            'nickname' => $object->nickname,
            'mobile'=>$object->mobile,
            'email'=>$object->email,
            'emailStatus'=>$object->email_status?$object->email_status:"",
            'permission'=> $object->permission,
            'superAccount' => $object->super_account,
            'gender' => $object->gender,
            'position' => $object->position?$object->position:'',
            'menus' => $object->menus?$object->menus:array()
        ];
        //處理菜單 json
        if(!empty($result['menus']) && is_string($result['menus'])){
            $menus = json_decode($result['menus'],true);
            if($menus !== false && $menus != NULL){
                $result['menus'] = $menus;
            }else{
                $result['menus'] = array();
            }
        }

        if(isset($object->token)){
            $result['token'] = $object->token;
        }
        if(isset($object->store)){
            $result['store'] = $object->store;
        }
        return $result;
    }
}