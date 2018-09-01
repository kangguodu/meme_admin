<?php
/**
 * Created by PhpStorm.
 * User: zzq
 * Date: 18-7-2
 * Time: 上午9:52
 */

namespace APP\Api\Generalize\V1\Controllers\CreditsLog;


use App\Api\Generalize\V1\Controllers\BaseController;
use App\MemberCreditsLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DailyLogControllers extends BaseController
{
    public function get($handle, Request $request)
    {
        switch ($handle){
            case 'daily':
                return $this->getDailyLog($request);
            default:
                return $this->responseError('404 Not Fount',40401,404);
        }
    }
    private function getDailyLog(Request $request)
    {
        $userId = $this->getAuthUserId();
        $fromDate = $this->parseDate($request);
        $tradeType = "推廣回饋";

        $sql = sprintf("SELECT member_credits_log.*,SUM(amount) as amount FROM `member_credits_log` WHERE member_id=%d AND date>'%s' AND type=1 AND trade_type='%s' GROUP BY date",$userId,$fromDate,$tradeType);

        $creditsLog = DB::select($sql);
//        print_r($creditsLog);
        return $creditsLog;
//        return $this->success($creditsLog);
    }
    private function parseDate(Request $request)
    {
        $days = $request->days?(int)$request->days:7;
        $timestamp = time()-$days*24*60*60;
        return date('Y-m-d',$timestamp);

    }
}