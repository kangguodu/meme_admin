<?php
/**
 * Created by PhpStorm.
 * User: zzq
 * Date: 18-7-28
 * Time: 下午3:54
 */

namespace App\Admin\Repository;


use App\Admin\Provider\AdminHelpers;
use App\ImageSignApply;

class LiPaiHandle
{

    public function handleBtn($id)
    {

        switch (request('status', 1)){
            case '1':
                $btn = "<a onclick='javascript:passImgSignApp({$id})' title='通過'><i class='fa fa-check-circle'></i> </a>";
                break;
            case '2':
                $btn = "<a onclick='javascript:overImgSignApp({$id})' title='完成'><i class='fa fa-check-circle'></i> </a>";
                break;
            default:
                return ;

        }
        $btn .= "<a onclick='javascript:rejectImgSignApp({$id})'><i class='fa fa-times-circle'></i> </a>";
        return $btn;
    }

    public function doHandle($data)
    {
        switch ($data['handle']){
            case 'pass':
                return $this->doApply($data, 2);
            case 'over':
                return $this->doApply($data, 3);
            case 'reject':
                return $this->doApply($data, 4, $data['note']);
        }
    }

    private function doApply($data, $status, $reason=null)
    {
        if (ImageSignApply::where('id', $data['id'])->update(['status' => $status, 'cancel_reason' => $reason]))
            return AdminHelpers::jsonResponse('操作成功');
        return AdminHelpers::jsonResponse('操作失敗', false);
    }

}