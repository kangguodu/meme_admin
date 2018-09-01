<?php


namespace App\Api\Merchant\Transformers;

use App\Api\Merchant\Services\ImageToolsService;
use League\Fractal\TransformerAbstract;

class StoreTransferTransformer extends TransformerAbstract
{
    public function transform($object){
        $result = [
            'id'=>$object->id,
            'transfer_date'=>$object->transfer_date,
            'accounts_no' => $object->accounts_no,
            'amount' => $object->amount,
            'attachment' => ImageToolsService::getUrlWithDefaultPath($object->attachment,'transfer'),
            'status' => $object->status,
            'status_text' => $this->getStatusText($object->status),
            'created_at' => $object->created_at,
        ];
        return $result;
    }

    private function getStatusText($status){
        $statues = [
            'pending' => '待處理',
            'processing' => '處理中',
            'cancelled' => '已取消',
            'refunded' => '已退款',
            'failed' => '付款失敗',
        ];
        return isset($statues[$status])?$statues[$status]:'';
    }
}