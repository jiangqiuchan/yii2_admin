<?php
namespace frontend\controllers;

use Yii;
use common\components\PayFun;
use frontend\models\activity\Act2;
use common\functions\OrderFunctions;
use backend\models\SoftPdfOrder;

require_once "../../vendor/WxPay/WxPay.Api.php";
require_once "../../vendor/WxPay/WxPay.NativePay.php";
require_once "../../vendor/WxPay/log.php";

class SoftBaseController extends BaseController
{
    public $enableCsrfValidation = false;
    public $layout = false;
    public $userId;
    
    function  init(){
        //初始化日志
        $logHandler= new \CLogFileHandler("../web/logs/".date('Y-m-d').'.log');
        $log = \Log::Init($logHandler, 15);
    
        //登录用户
        if (!Yii::$app->user->isGuest) {
            $this->userId = Yii::$app->user->id;
        } else {
            $this->userId = 0;
        }
    }

//二合一版支付---------------------------------------------------------------------------------------------------
    //获取接口二维码
    public function actionGetEwm2()
    {
        //生成订单
        $userId = Yii::$app->user->id;
        if ($userId) {
            $package = Yii::$app->request->post('itemtype',3);
            $payType = Yii::$app->request->post('pay_type','alipay');

            $outTradeNo = date('Ymd').time().rand(10000,99999);

            $moneyData = OrderFunctions::getPackageMoney($package,'test');

            if ($moneyData['status'] == '1') {
                $money = $moneyData['money'];

                $startTime = OrderFunctions::getExpireStartTime($userId,$where="package<>11");
                $expireTime = OrderFunctions::getPackageExpireTime($package,$userId,$startTime);

                //创建支付订单
                $payTypeMethod = '3';
                //活动2
                $return = OrderFunctions::createOrder($money, $outTradeNo, $userId, $payType, $package, $startTime, $expireTime, $payTypeMethod,0,0,1);
                if ($return['status'] != '1') {
                    return $return['data'];
                } else {
                    $url = "http://pdf.66zip.cn/soft-base/pay2?out_trade_no=".$outTradeNo;
//                $url = "http://192.168.3.1:88/soft-base/pay2?out_trade_no=".$outTradeNo;
                    $img = "http://pdf.66zip.cn/pay/to-qrcode?data=".urlencode($url);
                    $data = [];
                    $data['status'] = 1;
                    $data['orderid'] = $outTradeNo;
                    $data['img'] = $img;

                    return json_encode($data);
                }
            }
        } else {
            $data = ['status' => '0','msg' => '获取登录信息失败'];
            return json_encode($data);
        }
    }

    //扫码支付
    public function actionPay2()
    {
        $outTradeNo = Yii::$app->request->get('out_trade_no','');

        $payModel = new PayFun();
        $order = SoftPdfOrder::find()->where("out_trade_no = '$outTradeNo'")->one();

        if ($order) {
            $money = $order->money;
            $package = $order->package;
            $data = ['out_trade_no' => $outTradeNo,'money' => $money];

            if (strpos($_SERVER['HTTP_USER_AGENT'], 'MicroMessenger')) {
                $arr = $payModel->wxJszf($outTradeNo,$money,$package);
                $jsApiParameters = $arr['jsApiParameters'];
                $editAddress = $arr['editAddress'];

                return $this->renderPartial('/softpdf/buy2/wx',[
                    'jsApiParameters' => $jsApiParameters,
                    'editAddress' => $editAddress,
                    'data' => $data
                ]);
            } else {
                $url = $payModel->zfbDmf1Url($outTradeNo, $money);

                header("location:$url");
            }
        }
    }

    //刷新二维码
    public function actionRefreshOrder2()
    {
        $orderId = Yii::$app->request->post('orderId',0);
        $order = SoftPdfOrder::find()->where("out_trade_no = '$orderId'")->one();
        if($order) {
            $outTradeNo = $order->out_trade_no;

            //生成二维码
            $url = "http://pdf.66zip.cn/soft-base/pay2?out_trade_no=".$outTradeNo;
            $img = "http://pdf.66zip.cn/pay/to-qrcode?data=".urlencode($url);
            $data = [];
            $data['status'] = 1;
            $data['orderid'] = $outTradeNo;
            $data['img'] = $img;

            return json_encode($data);
        }
    }

}