<?php

namespace App\Api\Merchant\Services;


class ImageToolsService
{

    static private $host = 'https://office.techrare.com/memecoinsapi/public';
    static private $imageType = [
        'banner' => 'banner/',
        'store' => 'store/',
        'transfer' => 'transfer/'
    ];

    static $defaultImage = [
        'banner' => 'banner/banner.jpg', //店铺默认Logo
        'download' => 'download/example1.png',
        'image_sign' => 'download/example1.png',//立牌
        'member' => 'notice_icon.png',//会员头像
        'store' => 'logo.png',
        'transfer' => 'notice_icon.png'
    ];

    public static function getDefaultImageByType($type){
        return isset(self::$defaultImage[$type])?self::$defaultImage[$type]:'';
    }

    public static function getImageTypeFolder($type){
        return isset(self::$imageType[$type])?self::$imageType[$type]:'other/';
    }

    public static function getImageTypes(){
        return self::$imageType;
    }


    public static function getUrlWithDefaultPath($imagePath,$type = 'shop', $default = ''){
        $httpIndex = strpos($imagePath,'http');
        if($httpIndex !== false){
            return $imagePath;
        }
        if(empty($imagePath)){
            $imagePath = self::getDefaultImageByType($type);
            if(empty($imagePath) && !empty($default)){
                $imagePath = $default;
            }
        }
        $uploadIndex = strpos($imagePath,'upload');
        if($uploadIndex === 0){
            $imagePath = str_replace('upload/','',$imagePath);
        }

        $uploadIndex2 = strpos($imagePath,'/upload');
        if($uploadIndex2 === 0){
            $imagePath = str_replace('/upload/','',$imagePath);
        }
        //\Log::debug("image path".$imagePath);
        return empty($imagePath)?'':self::$host.'/upload/'.$imagePath;
    }

    public static function getImageNameFromTempUrl($imageUrl){
        $tempIndex = strpos($imageUrl,'temp/');
        $uploadIndex = strpos($imageUrl,'upload/');
        if($tempIndex){
            $imageName = mb_substr($imageUrl,$tempIndex + 5);
            return $imageName;
        }else if($uploadIndex){
            $imageName = mb_substr($imageUrl,$uploadIndex + 7);
            return $imageName;
        }
        return $imageUrl;
    }


    public static function getTargetFileName($type,$fileName){
        $targetDir = isset(self::$imageType[$type])?self::$imageType[$type]:'other/';
        $dateFolder = '';
        return $targetDir.$dateFolder.$fileName;
    }

    /**
     * 将图片从临时目录移到目标目录
     * @param $type
     * @param $fileName
     * @param $oldImagePath
     * @return string
     */
    public static function tempMoveToTarget($type,$fileName,$oldImagePath = ''){
        $targetDir = isset(self::$imageType[$type])?self::$imageType[$type]:'other/';
        //\Log::debug("imageType:".$type.' '.$targetDir );
        //$dateFolder = date('ymd').'/';
        $dateFolder = '';
        try{
            $sourceFile = public_path().'/temp/'.$fileName;
            $sourceFile2 = public_path().'/upload/temp/'.$fileName;
            if(file_exists($sourceFile) || file_exists($sourceFile2)){
                $targetPath = public_path().'/upload/'.$targetDir.$dateFolder;
                //\Log::debug("target:".$type.' '.$targetPath );
                //$dateFolder = date('ymd').'/';
                if(!is_dir($targetPath)){
                    mkdir($targetPath, 0777, true);
                }
                if(file_exists($sourceFile)){
                    rename($sourceFile,$targetPath.$fileName);
                }else if(file_exists($sourceFile2)){
                    rename($sourceFile2,$targetPath.$fileName);
                }

                if(!empty($oldImagePath)){
                    self::removeOldImage($oldImagePath);
                }
                return $targetDir.$dateFolder.$fileName;
            }else if(file_exists(public_path().'/upload/'.$targetDir.$dateFolder.$fileName)){
                return $targetDir.$dateFolder.$fileName;
            }else{
                \Log::debug("sourcePath not exist:" .$sourceFile);
            }
        }catch (\Exception $e){
            \Log::error($e->getFile().' '.$e->getLine().' '.$e->getCode().' '.$e->getMessage());
        }
        return $fileName;
    }

    public static function removeOldImage($path){
        try{
            if(empty($path)){
                return true;
            }
            $imagePath = public_path().'/upload/'.$path;
            if(file_exists($imagePath)){
                unlink($imagePath);
            }
        }catch (\Exception $e){
            \Log::error("删除旧图片失败: ".$e->getFile().' '.$e->getLine().' '.$e->getCode().' '.$e->getMessage());
            return false;
        }
        return true;
    }



}