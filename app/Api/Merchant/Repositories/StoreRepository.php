<?php
namespace App\Api\Merchant\Repositories;

use App\Api\Merchant\Services\ImageToolsService;
use App\Api\Merchant\Services\StoreService;
use App\ImageSign;
use App\OpenHourRange;
use App\Store;
use App\StoreAccount;
use App\StoreBankAccount;
use App\StoreBanner;
use App\StoreTrans;
use App\StoreUser;
use App\Verification;
use App\Withdrawl;
use Carbon\Carbon;
use Endroid\QrCode\QrCode;
use Illuminate\Support\Facades\Cache;

class StoreRepository
{

    public function getStoreInfoById($id){
        return (new Store())->where('id','=',$id)
            ->first([
                'id',
                'name',
                'branchname',
                'city',
                'district',
                'address',
                'phone',
                'email',
                'company_name',
                'company_tax_no',
                'type_name',
                'avg_cost_status',
                'avg_cost_low',
                'avg_cost_high',
                'facebook',
                'instagram',
                'google_keyword',
                'code',
                'service',
                'search_keyword',
                'description'
            ]);
    }

    public function getStoreSimpleInfo($store_id){
       $store = (new Store())->leftJoin('store_user','store_user.id','=','store.super_uid')
            ->where('store.id','=',$store_id)
            ->first([
                'store.id',
                'store.name',
                'store.branchname',
                'store.phone',
                'store.email',
                'store.email_valid',
                'store_user.nickname',
                'store_user.gender',
                'store.service',
                'store.description'
            ]);
       if($store){
           if(!empty($store->service) && is_string($store->service)){
               $services = json_decode($store->service);
               if($services !== false && $services != NULL){
                   $store->service = $services;
               }else{
                   $store->service= array();
               }
           }else{
               $store->service= array();
           }
       }
       return $store;
    }


    protected function saveStoreInfoFilter($param){
        $params = StoreService::editStoreInfoFields();
        foreach($params as $value){
            $field = $value['field'];
            $param[$field] = isset($param[$field])?$param[$field]:$value['default'];
        }
        if(intval($param['avg_cost_status']) <= 0){
            $param['avg_cost_low'] = 0;
            $param['avg_cost_high'] = 0;
        }
        return $param;
    }

    public function saveStoreInfo($store_id,$param){
        $store = $this->getStoreInfoById($store_id);
        $email = $store->email;
        $param = $this->saveStoreInfoFilter($param);
        $store->avg_cost_status = intval($param['avg_cost_status']);
        $store->avg_cost_low = $param['avg_cost_low'];
        $store->avg_cost_high = $param['avg_cost_high'];
        $store->facebook = $param['facebook'];
        $store->instagram = $param['instagram'];
        $store->google_keyword = $param['google_keyword'];
        $store->description = $param['description'];
        $store->email = $param['email'];
        $store->service = isset($param['service'])?$param['service']:array();
        if(is_array($store->service)){
            $store->service = json_encode($store->service);
        }
        if($email != $param['email']){
            $store->email_valid = 'unverified';
        }
        $store->save();
    }


    protected function getAccountAmountById($store_id){
        return  (new StoreAccount())->where('store_id','=',$store_id)
            ->first([
                'store_id',
                'business_income',
                'credits_income',
                'return_credits',
                'probability',
                'fixed_probability',
                'feature_probability',
                'feature_probability_time',
                'feature_fixed_probability',
                'feature_fixed_probability_time'
            ]);
    }

    public function getAccountAmount($store_id){
        $result = $this->getAccountAmountById($store_id);
        if($result == null){
            (new StoreAccount())->insert(StoreService::StoreAccountInitData($store_id));
            $result = $this->getAccountAmountById($store_id);
        }
        return $result;
    }




    public function getAmountBills($store_id,$per_page){
        return (new StoreTrans())->where('store_id',$store_id)
            ->where('trans_category','=',1)
            ->select([
                'id',
                'trans_type',
                'trans_category',
                'trans_category_name',
                'trans_description',
                'trans_datetime',
                'amount'
            ])->orderBy('id','DESC')->paginate($per_page);
    }

    protected function getStoreProbability($store_id){
        return (new StoreAccount())->where('store_id','=',$store_id)
            ->first([
                'id',
                'probability',
                'fixed_probability',
                'feature_probability',
                'feature_probability_time',
                'feature_fixed_probability',
                'feature_fixed_probability_time'
            ]);
    }

    public function storeProbability($store_id,$probability,$fixed_probability){
        $storeAccountInfo = $this->getStoreProbability($store_id);
        if($storeAccountInfo == null){
            \Log::info("i'm here {$probability} {$fixed_probability}");
            return true;
        }
        $updateData = array();
        if($probability != null){
            if(intval($storeAccountInfo->feature_probability) != intval($probability)){
                $time = Carbon::create(date('Y'),date('m'),date('d'),1,0,0)
                    ->addDay(1)
                    ->getTimestamp();
                $updateData['feature_probability'] = $probability;
                $updateData['feature_probability_time'] = $time;
            }
        }

        if($fixed_probability != null){
            if(intval($storeAccountInfo->feature_fixed_probability) != intval($fixed_probability)){
                $time = Carbon::create(date('Y'),date('m'),date('d'),1,0,0)
                    ->addDay(1)
                    ->getTimestamp();
                $updateData['feature_fixed_probability'] = $fixed_probability;
                $updateData['feature_fixed_probability_time'] = $time;
            }
        }
        if(count($updateData) > 0){
            (new StoreAccount())->where('id',$storeAccountInfo->id)->update($updateData);
        }
        return true;
    }

