<?php
namespace frontend\controllers;

use common\models\FreeManConvert;
use yii\web\Controller;
use Yii;
use frontend\models\SoftPdfOrder;
use common\functions\Functions;
use common\components\PayFun;
use frontend\models\activity\Act2;
use common\functions\OrderFunctions;

require_once "../../vendor/WxPay/WxPay.Api.php";
require_once "../../vendor/WxPay/WxPay.NativePay.php";
require_once "../../vendor/WxPay/log.php";


//version2
class PayController extends Controller
{
    public $enableCsrfValidation = false;
    
    function  init(){
		//初始化日志
        $logHandler= new \CLogFileHandler("../web/logs/".date('Y-m-d').'.log');
        $log = \Log::Init($logHandler, 15);
    }

    public function actionPay()
    {
        $outTradeNo = date('Ymd').time().rand(10000,99999);
        $package = $_POST['itemtype'];
        $moneyData = OrderFunctions::getPackageMoney($package);
        //活动2优惠
        $saleType = Yii::$app->request->post('saleType',0);
        $drawId = 0;
        
        if ($moneyData['status'] == '1') {
            $money = $moneyData['money'];
            
            //活动2，获取优惠后价格
            $prizeId = Act2::getPrizeId($saleType);         
            if ($saleType && $prizeId) {
                $actData = Act2::getMoney($saleType, $money, $package);
                $money = $actData['data']['money'];
                $drawId = isset($actData['data']['drawId']) ? $actData['data']['drawId'] : 0;
            }
            
            $userId = $_POST['user_id'];
            $payType = $_POST['pay_type'];
            $startTime = OrderFunctions::getExpireStartTime($userId,$referer="pdf",$where="package<>11");
            $expireTime = OrderFunctions::getPackageExpireTime($package,$userId,$startTime);
            
            //创建支付订单
            $payTypeMethod = $payType == 'alipay' ? '1' : '2';
            //活动2
            $return = OrderFunctions::createOrder($money, $outTradeNo, $userId, $payType, $package, $startTime, $expireTime, $payTypeMethod,$drawId,$prizeId);
            if ($return['status'] != '1') {
                return $return['data'];
            } else {
                $order = $return['data'];
            }
            
            $payModel = new PayFun();
            if ($payType == 'alipay') {
                $res = $payModel->zfbJsdz($outTradeNo, $money);
                return $res;
            } else {
                $res = $payModel->wxSmzf($outTradeNo, $money, $package,$order,$type = 1);
                return $res;
            }
        }        
    }
    
    //微信支付回调
    public function actionNotify()
    {
        $package = 6;
        $user_id = 2;
        $relate_id = 2;
        FreeManConvert::addFreeTimes($package,$user_id,$relate_id);
die;
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
    
    //微信订单支付情况
    public function actionWxPayStatus()
    {
        $post = Yii::$app->request->post();
        $orderId = $post['orderId'];
        $data = ['orderId' => ''];
    
        $isExsit = SoftPdfOrder::find()->where("out_trade_no = '$orderId'")->one();
        if ($isExsit && $isExsit->pay_status == '1') {
            $data = ['status' => 1];
        } elseif ($isExsit && $isExsit->pay_status == '2') {
            $data = ['status' => 2,'msg'=>'订单支付失败'];
        }
    
        return json_encode($data);
    }

	//输出二维码    
    public function actionToQrcode($data,$size = 5,$margin = 0.7)
    {
        return Functions::getQrcode($data, false, 'L', $size, $margin);
    }
}