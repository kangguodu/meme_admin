<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class RegionsList extends Model
{
    protected $table = 'regions_list';

    public $timestamps = false;

    public static function getCityOption()
    {
        $arr = [null => '請選擇縣市'];
        $rArr = self::where('parent_id', 1)->pluck('region_name', 'region_name')->toArray();
        return array_merge($arr, $rArr);
    }
}