    public function getBills($store_id,$category,$per_page){
        $query = (new StoreTrans())->where('store_id','=',$store_id)
            ->where('trans_category','<>',1);
        if($category >0 && $category != 1){
            $query->where('trans_category','=',$category);
        }
        return $query->select([
                'id',
                'trans_type',
                'trans_category',
                'trans_category_name',
                'trans_description',
                'trans_date',
                'trans_datetime',
                'amount',
                'balance',
                'created_at',
                'created_by',
                'created_name',
            ])
            ->orderBy('id','DESC')
            ->paginate($per_page);
    }

    private function openHoursDateToEmpty($data){
        if(empty($data)){
            return '';
        }else if($data == '0000-00-00'){
            return '';
        }else{
            return $data;
        }
    }

    public function getStoreOpenHours($store_id){
        $result = (new Store())->leftJoin('open_hour_range','store.id','=','open_hour_range.store_id')
            ->where('store.id','=',$store_id)
            ->select([
                'store.routine_holiday',
                'store.special_holiday',
                'store.special_business_day',
                'store.remark',
                'open_hour_range.id',
                'open_hour_range.day_of_week',
                'open_hour_range.open_time',
                'open_hour_range.close_time'
            ])->orderBy('open_hour_range.day_of_week','ASC')->get();
        if($result->isNotEmpty()){
            $firstResult = $result[0];
            $openHours = array(
                'routine_holiday' => $firstResult->routine_holiday,
                'special_holiday' => $this->openHoursDateToEmpty($firstResult->special_holiday),
                'special_business_day' => $this->openHoursDateToEmpty($firstResult->special_business_day),
                'remark' => $firstResult->remark,
                'open_hours' => array(

                )
            );
            $openHoursData = array(
                '1' => array(
                    'label' => '(一)',
                    'value' => false,
                    'day_of_week' => 1,
                    'time' => array()
                ),
                '2' => array(
                    'label' => '(二)',
                    'value' => false,
                    'day_of_week' => 2,
                    'time' => array()
                ),
                '3' => array(
                    'label' => '(三)',
                    'value' => false,
                    'day_of_week' => 3,
                    'time' => array()
                ),
                '4' => array(
                    'label' => '(四)',
                    'value' => false,
                    'day_of_week' => 4,
                    'time' => array()
                ),
                '5' => array(
                    'label' => '(五)',
                    'value' => false,
                    'day_of_week' => 5,
                    'time' => array()
                ),
                '6' => array(
                    'label' => '(六)',
                    'value' => false,
                    'day_of_week' => 6,
                    'time' => array()
                ),
                '7' => array(
                    'label' => '(日)',
                    'value' => false,
                    'day_of_week' => 7,
                    'time' => array()
                ),
            );
            foreach ($result as $key=>$value){
                $day_of_week = intval($value->day_of_week);
                if(array_key_exists($day_of_week,$openHoursData)){
                    $tempData = array(
                        'open_time' => date('H:i',strtotime(date("Y-m-d {$value->open_time}"))),
                        'close_time' => date('H:i',strtotime(date("Y-m-d {$value->close_time}")))
                    );
                    if(count($openHoursData[$day_of_week]) <= 0){
                        $openHoursData[$day_of_week] = array(
                            'id' => $value->id,
                            'day_of_week' => $value->day_of_week,
                            'time' => array()
                        );
                    }
                    $openHoursData[$day_of_week]['day_of_week'] = $value->day_of_week;
                    $openHoursData[$day_of_week]['value'] = true;
                    $openHoursData[$day_of_week]['time'][] = $tempData;
                }
            }
            $openHours['open_hours'] = array_merge($openHoursData,array());
        }else{
            $openHours = array(
                'routine_holiday' => 0,
                'special_holiday' => '',
                'special_business_day' => '',
                'remark' => '',
                'open_hours' => array()
            );
        }
        return $openHours;
    }

    private function getStoreOpenHoursData($store_id){
        return (new Store())->where('id','=',$store_id)
            ->first([
                'id',
                'routine_holiday',
                'special_holiday',
                'special_business_day',
                'remark',
            ]);
    }



