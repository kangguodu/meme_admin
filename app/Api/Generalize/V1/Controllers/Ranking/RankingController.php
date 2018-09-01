<?php
/**
 * Created by PhpStorm.
 * User: zzq
 * Date: 18-6-23
 * Time: 下午4:06
 */

namespace App\Api\Generalize\V1\Controllers\Ranking;


use App\Api\Generalize\V1\Controllers\BaseController;
use App\Member;
use App\MemberCredits;
use Illuminate\Http\Request;

class RankingController extends BaseController
{
    public function get($handle,Request $request)
    {
        switch ($handle){
            case 'money':
                return $this->rankByMoney($request);
            case 'invite':
                return $this->rankByInvite($request);
            case 'mine':
                return $this->userRanking();
        }
    }
    private function userRanking()
    {
        $uid = $this->getAuthUserId();

        $ranking['invite'] = $this->inviteRanking($uid);
        $ranking['money'] = $this->moneyRanking($uid);

        return$ranking;
//        return $this->success($ranking);
    }
    private function inviteRanking($uid)
    {
        $mine = Member::find($uid);
        $inviteCount = $mine->invite_count;
        $ranking = Member::where('invite_count','>=',$inviteCount)->where('user_type',2)->orderBy('invite_count','DESC')
            ->orderBy('id','ASC')
            ->pluck('id');
        $ranking = $ranking->toArray();
        $ranking = array_search($uid,$ranking);
        $ranking = empty($ranking)?0:$ranking;
        return ++$ranking;
    }
    private function moneyRanking($uid)
    {
        $uid = $this->getAuthUserId();
        $mine = MemberCredits::where('member_id',$uid)->first();
        $money = $mine->promo_credits_total;
        $ranking = MemberCredits::where('promo_credits_total','>=',$money)->orderBy('promo_credits_total','DESC')
            ->orderBy('member_id','ASC')
            ->pluck('member_id');
        $ranking = $ranking->toArray();
        $ranking = array_search($uid,$ranking);
        $ranking = empty($ranking)?0:$ranking;
        return ++$ranking;
    }
    private function rankByInvite($request)
    {
        $perPage = empty($request->per_page)?10:$request->per_page;
        $rankObj = Member::where('user_type',2)
            ->orderBy('invite_count','DES')
            ->orderBy('id', 'ASC')
            ->paginate($perPage,['avatar','nickname','invite_count']);
        if ($rankObj)
            $rankObj->appends('per_page',$perPage);

//        $rankObj['ranking'] = ++$ranking;
        return $rankObj->toArray();
//        return $this->success($rankObj->toArray());
    }
    private function rankByMoney($request)
    {
        if (empty($ranking))
            $ranking = 0;

        $perPage = empty($request->per_page)?10:$request->per_page;
        $rankObj = Member::join('member_credits','member.id','=','member_credits.member_id')
                    ->where('member.user_type',2)
                    ->orderBy('member_credits.promo_credits_total','DES')
                    ->orderBy('member_id', 'ASC')
                    ->paginate($perPage,['member.avatar','member.nickname','member_credits.promo_credits_total']);
        if ($rankObj)
            $rankObj->appends('per_page',$perPage);
//        $rankObj['ranking'] = ++$ranking;
        return $rankObj->toArray();
//        return $this->success($rankObj->toArray());
    }
}