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
        $referer = Yii::$app->request->post('referer',0);

        //活动2优惠
        $saleType = Yii::$app->request->post('saleType',0);
        $drawId = 0;

        $outTradeNo = date('Ymd').time().rand(10000,99999);

        $moneyData = OrderFunctions::getPackageMoney($package,$referer);

        if ($package == 66) {
            $package = 6;
            $referer = 'pdf,ocr';
        }
        
        if ($moneyData['status'] == '1') {
            $money = $moneyData['money'];
            //活动2，获取优惠后价格
            $prizeId = Act2::getPrizeId($saleType);
            if ($saleType && $prizeId) {
                $actData = Act2::getMoney($saleType, $money, $package);
                $money = $actData['data']['money'];
                $drawId = isset($actData['data']['drawId']) ? $actData['data']['drawId'] : 0;
            }

            $startTime = OrderFunctions::getExpireStartTime($userId,$referer,$where="package<>11");
            $expireTime = OrderFunctions::getPackageExpireTime($package,$userId,$startTime);
            
            //创建支付订单
            $payTypeMethod = $payType == 'alipay' ? '3' : '4';
            //活动2
            $return = OrderFunctions::createOrder($money, $outTradeNo, $userId, $payType, $package, $startTime, $expireTime, $payTypeMethod,$drawId,$prizeId,$referer);
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

//二合一版支付---------------------------------------------------------------------------------------------------
    //获取接口二维码
    public function actionGetEwm2()
    {
        //生成订单
        $userId = Yii::$app->user->id;
        if ($userId) {
            $package = Yii::$app->request->post('itemtype',3);
            $payType = Yii::$app->request->post('pay_type','alipay');
            $referer = Yii::$app->request->post('referer',0);

            $outTradeNo = date('Ymd').time().rand(10000,99999);

            $moneyData = OrderFunctions::getPackageMoney($package,'test');

            if ($package == 66) {
                $package = 6;
                $referer = 'pdf,ocr';
            }

            if ($moneyData['status'] == '1') {
                $money = $moneyData['money'];

                $startTime = OrderFunctions::getExpireStartTime($userId,$referer,$where="package<>11");
                $expireTime = OrderFunctions::getPackageExpireTime($package,$userId,$startTime);

                //创建支付订单
                $payTypeMethod = '3';
                //活动2
                $return = OrderFunctions::createOrder($money, $outTradeNo, $userId, $payType, $package, $startTime, $expireTime, $payTypeMethod,0,0,$referer,1);
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
//                if (!isset($_GET['code'])){
//                    //触发微信返回code码
//                    $baseUrl = urlencode('http://'.$_SERVER['HTTP_HOST']);
//                    $url = "https://openauth.alipay.com/oauth2/publicAppAuthorize.htm?app_id=2017121900967489&scope=auth_base&redirect_uri=http://192.168.3.1:88";
////                    var_dump($url);die;
//                    Header("Location: $url");
//                    exit();
//                } else {
//                    //获取code码，以获取openid
//                    $code = $_GET['code'];var_dump($_GET);die;
//                    $openid = $this->getOpenidFromMp($code);
//                    return $openid;
//
////                    header("location:$url");
//                }

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