    private function updateOpenHourFilter($store_id,$params){
        $result = array(
            'routine_holiday' => isset($params['routine_holiday'])?$params['routine_holiday']:0,
            'special_holiday' => isset($params['special_holiday'])?$params['special_holiday']:'',
            'special_business_day' => isset($params['special_business_day'])?$params['special_business_day']:'',
            'remark' => isset($params['remark'])?$params['remark']:'',
        );
        if(empty($result['routine_holiday'])){
            $result['routine_holiday'] = 0;
        }
        if(empty($result['special_holiday'])){
            $result['special_holiday'] = '';
        }
        if(empty($result['special_business_day'])){
            $result['special_business_day'] = '';
        }
        $openHours = isset($params['open_hours'])?$params['open_hours']:array();

        if(empty($openHours)){
            $result['open_hours'] = array();
        }else if(count($openHours) > 0){
            $openHoursIndex = array(
                '1' ,
                '2' ,
                '3' ,
                '4' ,
                '5' ,
                '6' ,
                '7' ,
            );
            $openHoursData = array();
            $openHoursParamCount = count($openHours);
            foreach ($openHours as $key=>$value){
                $day_of_week = isset($value['day_of_week'])?$value['day_of_week']:0;
                if($day_of_week <= 0 && $openHoursParamCount == 7){
                    $day_of_week = $key + 1;
                }
                $time = isset($value['time'])?$value['time']:array();
                if(count($time) > 0){
                    foreach ($time as $timeValue){
                        if(in_array($day_of_week,$openHoursIndex)){
                            $tempData = array(
                                'store_id' => $store_id,
                                'day_of_week' => $day_of_week,
                                'open_time' => $timeValue['open_time'].':00',
                                'close_time' => $timeValue['close_time'].':00',
                            );
                            $openHoursData[] = $tempData;
                        }
                    }
                }
            }

            $result['open_hours'] = $openHoursData;
        }

        return $result;
    }
    public function updateOpenHours($store_id,$params){
        $storeInfo = $this->getStoreOpenHoursData($store_id);
        $paramData = $this->updateOpenHourFilter($store_id,$params);
        if($storeInfo != null){
            $storeInfo->routine_holiday = $paramData['routine_holiday'];
            $storeInfo->special_holiday = empty($paramData['special_holiday'])?'000-00-00':$paramData['special_holiday'];
            $storeInfo->special_business_day = empty($paramData['special_business_day'])?'000-00-00':$paramData['special_business_day'];
            $storeInfo->remark = $paramData['remark'];
            $storeInfo->save();
            \DB::table('open_hour_range')->where('store_id',$store_id)->delete();
            if(count($paramData['open_hours']) > 0){
                foreach ($paramData['open_hours'] as $value){
                    \DB::table('open_hour_range')->insert($value);
                }
            }
        }
    }

    public function getStoreUserById($store_id,$params){
        return (new StoreUser())->where('store_id','=',$store_id)
            ->where('id','=',$params['id'])
            ->first([
                'id',
                'nickname',
                'store_id',
                'mobile',
                'email',
                'email_status',
                'permission',
                'gender',
                'super_account',
                'position',
                'menus'
            ]);
    }

    public function checkAddUserVerification($phone,$code){
        return (new Verification())->where('verification_account','=',$phone)
            ->where('verification_code','=',$code)
            ->where('verification_type','=',1)
            ->first([
                'id',
                'send_at'
            ]);
    }

    public function deleteVerificationById($id){
        try{
            (new Verification())->where('id','=',$id)->delete();
        }catch (\Exception $e){
            \Log::error("delete verification fail".$e->getMessage());
        }
    }

    public function addStoreUser($store_id,$params){
        $filterData = array(
            'store_id' => $store_id,
            'nickname' => (isset($params['nickname']) && !empty($params['nickname']))?$params['nickname']:'',
            'email' => isset($params['email'])?$params['email']:'',
            'mobile' => isset($params['mobile'])?$params['mobile']:'',
            'zone' => '886',
            'password' => isset($params['password'])?$params['password']:'',
            'permission' => '',
            'position' => $params['position'], //職稱
            'menus' => $params['menus'], //權限
            'super_account' => 0,
            'gender' => 'male',
        );

        if(is_array($filterData['menus'])){
            $filterData['menus'] = json_encode($filterData['menus']);
        }

        if(empty($filterData['nickname'])){
            $filterData['nickname'] = !empty($filterData['mobile'])?$filterData['mobile']:'默認暱稱';
        }
        if(empty($filterData['password'])){
            unset($filterData['password']);
        }else{
            $filterData['password'] = \Hash::make($filterData['password']);
        }

        if(!empty($filterData['email'])){
            $filterData['email_status'] = 'unverified';
        }

        StoreUser::unguard();
        $user_id = (new StoreUser())->insertGetId($filterData);
        StoreUser::reguard();
        if($user_id > 0){
            return true;
        }else{
            return false;
        }
    }

    public function updateStoreUser($store_id,$params){
        $filterData = array(
            'store_id' => $store_id,
            'nickname' => (isset($params['nickname']) && !empty($params['nickname']))?$params['nickname']:'',
            'email' => isset($params['email'])?$params['email']:'',
            'mobile' => isset($params['mobile'])?$params['mobile']:'',
            'zone' => '886',
            'password' => isset($params['password'])?$params['password']:'',
            'permission' => '',
            'position' => $params['position'], //職稱
            'menus' => $params['menus'], //權限
            'super_account' => 0,
            'gender' => 'male',
        );

        if(is_array($filterData['menus'])){
            $filterData['menus'] = json_encode($filterData['menus']);
        }

        if(empty($filterData['nickname'])){
            $filterData['nickname'] = !empty($filterData['mobile'])?$filterData['mobile']:'默認暱稱';
        }
        if(empty($filterData['password'])){
            unset($filterData['password']);
        }else{
            $filterData['password'] = \Hash::make($filterData['password']);
        }

        if(!empty($filterData['email'])){
            $filterData['email_status'] = 'unverified';
        }

        (new StoreUser())->where('id','=',$params['id'])->update($filterData);
        return true;
    }

