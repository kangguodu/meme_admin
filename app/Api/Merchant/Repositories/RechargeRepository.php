<?php
namespace App\Api\Merchant\Repositories;


use App\Api\Merchant\Services\ImageToolsService;
use App\StoreTrans;
use App\StoreTransfer;
use Illuminate\Support\Facades\Config;

use GuzzleHttp\Client;
/*
    程式撰寫流程(以信用卡為範例)
    0.參數定義
    1.讀取購物車商品
    2.寫入訂單，取得訂單編後
    3.透過站內付SDK 送出請求，並取得API回傳參數
    4.將API回傳參數往前端送
*/
class RechargeRepository
{
    private function getTradeNo(){
        return 'MES'.date('ymdHis').mt_rand(10,99);
    }

    public function createOrder($amount){
        try {

            $obj = new \ECPay_AllInOne();

            //服務參數
            $obj->ServiceURL  = "https://payment-stage.ecpay.com.tw/Cashier/AioCheckOut/V5";  //服務位置
            $obj->HashKey     = '5294y06JbISpM5x9' ;                                          //測試用Hashkey，請自行帶入ECPay提供的HashKey
            $obj->HashIV      = 'v77hoKGq4kWxNNIS' ;                                          //測試用HashIV，請自行帶入ECPay提供的HashIV
            $obj->MerchantID  = '2000132';                                                    //測試用MerchantID，請自行帶入ECPay提供的MerchantID
            $obj->EncryptType = '1';                                                          //CheckMacValue加密類型，請固定填入1，使用SHA256加密
            //基本參數(請依系統規劃自行調整)
            $MerchantTradeNo = $this->getTradeNo() ;
            $obj->Send['ReturnURL']         = Config::get("ecpay.returnUrl") ;     //付款完成通知回傳的網址
            $obj->Send['MerchantTradeNo']   = $MerchantTradeNo;                           //訂單編號
            $obj->Send['MerchantTradeDate'] = date('Y/m/d H:i:s');                        //交易時間
            $obj->Send['TotalAmount']       = intval($amount);                                       //交易金額
            $obj->Send['TradeDesc']         = "蜂幣回贈儲蓄金" ;                           //交易描述
            $obj->Send['ChoosePayment']     = \ECPay_PaymentMethod::ALL ;                  //付款方式:全功能
            //訂單的商品資料
            array_push($obj->Send['Items'], array('Name' => "蜂幣回贈儲蓄金", 'Price' => (int)$amount,
                'Currency' => "元", 'Quantity' => (int) "1", 'URL' => "dedwed"));
            # 電子發票參數
            /*
            $obj->Send['InvoiceMark'] = ECPay_InvoiceState::Yes;
            $obj->SendExtend['RelateNumber'] = "Test".time();
            $obj->SendExtend['CustomerEmail'] = 'test@ecpay.com.tw';
            $obj->SendExtend['CustomerPhone'] = '0911222333';
            $obj->SendExtend['TaxType'] = ECPay_TaxType::Dutiable;
            $obj->SendExtend['CustomerAddr'] = '台北市南港區三重路19-2號5樓D棟';
            $obj->SendExtend['InvoiceItems'] = array();
            // 將商品加入電子發票商品列表陣列
            foreach ($obj->Send['Items'] as $info)
            {
                array_push($obj->SendExtend['InvoiceItems'],array('Name' => $info['Name'],'Count' =>
                    $info['Quantity'],'Word' => '個','Price' => $info['Price'],'TaxType' => ECPay_TaxType::Dutiable));
            }
            $obj->SendExtend['InvoiceRemark'] = '測試發票備註';
            $obj->SendExtend['DelayDay'] = '0';
            $obj->SendExtend['InvType'] = ECPay_InvType::General;
            */
            //產生訂單(auto submit至ECPay)
            $obj->CheckOut();


        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function createTransfer($params,$user){
        $model = new StoreTransfer();
        $processCount = $model->where('store_id','=',$user->store_id)
            ->where('status','=','pending')
            ->count();
        if($processCount > 0){
            return false;
        }
        $fileName = ImageToolsService::getImageNameFromTempUrl($params['attachment']);
        $attachment = ImageToolsService::tempMoveToTarget('transfer',$fileName,'');
        $data = array(
            'transfer_date' => isset($params['transfer_date'])?$params['transfer_date']:date('Y-m-d'),
            'accounts_no' => $params['accounts_no'],
            'amount' => $params['amount'],
            'attachment' => 'upload/'.$attachment,
            'status' => 'pending',
            'created_by' => $user->id,
            'store_id' => $user->store_id,
            'created_at' => date('Y-m-d H:i:s')
        );
        $model->insert($data);
        return true;
    }

    public function createTransferBy($params,$user){
        if($params['amount'] < 1000){
            return ['success'=>false,'msg'=>'儲值金額不得少於1000'];
        }
        $model = new StoreTransfer();
        \DB::beginTransaction();
        try{
            $data = array(
                'transfer_date' => isset($params['transfer_date'])?$params['transfer_date']:date('Y-m-d'),
                'amount' => $params['amount'],
                'status' => 'completed',
                'created_by' => $user->id,
                'store_id' => $user->store_id,
                'created_at' => date('Y-m-d H:i:s')
            );

            $account = (new \App\StoreAccount())->where('store_id','=',$user->store_id)
                            ->first([
                                'id',
                                'business_income',
                                'credits_income',
                                'return_credits'
                            ]);
            $transData = array(
                'store_id' => $user->store_id,
                'trans_type' => 2,
                'trans_category' => 6,
                'trans_category_name' => '儲值支出',
                'trans_date' => date('Y-m-d'),
                'trans_datetime' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => $user->id,
                'created_name' => $user->nickname,
                'custom_field1' => ''
            );
            if($params['type'] == 1){
                if($account->business_income < $params['amount']){
                    return ['success'=>false,'msg'=>'儲值金額大於蜂幣收入，儲值失敗'];
                }
                $transData['amount'] = $params['amount'];
                $transData['balance'] = $account->business_income;
                $transData['trans_description'] = '營業蜂幣收入進行儲值，支出 '.$params['amount'];
                (new \App\StoreTrans())->insert($transData);
                $account->business_income -= $params['amount'];
            }else{
                if($account->credits_income < $params['amount']){
                    return ['success'=>false,'msg'=>'儲值金額大於回饋收入，儲值失敗'];
                }
                $transData['amount'] = $params['amount'];
                $transData['balance'] = $account->credits_income;
                $transData['trans_description'] = '推廣回饋收入進行儲值，支出 '.$params['amount'];
                (new \App\StoreTrans())->insert($transData);
                $account->credits_income -= $params['amount'];
            }
            $account->return_credits += $params['amount'];
            $transRechargeData = array(
                'store_id' => $user->store_id,
                'trans_type' => 1,
                'trans_category' => 1,
                'trans_category_name' => '蜂幣回贈儲值金',
                'amount' => $params['amount'],
                'balance' => $account->return_credits,
                'trans_date' => date('Y-m-d'),
                'trans_datetime' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => $user->id,
                'created_name' => $user->nickname,
                'custom_field1' => ''
            );
            if($params['type'] == 1){
                $transRechargeData['trans_description'] = '蜂幣預存 '.$params['amount'];
            }else{
                $transRechargeData['trans_description'] = '回饋預存 '.$params['amount'];
            }


            (new \App\StoreTrans())->insert($transRechargeData);
            $model->insert($data);
            $account->save();
            if($account->return_credits >=3000){
                (new \App\Store())->where('id',$user->store_id)->update(['status'=>1]);
            }
            \DB::commit();
            return ['success'=>true,'msg'=>''];


        }catch (\Exception $e){
            \DB::rollback();
            \Log::error("recharge fail: ".$e->getMessage().', '.$e->getLine());
            return ['success'=>false,'msg'=>'儲值出錯'];
        }


    }

    public function getTransferList($user,$per_page){
        return (new StoreTransfer())->where('store_id','=',$user->store_id)
            ->orderBy('id','DESC')
            ->paginate($per_page);
    }


}