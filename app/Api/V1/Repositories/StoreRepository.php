<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/19
 * Time: 15:16
 */

namespace App\Api\V1\Repositories;

use App\Store;
use App\Regions;
use App\Collection;
use App\StoreBanner;
use App\Hotword;
use App\Comment;
use App\Goods;
use App\Recommend;
use App\Api\V1\Services\BaseService;
class StoreRepository
{
    protected $model;
    public function __construct(Store $store)
    {
        $this->model = $store;
    }


    /**
     * 店鋪評論
     */
    private function getComment($store_id,$size){
        $data =(new \App\Comment())->join('member','member.id','=','member_id')
                ->where('store_id',$store_id)
                ->select([
                    'comments.id',
                    'comments.store_id',
                    'comments.content',
                    'comments.level',
                    'is_reply',
                    'reply_content',
                    'comments.nickname',
                    'comments.image',
                    'avatar',
                    'comments.created_at'
                ])
                ->orderBy('comments.level','DESC')
                ->limit($size)
                ->get();
        if(!empty($data)){
            $yesterday = strtotime(date("Y-m-d",strtotime("-1 day")));
            $two = strtotime(date("Y-m-d",strtotime("-2 day")));
            foreach ($data as $v){
                $v->time = '3天前';
                if (date('Y-m-d') == date('Y-m-d',$v->created_at)) {
                    $v->time = '今天';
                }
                if($v->created_at>=$yesterday && $v->created_at<$yesterday+24*3600-1){
                    $v->time = '1天前';
                }
                if($v->created_at>=$two && $v->created_at<$two+24*3600-1){
                    $v->time = '2天前';
                }
                $v->avatar = empty( $v->avatar)?url('/images/avatar/').'/avatar.png': BaseService::image($v->avatar);

            }
        }
        return $data;
    }

    /**
     * 店鋪輪播圖
     */
    private function getBanner($store_id){

        $data = StoreBanner::where('store_id',$store_id)->orderBy('rank','ASC')->get();
        $test =  [["id"=>$store_id,'image'=>BaseService::image('','banner'),'rank'=>0]];
        if(count($data)){
            foreach ($data as $v){
                $v->image = BaseService::image($v->image,'banner');
            }
        }else{
            $data = $test;
        }
        return $data;
    }

    //營業時間及營業狀態判斷
    private function getservicetime($store_id,$routine_holiday,$special_holiday,$special_business_day){
        $data = (new \App\OpenHourRange())->where('store_id',$store_id)->orderBy('day_of_week','DESC')->get();
        $data = collect($data)->groupBy('day_of_week')->toArray();
        $week = [1,2,3,4,5,6,7];
        $d = date('d');
        $date = date('Y-m-d');
        $status = 0;//休息
        $service = [];
        $totime = '';
        if($data){
            $i = 7;
            $w = date('w');
            $w = $w ? $w : 7;
            $time = date('H:i:s');
            foreach ($data as $k=>$v){
                if($v){
                    foreach ($v as $key=>$value){
                        if($k ==$w){
                            if($time>=$value['open_time'] && $time<=$value['close_time']){
                                $status = 1;
                            }
                        }
                        $v[$key]['open_time'] = date('H:i',strtotime($v[$key]['open_time']));
                        $v[$key]['close_time'] = date('H:i',strtotime($v[$key]['close_time']));
                        $v[$key]['time'] = $v[$key]['open_time'] . ' - ' .$v[$key]['close_time'];
                        $v[$key]['w'] = $v[$key]['day_of_week'];
                        $v[$key]['day_of_week'] = $this->getWeek($value['day_of_week']);
                        if($value['day_of_week'] == $w){
                            $totime .= date('H:i',strtotime($v[$key]['open_time'])).' - '.date('H:i',strtotime($v[$key]['close_time']));
                            if($key != count($v)-1){
                                $totime .= ',';
                            }

                        }
                    }
                }
                if($k != $i){
                    for($j=$i-$k;$j>0;$j--){
                        $display = [
                            'id'=>'',
                            'store_id'=>'',
                            'day_of_week'=>$this->getWeek($i),
                            'open_time' => '',
                            'close_time' => '',
                            'time'=> '休息中'
                        ];
                        array_unshift($service,[$display]);
                        $i--;
                    }



                }
                array_unshift($service,$v);
                $i--;
            }
        }else{
            $status = 3;//未开业
        }
        if(count($service) != 7){
            $a = count($service);
            for($j=7-$a;$j>0;$j--){
                $display = [
                    'id'=>'',
                    'store_id'=>'',
                    'day_of_week'=>$this->getWeek($j),
                    'open_time' => '',
                    'close_time' => '',
                    'time'=> '休息中'
                ];
                array_unshift($service,[$display]);
            }
        }
        if($routine_holiday == $d){
            $status = 0;//休息
        }

        if($special_business_day == $date){
            $status = 1;//营业中
            if(!$totime){
                $totime = '特別營業日';
            }
        }else if($special_holiday == $date){
            $status = 0;
            $totime = '特別休息日';
        }else if($routine_holiday == $d){
            $status = 0;//休息
            $totime = '例行休假日';
        }
        if(!$totime){
            $totime = '休息中';
        }
        $result['time'] = $totime;
        $result['data'] = $service;
        $result['service_status'] = $status;
        return $result;
    }