    public function removeStoreUser($store_id,$user_id){
        try{
            (new StoreUser())->where('store_id','=',$store_id)
                ->where('id','=',$user_id)
                ->delete();
        }catch (\Exception $e){
            \Log::error("remove store user fail:".$e->getMessage());
        }
    }

    public function getBannerList($store_id){
        return (new StoreBanner())->where('store_id','=',$store_id)
            ->orderBy('id','ASC')->select(['*'])->get();
    }

    public function getBannerFirstImage($store_id){
        return (new StoreBanner())->where('store_id','=',$store_id)
            ->orderBy('id','ASC')->first();
    }

    public function saveBannerList($store_id,$params){
        $banners = isset($params['banners'])?$params['banners']:array();
        //\Log::debug('count:'.count($banners));
        if(count($banners) > 0){

            $dbBanners = $this->getBannerList($store_id);
            $requestBanners = array();

            $insert = $update = $delete = [];

            foreach ($banners as $key=>$value){
                $tempData = array(
                    'id' => isset($value['id'])?intval($value['id']):0,
                    'store_id' => $store_id,
                    'image' => isset($value['image'])?ImageToolsService::getImageNameFromTempUrl($value['image']):''
                );
                if($tempData['id'] <= 0){
                    $insert[] = $tempData;
                }else{
                    $requestBanners[] = $tempData;
                }

            }
            if($dbBanners->isNotEmpty()){
                $dbBanners = $dbBanners->toArray();
                //1. 請求記錄爲空
                if(count($requestBanners) <= 0){
                    foreach ($dbBanners as $value){
                        $delete[] = array(
                            'id' => $value['id'],
                            'image' => $value['image']
                        );
                    }
                }else if(count($requestBanners) > 0){
                    foreach ($dbBanners as $key=>$value){
                        $id = $value['id'];
                        foreach ($requestBanners as $k2=>$item){
                            if($item['id'] == $id){
                                $item['oldImage'] = $value['image'];
                                $update[] = $item;
                                unset($dbBanners[$key]);
                                unset($requestBanners[$k2]);
                                break;
                            }
                        }
                    }

                    if(count($dbBanners) > 0){
                        foreach ($dbBanners as $value){
                            $delete[] = $value;
                        }
                    }

                }
            }

            if(count($insert) > 0){
                foreach ($insert as $value){
                    unset($value['id']);
                    if(!empty($value['image']) && strpos($value['image'],'banner/') === false){
                        ImageToolsService::tempMoveToTarget('banner',$value['image'],'');
                        $value['image'] = ImageToolsService::getTargetFileName('banner',$value['image']);
                    }

                    \DB::table('store_banner')->insert($value);
                }
            }
            if(count($update) > 0){
                foreach ($update as $value){
                    $tempId = $value['id'];
                    $oldImage = $value['oldImage'];
                    unset($value['oldImage']);
                    unset($value['id']);
                    if($oldImage != $value['image']){
                        if(!empty($value['image']) && strpos($value['image'],'banner/') === false){
                            ImageToolsService::tempMoveToTarget('banner',$value['image'],$oldImage);
                            $value['image'] = ImageToolsService::getTargetFileName('banner',$value['image']);
                        }
                    }else{
                        if(!empty($value['image']) && strpos($value['image'],'banner/') === false){
                            ImageToolsService::tempMoveToTarget('banner',$value['image'],'');
                            $value['image'] = ImageToolsService::getTargetFileName('banner',$value['image']);
                        }
                    }
                    \DB::table('store_banner')->where('id','=',$tempId)->update($value);
                }
            }
            
            if(count($delete) > 0){
                foreach ($delete as $item) {
                    ImageToolsService::removeOldImage($item['image']);
                    \DB::table('store_banner')->where('id','=',$item['id'])->delete();
                }
            }
        }
    }

    public function updateStoreLogo($params){
        $store_id = $params['store_id'];
        $logo = isset($params['logo'])?ImageToolsService::getImageNameFromTempUrl($params['logo']):'';
        $storeInfo = (new Store())->where('id','=',$store_id)->first([
            'id',
            'image'
        ]);
        if(!empty($logo) && $storeInfo->image != $logo){
            $fileName = ImageToolsService::tempMoveToTarget('store',$logo,$storeInfo->image);
            $storeInfo->image = $fileName;
            $storeInfo->save();
            file_get_contents(url('/').'/api/share?id='.$params['store_id']);
        }
    }

    public function getStoreLogo($store_id){
        $storeInfo = (new Store())->where('id','=',$store_id)->first([
            'id',
            'image'
        ]);
        $logo = isset($storeInfo->image)?$storeInfo->image:'';
        return ImageToolsService::getUrlWithDefaultPath($logo,'store');
    }


