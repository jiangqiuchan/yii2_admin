<?php
namespace frontend\controllers;

use yii\web\Controller;
use Yii;
use common\functions\Functions;
use common\models\User;
use common\components\Wechat;
use common\models\OauthThirdLogin;
use common\models\ErrorLog;
use frontend\models\activity\Act1;
use frontend\models\SoftPdfOrder;
use backend\models\SoftPdfRefund;
use common\components\PayFun;

require_once("../../common/components/QQAPI/qqConnectAPI.php");
require_once "../../vendor/WxPay/log.php";
require_once "../../vendor/AliPay/dmf/AlipayTradeService.php";

class NotifyController extends Controller 
{
    public function init()
    {
        $this->enableCsrfValidation = false;
    }
    
    //支付宝当面付--扫码支付回调
    public function actionZfbDmfNotify1(){
        $logHandler= new \CLogFileHandler("../web/logs/".date('Y-m-d').'zfbdmf.log');
        \Log::Init($logHandler, 15);

        $arr = $_POST;
        \Log::DEBUG("call back:" . json_encode($arr,JSON_UNESCAPED_UNICODE));

		$qrPay = new \AlipayTradeService();
		$result = $qrPay->check($arr);
        
        if($result){
            if ( $arr['trade_status'] == 'TRADE_SUCCESS') {
                //  订单金额
                $total_amount   = $arr['total_amount'];
                
                //  实收金额
                $receipt_amount = $arr['receipt_amount'];
                if ($arr['seller_id'] == $qrPay->seller_id && $receipt_amount != 0 && ($total_amount == $receipt_amount)) {
                    //  本站订单号
                    $out_trade_no   = $arr['out_trade_no'];
                
                    //  支付宝交易号
                    $trade_no       = $arr['trade_no'];
                
                    //  回调通知的发送时间
                    $notify_time    = $arr['notify_time'];
                
                    //  支付时间
                    $gmt_payment    = $arr['gmt_payment'];
                
                    $hasRecord = SoftPdfOrder::find()
                    ->where(['out_trade_no' => $out_trade_no])
                    ->one();
     
                    if ($hasRecord && $hasRecord->pay_status != 1)
                    {
                        $hasRecord->pay_status  = 1;
                        $hasRecord->notify_at = strtotime($notify_time);
                        $hasRecord->receipt_amount = $receipt_amount;
                        $hasRecord->trade_no = $trade_no;
                        $hasRecord->gmt_payment = strtotime($gmt_payment);
                        if ($hasRecord->save()) {
                            //保存免费人工转换次数
                            if ($hasRecord->man_convert_pass) {
                                $referer = $hasRecord->referer;
                                if (strpos($referer,'pdf') !== false) {
                                    $package = $hasRecord->package;
                                    $user_id = $hasRecord->user_id;
                                    $relate_id = $hasRecord->id;
                                    FreeManConvert::addFreeTimes($package,$user_id,$relate_id);
                                }
                            }

                            echo "success";die;
                        }else{
                            echo "fail";
                        };
                    }
                }
            }
        
            echo "success";
        
        } else {
            echo "fail";
        
        }
    }
    
    //微信扫码支付回调
    public function actionWxSmzfNotify()
    {
        libxml_disable_entity_loader(true);
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $data = Functions::xmlToArray($xml);
        $payModel = new PayFun();

        if (!empty($data)) {
            \Log::DEBUG("call back:" . json_encode($data));

            if(!array_key_exists("transaction_id", $data)){
                $msg = "输入参数不正确";
                return false;
            }

            // 本站订单号
            $out_trade_no = $data['out_trade_no'];

            // 微信订单号
            $trade_no = $data['transaction_id'];

            // 订单金额
            $total_amount = isset($data['settlement_total_fee']) ? $data['settlement_total_fee']/100 : 0;

            // 实收金额
            $receipt_amount = $data['total_fee']/100;

            // 回调通知的发送时间
            $notify_time = strtotime($data['time_end']);

            //  支付时间
            $gmt_payment = strtotime($data['time_end']);

            //查询订单，判断订单真实性
            if(!$payModel->actionWxSmzfQueryorder($trade_no)){
                $msg = "订单查询失败";
                return false;
            }

            $hasRecord = SoftPdfOrder::find()
                ->where(['out_trade_no' => $out_trade_no])
                ->one();

            //成功处理逻辑
            if ($data['return_code'] == 'SUCCESS' && $data['result_code'] == 'SUCCESS') {

                // update
                if ($hasRecord && $hasRecord->pay_status != 1)
                {
                    $hasRecord->pay_status  = 1;
                    $hasRecord->notify_at = $notify_time;
                    $hasRecord->receipt_amount = $receipt_amount;
                    $hasRecord->trade_no = $trade_no;
                    $hasRecord->gmt_payment = $gmt_payment;
                    $hasRecord->pay_type_method = 4;
                    $hasRecord->pay_type = 'weixin';
                    if ($hasRecord->save()) {
                        $return = '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
                        return $return;
                    }else{
                        \Log::DEBUG("query:" . json_encode($hasRecord->getErrors()));
                    };
                }
            } elseif ($hasRecord && $data['result_code'] == 'FAIL' && $hasRecord->pay_status == 0) {
                $hasRecord->pay_status  = 2;
                if ($hasRecord->save()) {
                    $return = '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
                    return $return;
                }else{
                    \Log::DEBUG("query:" . json_encode($hasRecord->getErrors()));
                };
            }

            return true;
        }
    }
    

    
    
}