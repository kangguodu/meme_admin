<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/25
 * Time: 10:45
 */

namespace App\Api\V1\Repositories;

use App\MemberCreditsLog;

class CreditsRepository
{

    public function Credit($member_id,$per_page,$type){
        $where = ['member_id'=>$member_id];
        if($type){
            $where['type'] = $type;
        }
        return MemberCreditsLog::where($where)
            ->where('amount','>',0)
            ->select([
                'id',
                'type',
                'trade_type',
                'date',
                'log_date',
                'amount',
            ])
            ->orderBy('log_date','DESC')
            ->paginate($per_page);
    }

    public function calculate($member_id){
        $rebate = MemberCreditsLog::where(['type'=>1,'member_id'=>$member_id])
            ->where(function($query){
                $query->where('trade_type','回饋')
                    ->orWhere('trade_type','rebate');
            })
            ->sum('amount');
        $acvitity = MemberCreditsLog::where(['type'=>1,'member_id'=>$member_id])
            ->where('trade_type','活動')
            ->sum('amount');
        $total =  MemberCreditsLog::where(['type'=>1,'member_id'=>$member_id])->sum('amount');
        if(!$total){
            return [];
        }
        $acvitity_credit = $acvitity / $total ;
        $rebate_credit = $rebate / $total ;
        return ['acvitity_credit'=>$acvitity_credit,'rebate_credit'=>$rebate_credit];
    }
}