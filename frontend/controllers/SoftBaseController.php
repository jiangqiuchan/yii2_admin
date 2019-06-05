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

    //第三方支付
    public function actionPay()
    {
        return $this->getOrder1();
    }

    //刷新二维码
    public function actionRefreshOrder()
    {
        return $this->getOrder2();
    }

    //软件支付--生成订单
    private function getOrder1()
    {
        $userId = Yii::$app->request->post('user_id',0);
        $package = Yii::$app->request->post('itemtype',0);
        $payType = Yii::$app->request->post('pay_type',0);

        //活动2优惠
        $saleType = Yii::$app->request->post('saleType',0);
        $drawId = 0;

        $outTradeNo = date('Ymd').time().rand(10000,99999);

        $moneyData = OrderFunctions::getPackageMoney($package);

        if ($moneyData['status'] == '1') {
            $money = $moneyData['money'];

            $startTime = OrderFunctions::getExpireStartTime($userId,$where="package<>11");
            $expireTime = OrderFunctions::getPackageExpireTime($package,$userId,$startTime);

            //创建支付订单
            $payTypeMethod = $payType == 'alipay' ? '3' : '4';
            //活动2
            $return = OrderFunctions::createOrder($money, $outTradeNo, $userId, $payType, $package, $startTime, $expireTime, $payTypeMethod,$drawId);
            if ($return['status'] != '1') {
                return $return['data'];
            } else {
                $order = $return['data'];
            }

            //生成二维码
            return $this->getEwm($payType, $outTradeNo, $money, $package, $order);
        }
    }

    //软件支付--刷新订单二维码
    private function getOrder2()
    {
        $orderId = Yii::$app->request->post('orderId',0);
        $order = SoftPdfOrder::find()->where("out_trade_no = '$orderId'")->one();
        if($order) {
            $payType = $order->pay_type;
            $outTradeNo = $order->out_trade_no;
            $money = $order->money;
            $package = $order->package;

            //生成二维码
            return $this->getEwm($payType, $outTradeNo, $money, $package, $order);
        }
    }

    //生成二维码返回
    private function getEwm($payType,$outTradeNo,$money,$package,$order)
    {
        $payModel = new PayFun();
        if ($payType == 'alipay') {
            $res = $payModel->zfbDmf1($outTradeNo, $money);
            return $res;
        } else {
            $res = $payModel->wxSmzf($outTradeNo, $money, $package,$order);
            return $res;
        }
    }

}