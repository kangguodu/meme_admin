<?php
namespace App\Api\V1\Services;


class BaseService
{
    public static function image($image,$type='logo'){
        if(!$image && $type=='logo'){
            return url('/upload').'/logo.png';  //店铺logo
        }
        if(!$image && $type=='banner'){
            return url('/upload').'/banner/banner.jpg';  //店铺banner
        }
        if(strstr($image,'http') || strstr($image,'https')){
            return $image;
        }else if(strstr($image,'upload')){
            return url('/').'/'.$image;
        }else{
            return url('/upload').'/'.$image;
        }
    }

    /**
     * 營業情況獲取
     */
    public static function getservicetime($store_id,$routine_holiday,$special_holiday,$special_business_day){
        $w = date('w');
        $d = date('d');
        $date = date('Y-m-d');
        $data = (new \App\OpenHourRange())->where('store_id',$store_id)
            ->where('day_of_week',$w)
            ->get();
        $result['status'] = 0;

        if(isset($data{0})){
            $result['time'] = '';
            foreach ($data as $k=>$v){
                $result['time'] .= date('H:i',strtotime($v->open_time)).' - '.date('H:i',strtotime($v->close_time));
                if($k != count($data)-1){
                    $result['time'] .= ',';
                }
                $result['status'] = 1;
            }
        }else{
            $result['time'] = '休息中';
            $result['status'] = 0;
        }

        if($routine_holiday == $d){
            $result['time'] = '休息中';
            $result['status'] = 0;
        }
        if($special_holiday == $date){
            $result['time'] = '休息中';
            $result['status'] = 0;
        }
        if($special_business_day == $date){
            if(count($data)){
                $result['time'] = '';
                foreach ($data as $v){
                    $result['time'] .= date('H:i',strtotime($v->open_time)).' - '.date('H:i',strtotime($v->close_time));
                    $result['status'] = 1;
                }
            }else{
                $result['time'] = '特別營業日';
                $result['status'] = 0;
            }

        }
        return $result;
    }

    //根據經緯度計算兩點距離
    public static function getDistance($lat1, $lng1, $lat2, $lng2){
        $earthRadius = 6367000; //approximate radius of earth in meters
        $lat1 = ($lat1 * pi() ) / 180;
        $lng1 = ($lng1 * pi() ) / 180;
        $lat2 = ($lat2 * pi() ) / 180;
        $lng2 = ($lng2 * pi() ) / 180;
        $calcLongitude = $lng2 - $lng1;
        $calcLatitude = $lat2 - $lat1;
        $stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);
        $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
        $calculatedDistance = $earthRadius * $stepTwo;
        return round($calculatedDistance);
    }

    //根據位置獲取周圍經緯度
    public static function getLatAndLngRange($Lat,$Lng,$size){

        $range = 180 / pi() * $size / 6367;  //周圍2千米
        $lngR = $range / cos($Lat * pi() / 180);


        $maxLat= $Lat + $range;//最大纬度
        $minLat= $Lat - $range;//最小纬度
        $maxLng = $Lng + $lngR;//最大经度
        $minLng = $Lng - $lngR;//最小经度

        $list = array('maxLat'=>$maxLat,'minLat'=>$minLat,'maxLng'=>$maxLng,'minLng'=>$minLng);
        return $list;
    }

    //會員推廣碼生成
    public static function inviteCode($member_id){
        $user = (new \App\User())->where('id',$member_id)->first();
        if (empty($user->invite_code)){
                \DB::beginTransaction();
                try{
                    $codeObj = (new \App\PromoCode())->where('used', 0)
                        ->orderBy(\DB::raw('RAND()'))
                        ->first();
                    $user->invite_code = $codeObj->code;
                    $user->save();
                    $codeObj->used = 1;
                    $codeObj->save();
                    \DB::commit();
                   return $user->invite_code;

                }catch (\Exception $e){
                    \DB::rollback();
                    \Log::error("invite create fail: ".$e->getMessage().', '.$e->getLine());

                }

        }
        return $user->invite_code;
    }

    /**
     * 優惠券領取記錄
     */
    public static function coupons_receive_log($coupons_id,$member_id){
        $data = [
            'coupons_id'  => $coupons_id,
            'member_id'   => $member_id,
            'receive_at' => date('Y-m-d H:i:s'),
            'receive_date' => date('Y-m-d'),
            'type' => 1
        ];
        (new \App\CouponsReceiveLog())->insert($data);

    }

    public static function operation($member_id,$coupons_id){

        \DB::beginTransaction();
        try{
            $list = (new \App\CouponsRelease())->where('id',$coupons_id)->first();
            if($list) {
                if ($list->number <= 0) {
                    return ['success'=>false,'msg'=>'優惠券已領取完'];
                }
                if($list->start_at > date('Y-m-d')){
                    return ['success'=>false,'msg'=>'領取優惠券活動尚未開始'];
                }
                if($list->expire_at < date('Y-m-d') && $list->expire_at && $list->expire_at!='0000-00-00'){
                    return ['success'=>false,'msg'=>'優惠券領取已結束'];
                }
                $data = [
                    'coupons_id'  => $coupons_id,
                    'member_id'   => $member_id,
                    'status' => 1,
                    'receive_at'=>time(),
                    'start_at' => strtotime(date('Y-m-d')),
                    'expire_time'=> strtotime(date('Y-m-d')) +  $list->valid_time*24*3600
                ];

                (new \App\Coupons())->insertGetId($data);
                $list->number -= 1;
                $list->save();
                self:: coupons_receive_log($coupons_id,$member_id);
                \DB::commit();
                return ['success'=>true,'msg'=>'領取成功'];
            }
            return ['success'=>false,'msg'=>'優惠券尚未發行'];


        }catch (\Exception $e){
            \DB::rollback();
            \Log::error("receive coupons fail by invite: ".$e->getMessage().', '.$e->getLine());
            return ['success'=>false,'msg'=>'系統出錯'];
        }
    }
}