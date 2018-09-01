<?php
/**
 * Created by PhpStorm.
 * User: zzq
 * Date: 18-6-25
 * Time: 上午10:27
 */

namespace App\Api\Generalize\V1\Controllers\Activity;


use App\Activity;
use App\Api\Generalize\V1\Controllers\BaseController;
use Illuminate\Http\Request;
use App\Api\V1\Services\BaseService;

class ActivityController extends BaseController
{
    public function get($handle,Request $request)
    {
        switch ($handle){
            case 'list':
                return $this->activityList($request);
            case 'detail':
                return $this->activityDetail($request);
            default:
                return $this->responseError('404 Not Found',404001,404);
        }
    }
    private function activityList(Request $request)
    {
        $perPage = empty($request->per_page)?20:(int)$request->per_page;
        $cond = [
            'platform_type' => 3,
        ];
        $activity = Activity::where($cond)->orderBy('created_at','DES')->paginate($perPage);
        if(count($activity)){
            foreach ($activity as $v){
                $v->posters_pictures = $v->posters_pictures ? BaseService::image($v->posters_pictures) : '';
            }
        }
        $activity->appends('per_page',$perPage);
        return $activity->toArray();
//        return $this->success($activity);
    }
    private function activityDetail(Request $request)
    {
        $activityId = $request->id;
        if (!$activityId)
            return $this->responseError('activity id not given',40001,400);

        $activity = Activity::find($activityId);
        $activity->posters_pictures = $activity->posters_pictures ? BaseService::image($activity->posters_pictures) : '';
        return $activity;
//        return $this->success($activity);
    }
}