    /**
     * 獲取星期
     */
    private function getWeek($weekday){
        $week = [
            '1' => '星期一',
            '2' => '星期二',
            '3' => '星期三',
            '4' => '星期四',
            '5' => '星期五',
            '6' => '星期六',
            '7' => '星期日',
        ];
        foreach ($week as $k=>$v){
            if($k == $weekday){
                return $v;
            }
        }
        return '';

    }

    //點擊次數
    private function click($id){
        $data = (new \App\StoreData)->where('store_id',$id)->first();
        if($data){
            $data->click_number = $data->click_number + 1;
            $data->save();
        }else{
            (new \App\StoreData)->insertGetId(['store_id'=>$id,'click_number'=>1]);
        }
    }

    //回贈點數
    private function getprobability($store_id){
        $data = (new \App\StoreAccount())->where('store_id',$store_id)->first();

        return $data;
    }

    private function store_goods($store_id){
        $data = (new \App\Goods())->where('store_id',$store_id)->first([
            'id',
            'goods_name',
            'image',
            'price',
            'prom_price'
        ]);
        if($data){
            $data->image = BaseService::image($data->image);
        }
        return $data;
    }
    private function getDraft($lat,$lng,$size){
        $coordinate = BaseService::getLatAndLngRange($lat,$lng,$size);
        $model = $this->model;
        if($lat && $lng && $size){
            $model = $model->whereBetween('lat',[$coordinate['minLat'],$coordinate['maxLat']])
                ->whereBetween('lng',[$coordinate['minLng'],$coordinate['maxLng']]);
        }
        $model = $model->where('store.status',1);
        return $model->leftJoin('store_account','store_account.store_id','=','store.id')
            ->leftJoin('store_data','store.id','=','store_data.store_id')
            ->select([
            'store.id',
            'name',
            'branchname',
            'phone',
            'image',
            'type_name',
            'city',
            'district',
            'address',
            'is_return',
            'avg_cost_low',
            'avg_cost_high',
            'avg_cost_status',
            'service_status',
            'routine_holiday',
            'special_holiday',
            'special_business_day',
            'probability',
            'feature_probability',
            'feature_probability_time',
             'store_data.level',
            'store_data.number',
            'store_data.collect_number',
            'store_data.comment_number',
            'store_data.click_number',
             'store.recommend_rank',
             \DB::raw("acos(cos(" . $lat . "*pi()/180)*cos(lat*pi()/180)*cos(" . $lng . "*pi()/180-lng*pi()/180)+sin(" . $lat . "*pi()/180)*sin(lat * pi()/180)) * 6367000 AS distance")
        ]);
    }

    /**
     * @param $lat
     * @param $lng
     * @param $orderby
     * @param $desc
     * @param $size
     * @param $per_page
     * @鮮貨報馬仔，熱搜人氣，鄰近店家，響導嚴選，在地小吃，蜜蜜推薦
     */
    public function query($lat,$lng,$orderby,$desc,$size,$per_page,$type){
        $model = $this->getDraft($lat,$lng,$size);
        if($type==1){
             $model = $model->orderBy('recommend_rank','ASC');
         }
         $data = $model->orderBy($orderby,$desc)->paginate($per_page);

        if($orderby == 'comment_number'){
            if(count($data)){
                foreach ($data as $v){
                    $v->comments = $this->getComment($v->id,5);
                }
            }
        }
        if($type == 1){  //蜜蜜推薦
            if(count($data)){
                foreach ($data as $v){
                    $v->goods = $this->store_goods($v->id);
                }
            }
        }
        return $data;
    }



    /**
     * 搜索
     */
    public function search($hot_word,$per_page,$page=1,$member_id){
        $hot_word = trim($hot_word);
        $model = $this->model->leftJoin('store_data','store.id','=','store_data.store_id')
            ->leftJoin('store_account','store.id','=','store_account.store_id')
            ->where('store.status',1)
            ->where(function($query) use($hot_word){
                $query->where('name','like','%'.$hot_word.'%')
                    ->orWhere('search_keyword','like','%'.$hot_word.'%')
                    ->orWhere('type_name','like','%'.$hot_word.'%')
                    ->orWhere('city','like','%'.$hot_word.'%')
                    ->orWhere('district','like','%'.$hot_word.'%')
                    ->orWhere('address','like','%'.$hot_word.'%')
                    ->orWhere('service','like','%'.$hot_word.'%');
            })
            ->select([
                'store.id',
                'name',
                'branchname',
                'image',
                'type_name',
                'avg_cost_low',
                'avg_cost_high',
                'avg_cost_status',
                'phone',
                'city',
                'district',
                'address',
                'is_return',
                'service_status',
                'routine_holiday',
                'special_holiday',
                'special_business_day',
                'store_data.level',
                'store_data.number',
                'store_data.collect_number',
                'store_data.comment_number',
                'store_data.click_number',
                'store_data.level',
                'probability',
                'feature_probability',
                'feature_probability_time',
            ]);
        $data = $model->orderBy('created_at','DESC')->paginate($per_page);

        if($page==1 && count($data)){
            $this->hotword($hot_word);
            foreach ($data as $v){
                if($v->number || $v->number==0){
                    (new \App\StoreData())->where('store_id',$v->id)->increment('number',1);
                }else{
                    (new \App\StoreData())->insert(['store_id'=>$v->id,'number'=>1]);
                }
            }

        }


        return $data;

    }


