<?php
namespace App\Api\Merchant\Transformers;

use League\Fractal\TransformerAbstract;

class StoreInfoTransformer  extends TransformerAbstract
{
    public function transform($object){
        $result = [
            'id'=>$object->id,
            'name'=>$object->name,
            'branchname' => $object->branchname,
            'city' => $object->city,
            'district' => $object->district,
            'address' => $object->address,
            'phone' => $object->phone,
            'email' => $object->email,
            'company_name' => $object->company_name,
            'company_tax_no' => $object->company_tax_no,
            'type_name' => $object->type_name,
            'avg_cost_status' => $object->avg_cost_status,
            'avg_cost_low' => $object->avg_cost_low,
            'avg_cost_high' => $object->avg_cost_high,
            'facebook' => $object->facebook,
            'instagram' => $object->instagram,
            'google_keyword' => $object->google_keyword,
            'code' => $object->code,
            'service' => $object->service,
            'description' => $object->description,
        ];
        //處理提供的服务 json
        if(!empty($result['service']) && is_string($result['service'])){
            $services = json_decode($result['service'],true);
            if($services !== false && $services != NULL){
                $result['service'] = $services;
            }else{
                $result['service'] = array();
            }
        }else{
            $result['service'] = array();
        }
        return $result;
    }
}