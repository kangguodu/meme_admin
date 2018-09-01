<?php
namespace App\Api\V1\Transformers;

use App\User;
use League\Fractal\TransformerAbstract;
use App\Collection;
use App\Api\V1\Services\BaseService;

class UserTransformer extends TransformerAbstract
{
    public function transform(User $object){
        $object->avatar = empty($object->avatar)?url('/images/avatar/').'/avatar.png': BaseService::image($object->avatar);
        $result = array(
            'id' => $object->id,
            'phone' => $object->phone,
            'zone' => $object->zone,
            'avatar' =>  $object->avatar,
            'status' => intval($object->status),
            'gender' => $object->gender,
            'email'=> $object->email,
            'username'=> $object->username,
            'nickname' => $object->nickname,
            'birthday' => ($object->birthday && $object->birthday !='0000-00-00') ? $object->birthday : '',
            'user_type' => $object->user_type,
            'secure_password_is_empty' => $object->secure_password ? 1 : 0,
            'total_credits'=>$object->total_credits,
            'grand_total_credits'=> $object->grand_total_credits,
            'wait_total_credits'=> $object->wait_total_credits,
            'promo_credits_total'=> $object->promo_credits_total,
            'promo_credits' => $object->promo_credits,
            'used_credits' => $this->usedCredits($object->id),
            'collection_total' => $this->collection_total($object->id),
            'honor' => $object->honor,
            'number'=> $object->number,
            'invite_code'=>$object->invite_code,
            'promo_code'=>$object->promo_code,
            'invite_number' => $object->invite_number,
            'invite_count' => $object->invite_count,
            'qrcode' => $object->qrcode,
            'my_promo' => $object->my_promo,
        );
        if(isset($object->token)){
            $result['token'] = $object->token;
        }
        
        return $result;
    }

    private function collection_total($member_id){
        return Collection::where('member_id',$member_id)->count();
    }

    private function usedCredits($id){
        return (new \App\Order())->where(['member_id'=>$id,'status'=>1])->where('credits','>',0)->sum('credits');
    }
}