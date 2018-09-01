<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/6/25
 * Time: 12:13
 */

namespace App\Api\V1\Transformers;

use App\MemberCreditsLog;
use League\Fractal\TransformerAbstract;

class CreditsTransformer extends TransformerAbstract
{
    public function transform(MemberCreditsLog $object){
        return [
            'id'   => $object->id,
            'type' => $object->type,
            'trade_type' => $object->trade_type,
            'date' => $object->date,
            'time' => date('H:i:s',strtotime($object->log_date)),
            'log_date' => $object->log_date,
            'amount' => $object->amount,
//            'desc' => $object->desc,
//            'remark' => $object->remark,
        ];
    }

}