    //熱搜词列表
    public function hot_word($member_id){
        $data['all']  = (new \App\Hotword())->orderBy('number','DESC')->limit(10)->get();
        if($member_id){
            $data['my'] = (new \App\MemberHotword())->join('keyword','hot_word_id','=','keyword.id')
                ->where('member_id',$member_id)
                ->select([
                    'hot_word_id',
                    'hot_word'
                ])
                ->orderBy('created_at','DESC')
                ->limit(10)
                ->get();
            return $data;
        }
        return $data['all'];

    }




    //店鋪詳情
    public function storeById($id,$lat,$lng){
        $data = $this->model->leftJoin('store_account','store_account.store_id','=','store.id')
            ->leftJoin('store_data','store.id','=','store_data.store_id')
            ->where('store.id',$id)->first([
            'store.id',
            'name',
            'city',
            'district',
            'address',
            'phone',
            'email',
            'type_name',
            'branchname',
            'remark',
            'avg_cost_low',
            'avg_cost_high',
            'avg_cost_status',
            'routine_holiday',
            'special_holiday',
            'special_business_day',
            'facebook',
            'instagram',
            'google_keyword',
            'store_data.level',
            'lat',
            'lng',
            'is_return',
            'probability',
            'feature_probability',
            'feature_probability_time',
             'service',
             'description',
              'can_order'
        ]);
        if($data){
            $data->google_keyword = $data->google_keyword ? 'https://www.google.com.tw/search?q='.$data->google_keyword : '';
            $data->distince = BaseService::getDistance($lat,$lng,$data->lat,$data->lng);
            $servicetime = $this->getservicetime($data->id,$data->routine_holiday,$data->special_holiday,$data->special_business_day);
            $data->service_time = $servicetime['data'];
            $data->service_status = $servicetime['service_status'];
            $data->time = $servicetime['time'];
            $data->routine_holiday = $data->routine_holiday ? $data->routine_holiday : 0;
            $data->special_holiday = ($data->special_holiday && $data->special_holiday != '0000-00-00') ? $data->special_holiday : '';
            $data->special_business_day = ($data->special_business_day && $data->special_business_day != '0000-00-00') ? $data->special_business_day : '';
            $data->banner = $this->getBanner($data->id);
            $data->probability = ($data->is_return) ? (($data->feature_probability_time<=time() && $data->feature_probability_time>0) ? $data->feature_probability : $data->probability) : 0;
            $data->service = json_decode($data->service);
            $data->level = $data->level>4.5 ? $data->level : 4.5;
            $data->comments = $this->getComment($id,3);
            unset($data->feature_probability_time);
            unset($data->feature_probability);
            $data->goods = $this->getGoods($id);
            $this->click($id);
        }
        return $data;
    }

    private function hotword($hotword){
        $hot_word = (new \App\Keyword())->where('hot_word',$hotword)->first();
        if($hot_word){
            $hot_word->number += 1;
            $hot_word->save();
        }else{
            (new \App\Keyword())->insert(['hot_word'=>$hotword,'number'=>1]);
        }
    }

    //是否收藏
    public function is_collect($id,$member_id){
        $count = Collection::where(['store_id'=>$id,'member_id'=>$member_id])->count();
        if($count){
            return true;
        }
        return false;
    }


    /**
     * 查看店家評論
     */
    public function getStoreComment($store_id,$per_page){
       return (new \App\Comment())->join('member','member.id','=','member_id')
            ->where('store_id',$store_id)
            ->select([
                'comments.id',
                'comments.store_id',
                'comments.content',
                'comments.level',
                'is_reply',
                'reply_content',
                'comments.nickname',
                'comments.image',
                'avatar',
                'comments.created_at'
            ])
           ->orderBy('comments.created_at','DESC')
            ->orderBy('comments.level','DESC')
            ->paginate($per_page);
    }

    /**
     * 查看店家菜單
     */
    private function getGoods($store_id){
        $data = Goods::where('store_id',$store_id)->limit(3)->get();
        if(!empty($data)){
            foreach ($data as $v){
                $v->image = BaseService::image($v->image);
            }
        }
        return $data;

    }

    /**
     * 地區
     */
    public function regions(){
        return (new \App\Regions())->orderBy('rank','DESC')->get();
    }


}