    public function getDownloadAreaList($store_id){
        $date = date('Y-m-d');
        $result = (new ImageSign())->whereRaw("'{$date}' between start_at and end_at")
            ->orderBy('id','DESC')->select([
                'id',
                'title',
                'description',
                'image_config',
                'cover',
                'start_at',
                'end_at'
            ])->get();
        if($result->isNotEmpty()){
            foreach ($result as $key=>$value){
                $value->image = $this->getDownloadAreaImage($value,$store_id);
                $result[$key] = $value;
            }

        }
        return $result;
    }

    public function getDownloadAreaImage($imageSign,$store_id){
        if($imageSign != null){

            $start_date = strtotime($imageSign->start_at);
            $end_date = strtotime($imageSign->end_at);
            $time = time();
            if($time > $start_date && $time > $end_date){
                return "";
            }
            $config = json_decode($imageSign->image_config,TRUE);
            $logoPath = public_path($config['logo_path']);
            $store = (new Store())->where('id','=',$store_id)->first(['code','name']);
            if($store){
                $code = $store->code;
                $storeName = $store->name;
                $targetImagePath = 'image_sign/tv2'.md5($store_id.$imageSign->id).'.png';
                if(file_exists(public_path('/upload/'.$targetImagePath))){
                    unlink(public_path('/upload/'.$targetImagePath));
                }
                if(!file_exists(public_path('/upload/'.$targetImagePath))){
                    if(!is_dir(public_path('upload/image_sign'))){
                        mkdir(public_path('upload/image_sign'));
                        chmod(public_path('upload/image_sign'),777);
                    }
                    try{
                        $qrCode = new QrCode();
                        $qrCode->setEncoding('UTF-8');
                        $qrCode->setText('https://office.techrare.com/memecoins-register-h5/#/register/'.$code.'/2');
                        $qrCode->setSize(intval($config['qr_code_size']));
                        $qrCode->setMargin(0);
                        $qrCode->setErrorCorrectionLevel('high');
                        $qrCode->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0));
                        $qrCode->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0));
                        //$qrCode->setRoundBlockSize(false);
                        $qrCode->setLogoPath($logoPath);
                        $qrCode->setLogoWidth(intval($config['logo_size']));
                        $qrCodePath = public_path('/upload/temp/t'.$store_id.uniqid().'.png');
                        $qrCode->writeFile($qrCodePath);
                        $backendimage = public_path($config['background']);

                        if(file_exists($backendimage)){

                            $backend_image_create = imagecreatefrompng($backendimage);
                            $qrcode_image_create = imagecreatefrompng($qrCodePath);

                            if(isset($config['qr_code_rotate']) && intval($config['qr_code_rotate']) > 0){
                                $transparency = imagecolorallocatealpha( $qrcode_image_create,255,255,255,0 );
                                $rotated = imagerotate( $qrcode_image_create, $config['qr_code_rotate'], $transparency, 1);
                                $background = imagecolorallocate($rotated , 255,  255,  255);
                                imagecolortransparent($rotated,$background);
                                imagealphablending( $rotated, false );
                                imagesavealpha( $rotated, true );
                                //imagepng($rotated,public_path('/upload/temp/'.time().'.png'));
                                $rwidth=imagesx($rotated);
                                $rheight=imagesy($rotated);
                                imagecopymerge(
                                    $backend_image_create,
                                    $rotated,
                                    $config['qr_code_position_x'],
                                    $config['qr_code_position_y'],
                                    0,
                                    0,
                                    $rwidth,
                                    $rheight,
                                    100
                                );
                            }else{
                                imagecopymerge(
                                    $backend_image_create,
                                    $qrcode_image_create,
                                    $config['qr_code_position_x'],
                                    $config['qr_code_position_y'],
                                    0,
                                    0,
                                    $config['qr_code_size'],
                                    $config['qr_code_size'],
                                    100
                                );
                                if(isset($config['number']) && $config['number'] == 4){
                                    imagecopymerge(
                                        $backend_image_create,
                                        $qrcode_image_create,
                                        $config['qr_code_position_x']+1185,
                                        $config['qr_code_position_y'],
                                        0,
                                        0,
                                        $config['qr_code_size'],
                                        $config['qr_code_size'],
                                        100
                                    );
                                    imagecopymerge(
                                        $backend_image_create,
                                        $qrcode_image_create,
                                        $config['qr_code_position_x'],
                                        $config['qr_code_position_y']+1750,
                                        0,
                                        0,
                                        $config['qr_code_size'],
                                        $config['qr_code_size'],
                                        100
                                    );
                                    imagecopymerge(
                                        $backend_image_create,
                                        $qrcode_image_create,
                                        $config['qr_code_position_x']+1185,
                                        $config['qr_code_position_y']+1750,
                                        0,
                                        0,
                                        $config['qr_code_size'],
                                        $config['qr_code_size'],
                                        100
                                    );
                                }
                            }

                            $fontPath = public_path($config['store_code_font']);
                            if($config['store_code_position_x'] > 0){

                                imagettftext(
                                    $backend_image_create,
                                    $config['store_code_font_size'],
                                    0,
                                    $config['store_code_position_x'],
                                    $config['store_code_position_y'],
                                    20,
                                    $fontPath,
                                    $code
                                );
                            }
                            if($config['store_name_position_x'] > 0){
                                imagettftext(
                                    $backend_image_create,
                                    $config['store_name_font_size'],
                                    0,
                                    $config['store_name_position_x'],
                                    $config['store_name_position_y'],
                                    20,
                                    $fontPath,
                                    $storeName
                                );
                            }
                            $merge = 'upload/'.$targetImagePath;
                            imagepng($backend_image_create,public_path($merge));
                            imagedestroy($backend_image_create );
                            imagedestroy($qrcode_image_create );
                            if(isset($rotated)){
                                imagedestroy($rotated );
                            }
                            unlink($qrCodePath);
                        }
                    }catch (\Exception $e){
                        \Log::error("generate download image detail fail:".$e->getMessage());
                    }
                }
                //unset($imageSign->image_config);
                //$imageSign->image = ImageToolsService::getUrlWithDefaultPath($targetImagePath,'image_sign');
                return ImageToolsService::getUrlWithDefaultPath($targetImagePath,'image_sign');
            }
        }
        return "";
    }

    /**
     *
     * @param $store_id
     * @param $id
     * @return array|\Illuminate\Database\Eloquent\Model|null|static
     */
    public function getDownloadAreaDetail($store_id,$id){
        $imageSign = (new ImageSign())->where('id','=',$id)->first([
            'id',
            'title',
            'description',
            'image_config',
            'start_at',
            'end_at'
        ]);
        if($imageSign != null){
            $start_date = strtotime($imageSign->start_at);
            $end_date = strtotime($imageSign->end_at);
            $time = time();
            if($time > $start_date && $time > $end_date){
                return array();
            }
            $config = json_decode($imageSign->image_config,TRUE);
            $logoPath = public_path($config['logo_path']);
            $store = (new Store())->where('id','=',$store_id)->first(['code','name']);
            if($store){
                $code = $store->code;
                $storeName = $store->name;
                $imageSign = $imageSign->toArray();
                $targetImagePath = 'image_sign/tv2'.md5($store_id.$imageSign['id']).'.png';
                if(!file_exists(public_path('/upload/'.$targetImagePath))){
                    if(!is_dir(public_path('upload/image_sign'))){
                        mkdir(public_path('upload/image_sign'));
                        chmod(public_path('upload/image_sign'),777);
                    }
                    try{
                        $qrCode = new QrCode();
                        $qrCode->setEncoding('UTF-8');
                        $qrCode->setText('https://office.techrare.com/memecoins-register-h5/#/register/'.$code.'/2');
                        $qrCode->setSize(intval($config['qr_code_size']));
                        $qrCode->setMargin(0);
                        $qrCode->setErrorCorrectionLevel('high');
                        $qrCode->setForegroundColor(array('r' => 0, 'g' => 0, 'b' => 0, 'a' => 0));
                        $qrCode->setBackgroundColor(array('r' => 255, 'g' => 255, 'b' => 255, 'a' => 0));
                        //$qrCode->setRoundBlockSize(false);
                        $qrCode->setLogoPath($logoPath);
                        $qrCode->setLogoWidth(intval($config['logo_size']));
                        $qrCodePath = public_path('/upload/temp/t'.$store_id.uniqid().'.png');
                        $qrCode->writeFile($qrCodePath);
                        $backendimage = public_path($config['background']);

                        if(file_exists($backendimage)){

                            $backend_image_create = imagecreatefrompng($backendimage);
                            $qrcode_image_create = imagecreatefrompng($qrCodePath);

                            if(isset($config['qr_code_rotate']) && intval($config['qr_code_rotate']) > 0){
                                $transparency = imagecolorallocatealpha( $qrcode_image_create,255,255,255,0 );
                                $rotated = imagerotate( $qrcode_image_create, $config['qr_code_rotate'], $transparency, 1);
                                $background = imagecolorallocate($rotated , 255,  255,  255);
                                imagecolortransparent($rotated,$background);
                                imagealphablending( $rotated, false );
                                imagesavealpha( $rotated, true );
                                //imagepng($rotated,public_path('/upload/temp/'.time().'.png'));
                                $rwidth=imagesx($rotated);
                                $rheight=imagesy($rotated);
                                imagecopymerge(
                                    $backend_image_create,
                                    $rotated,
                                    $config['qr_code_position_x'],
                                    $config['qr_code_position_y'],
                                    0,
                                    0,
                                    $rwidth,
                                    $rheight,
                                    100
                                );

                            }else{
                                imagecopymerge(
                                    $backend_image_create,
                                    $qrcode_image_create,
                                    $config['qr_code_position_x'],
                                    $config['qr_code_position_y'],
                                    0,
                                    0,
                                    $config['qr_code_size'],
                                    $config['qr_code_size'],
                                    100
                                );
                                if(isset($config['number']) && $config['number'] == 4){
                                    imagecopymerge(
                                        $backend_image_create,
                                        $qrcode_image_create,
                                        $config['qr_code_position_x']+1185,
                                        $config['qr_code_position_y'],
                                        0,
                                        0,
                                        $config['qr_code_size'],
                                        $config['qr_code_size'],
                                        100
                                    );
                                    imagecopymerge(
                                        $backend_image_create,
                                        $qrcode_image_create,
                                        $config['qr_code_position_x'],
                                        $config['qr_code_position_y']+1750,
                                        0,
                                        0,
                                        $config['qr_code_size'],
                                        $config['qr_code_size'],
                                        100
                                    );
                                    imagecopymerge(
                                        $backend_image_create,
                                        $qrcode_image_create,
                                        $config['qr_code_position_x']+1185,
                                        $config['qr_code_position_y']+1750,
                                        0,
                                        0,
                                        $config['qr_code_size'],
                                        $config['qr_code_size'],
                                        100
                                    );
                                }
                            }

                            $fontPath = public_path($config['store_code_font']);
                            if($config['store_code_position_x'] > 0){

                                imagettftext(
                                    $backend_image_create,
                                    $config['store_code_font_size'],
                                    0,
                                    $config['store_code_position_x'],
                                    $config['store_code_position_y'],
                                    20,
                                    $fontPath,
                                    $code
                                );
                            }
                            if($config['store_name_position_x'] > 0){
                                imagettftext(
                                    $backend_image_create,
                                    $config['store_name_font_size'],
                                    0,
                                    $config['store_name_position_x'],
                                    $config['store_name_position_y'],
                                    20,
                                    $fontPath,
                                    $storeName
                                );
                            }
                            $merge = 'upload/'.$targetImagePath;
                            imagepng($backend_image_create,public_path($merge));
                            imagedestroy($backend_image_create );
                            imagedestroy($qrcode_image_create );
                            if(isset($rotated)){
                                imagedestroy($rotated );
                            }
                            unlink($qrCodePath);
                        }
                    }catch (\Exception $e){
                        \Log::error("generate download image detail fail:".$e->getMessage());
                    }
                }
                unset($imageSign['image_config']);
                $imageSign['image'] = ImageToolsService::getUrlWithDefaultPath($targetImagePath,'image_sign');
                return $imageSign;
            }
        }
        return array();
    }

    /**
     * @deprecated v0.1
     * @param $image_file
     * @return string
     */
    public function base64EncodeImage($image_file){
        $base64_image = '';
        $image_info = getimagesize($image_file);
        $image_data = fread(fopen($image_file, 'r'), filesize($image_file));
        $base64_image = 'data:' . $image_info['mime'] . ';base64,' . chunk_split(base64_encode($image_data));
        return $base64_image;
    }




    public function getBankList($store_id){
        return (new StoreBankAccount())->where('store_id','=',$store_id)
            ->select([
                'id',
                'bank_name',
                'receiver_name',
                'bank_account',
                'bank_phone',
                'created_at',
            ])->get();
    }

    public function getBankCount($store_id){
        return (new StoreBankAccount())->where('store_id','=',$store_id)->count();
    }

    public function addBank($store_id,$param){
        $data = array(
            'store_id' => $store_id,
            'bank_name' => $param['bank_name'],
            'receiver_name' => $param['receiver_name'],
            'bank_account' => $param['bank_account'],
            'bank_phone' => $param['bank_phone'],
            'status' => 1,
            'created_at' => date('Y-m-d H:i:s')
        );
        (new StoreBankAccount())->insert($data);
    }

    public function deleteBank($store_id,$id){
        try{
            (new StoreBankAccount())->where('store_id','=',$store_id)
                ->where('id','=',$id)->delete();
        }catch (\Exception $e){
            \Log::error("remove bank account fail".$e->getMessage());
        }

    }

    public function getBankById($store_id,$id){
       return  (new StoreBankAccount())->where('store_id','=',$store_id)
            ->where('id','=',$id)->first();
    }

    public function getWithDrawlList($store_id,$per_page){
        return (new Withdrawl())->where('store_id','=',$store_id)
            ->where('type','=',1)
            ->select([
                'id',
                'amount',
                'service_charge',
                'status',
                'remark',
                'bank_name',
                'receiver_name',
                'bank_account',
                'bank_phone',
                'handle_note',
                'handle_date',
                'created_at'
            ])->orderBy('id','DESC')->paginate($per_page);
    }

    public function getWithDrawlProcessCount($store_id){
        return (new Withdrawl())->where('store_id','=',$store_id)
            ->where('status','=',0)
            ->where('type','=',1)
            ->count();
    }

    public function getStoreIncome($store_id){
        return (new StoreAccount())->where('store_id','=',$store_id)
            ->first([
                'id',
                'business_income',
                'credits_income',
                'return_credits'
            ]);
    }

    public function addWithDrawl($store_id,$params,$bank,$storeIncome,$type){
        $charge = $params['amount'] * 0.01;
//        if($charge <= 0.1){
//            $charge = 0.1;
//        }
        $data = array(
            'store_id' => $store_id,
            'type' => 1,
            'amount' => $params['amount'],
            'status' => 0,
            'remark' => '',
            'bank_name' => $bank->bank_name,
            'receiver_name' => $bank->receiver_name,
            'bank_account' => $bank->bank_account,
            'bank_phone' => $bank->bank_phone,
            'created_at' => date('Y-m-d H:i:s'),
            'service_charge' => $charge,
            'money_type' => $type,
        );
        (new Withdrawl())->insert($data);
        if($type == 1){
            $storeIncome->business_income -= $params['amount'];
        }else{
            $storeIncome->credits_income -= $params['amount'];
        }
        $storeIncome->save();
    }

    public function getWithDrawlInfo($store_id,$id){
        return (new Withdrawl())->where('store_id','=',$store_id)
            ->where('id','=',$id)
            ->where('type','=',1)
            ->first([
                'id',
                'amount',
                'service_charge',
                'status',
                'money_type'
            ]);
    }

    public function cancelWithDrawl($withDrawlInfo,$storeIncome){
        $amount = $withDrawlInfo->amount;
        $withDrawlInfo->status = 2;
        $withDrawlInfo->save();

        if($withDrawlInfo->money_type == 1){
            $storeIncome->business_income += $amount;
        }else{
            $storeIncome->credits_income += $amount;
        }
        $storeIncome->save();
    }

    public function inviteNum($id){
        return (new \App\Invitelog())->where(['promo_uid'=>$id,'invite_type'=>2])->count();
    }

    public function company_bank(){
        return (new \App\CompanyBank())->where('is_default',1)->first();

    }

    public function return_notice($store_id){
        $account = $this->getStoreIncome($store_id);
        $result = (new \App\Store())->where('id',$store_id)->first(['is_return']);
        $msg2 = '該儲值囉~,親愛的老闆~您的蜂幣不足$3,000,因逾期未儲值已暫停您的權限,目前無法參與現金回饋活動,您的店鋪將會於memecoins暫時下架,需於72小時內辦理儲值手續
                        請聯繫客服或直接來電04-23012801';
        $msg1 = '親愛的老闆~因為您目前蜂幣餘額不足$3,000，已先暫停您店鋪現金回饋活動，請於72小時內辦理儲值手續，請盡快與客服聯繫或直接來電04-23012801';
        if($account->return_credits <3000 && $result->is_return){
            if(Cache::has($store_id)){
                $data = Cache::get($store_id);
                $data = json_decode($data);
                if($data->time <= time()-72*3600){

                    if($data->number == 1){
                        $value = [
                            'time'  => time(),
                            'number' => 2,
                            'flag' => 1,
                            'store_id'=>$store_id
                        ];
                        Cache::forever($store_id,json_encode($value));
                        $value['msg'] = $msg2;
                        $this->noticelog($value['msg'],$store_id);
                        return $value;

                    }else if($data->number == 2){
                        (new \App\Store())->where('id',$store_id)->update(['status'=>0]);
                        return [
                            'time'  => time(),
                            'number' => 3,
                            'flag' => 1,
                            'store_id'=>$store_id,
                            'msg' => $msg2
                        ];
                    }

                }
                if($data->number == 1){
                    $msg = $msg1;
                }else if($data->number == 2){
                    $msg = $msg2;
                }
                return [
                    'time'  => $data->time,
                    'number' => 1,
                    'flag' => 0,
                    'store_id'=>$store_id,
                    'msg' => $msg,
                ];

            }else{

                $value = [
                    'time'  => time(),
                    'number' => 1,
                    'flag' => 1,
                    'store_id'=>$store_id
                ];

                Cache::forever($store_id, json_encode($value));
                $value['msg'] = $msg1;
                $this->noticelog($value['msg'],$store_id);
                $job = (new \App\Jobs\CloseStoreJob($store_id))->onQueue('close')->delay(2*72*3600);
                dispatch($job);
                return $value;
            }

        }
        Cache::forget($store_id);
        return ['number'=>0,'flag'=>0];

    }

    private function noticelog($description,$store_id){
        //添加通知消息
        (new \App\NoticeLog())->insert([
            'platform_type' => 2,
            'title' => $description,
            'description' => $description,
            'type' => 3,
            'content' => $description,
            'point_id' => $store_id,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
    }

    public function getNotice($store_id,$per_page){
        return (new \App\NoticeLog())
            ->where(function($query) use($store_id){
                $query->where('point_id',$store_id)->where('type',3)->where('platform_type',2);
            })
            ->orWhere(function($query) use($store_id){
                $query->where('type',1)->where('platform_type',2);
            })
            ->orWhere(function($query) use($store_id){
                $query->where('type',2)->where('platform_type',2);
            })
            ->select([
                'id',
                'title',
                'description',
                'created_at'
            ])
            ->orderBy('created_at','DESC')
            ->paginate($per_page);

    }

    public function unread($store_id){
       $sum = (new \App\NoticeLog())
           ->where(function($query) use($store_id){
               $query->where('point_id',$store_id)->where('type',3)->where('platform_type',2);
           })
           ->orWhere(function($query) use($store_id){
               $query->where('type',1)->where('platform_type',2);
           })
           ->orWhere(function($query) use($store_id){
               $query->where('type',2)->where('platform_type',2);
           })->count();

       $read = (new \App\NoticeBoss())->join('notice_log','notice_log.id','=','notice_id')->where('notice_boss.store_id',$store_id)->count();
       return $sum-$read;
    }

    public function notice_view($id,$store_id){
        $data = (new \App\NoticeLog())->where('id',$id)->first([
            'id',
            'title',
            'description',
            'content',
            'created_at'
        ]);
        $result = (new \App\NoticeBoss())->where('notice_id',$id)->where('store_id',$store_id)->first();
        if(!$result){
            (new \App\NoticeBoss())->insert(['notice_id'=>$id,'store_id'=>$store_id]);
        }
        return $data;
    }
}