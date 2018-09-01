<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/20
 * Time: 12:00
 */

namespace App\Api\V1\Repositories;

use App\Activity;

class ActivityRepository
{
    public function query($per_page){
        return Activity::where(['checked'=>1,'platform_type'=>1])
            ->selectRaw('activity.*,IF(expire_at >= ? and start_at <= ? and expire_at<=?,3,0) as status',[date('Y-m-d H:i:s'),date('Y-m-d H:i:s'),date('Y-m-d H:i:s',strtotime('3 days'))])
            ->where('show_time','<=',date('Y-m-d H:i:s'))
            ->where('expire_at','>=',date('Y-m-d H:i:s'))
            ->orderByRaw("status DESC")
            ->orderBy('start_at','ASC')
            ->orderBy('expire_at','DESC')
            ->paginate($per_page);
    }
}