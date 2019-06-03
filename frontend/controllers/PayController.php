<?php
namespace frontend\controllers;

use yii\web\Controller;
use Yii;
use frontend\models\SoftPdfOrder;
use common\functions\Functions;
use common\components\PayFun;
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