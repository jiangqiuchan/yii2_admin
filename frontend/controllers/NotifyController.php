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
    
    public function actionIndex()
    {
        $this->redirect(["/site2/index"]);
    }
    
    //微信登录回调
    public function actionWxLoginNotify()
    { 
        if(isset($_GET['code'])) {
            //其他域名回调登录
            $url = Yii::$app->request->get('referer_url');
            if ($url) {
                $res = $this->LoginRedirect($url, 'wx');
                if ($res != '1') {
                    return $this->redirect($res);
                }
            }

            $appId          = Yii::$app->params['weixinLogin']['APPID'];
            $appSecret      = Yii::$app->params['weixinLogin']['APPKEY'];
            $option         = ['appid' => $appId, 'appsecret' => $appSecret];
            $wexin          = new Wechat($option);
            $access_token   = $wexin->getOauthAccessToken();
            $autoLogin      = $_GET['state'];
        
            //检验授权凭证（access_token）是否有效
            $data = $wexin->checkAvail($access_token['access_token'],$access_token['openid']);
        
            if($data['errcode'] != '0' || $data['errmsg'] != 'ok'){
                //刷新access_token
                $access_token = $wexin->getOauthRefreshToken($access_token['refresh_token']);
            }
            
            //得到拉取用户信息
            $userData = $wexin->getOauthUserinfo($access_token['access_token'],$access_token['openid']);

            return $this->NotifyLogin('wx', $access_token['openid'],$userData,$autoLogin,$url);
        } else {
            //记录回调信息
            OauthThirdLogin::logOauthNotify();
        }
    }
    
    //qq登录回调
    public function actionQqLoginNotify()
    {
        if(isset($_GET['code'])) {
            //其他域名回调登录
            $url = Yii::$app->request->get('referer_url');
            if ($url) {
                $res = $this->LoginRedirect($url, 'qq');
                if ($res != '1') {
                    return $this->redirect($res);
                }
            }
            
            $qc = new \QC();
            $acs = $qc->qq_callback();
            $openid = $qc->get_openid();
            $qc = new \QC($acs,$openid);
            $uinfo = $qc->get_user_info();
            
            if ($uinfo) {
                $userData = [
                    'unionid' => '0',
                    'openid' => $openid,
                    'nickname' => $uinfo['nickname'],
                    'headimgurl' => isset($uinfo['figureurl_qq_2']) ? $uinfo['figureurl_qq_2'] : $uinfo['figureurl_qq_1'],
                ];
            }
            $autoLogin = $_GET['state'];            

            return $this->NotifyLogin('qq', $openid,$userData,$autoLogin,$url);
        } else {
            //记录回调信息
            OauthThirdLogin::logOauthNotify();
        }
    }
    
    //微信/qq登录
    public function actionOauthLogin()
    {
        $session = Yii::$app->session;
        $firstThirdLogin = $session->get('firstThirdLogin');
        if (!empty($firstThirdLogin)) {
            $unionid = $firstThirdLogin['unionid'];
            $openid = $firstThirdLogin['openid'];
            $nickname = $firstThirdLogin['nickname'];
            $headimgurl = $firstThirdLogin['headimgurl'];
            $registerType = $firstThirdLogin['logintype'];
            $btnType =  Yii::$app->request->post('btnType');
            $mobile = Yii::$app->request->post('mobile');
            $password = Yii::$app->request->post('password');
            $autoLogin = Yii::$app->request->post('rememberMe');
            $userObj = new User();
            
            if ($btnType == '1'){
                $thirdObj = new OauthThirdLogin();
                $thirdObj->type = $registerType;
                $thirdObj->openid = $openid;
                $thirdObj->unionid = $unionid;
                $thirdObj->headimgurl = $headimgurl;
                $thirdObj->save();
            
                $userObj = new User();
                $userObj->username  = Functions::userTextEncode($nickname);
                $userObj->mobile = $mobile;
                $userObj->referer = 'pc_pdf';
                $userObj->last_login_at = time();
                $userObj->setPassword($openid);
                $userObj->generateAuthKey();
                $userObj->save();

                $thirdObj->user_id = $userObj->id;
                $thirdObj->save();

                $uid = $userObj->id;
                
                //活动
//                 $post = Yii::$app->request->post();
//                 if ($post['referer'] == '2') {
//                     Act1::otherAddDrawTimes('2','pc',Yii::$app->params['actId'],$uid);
//                 }
            
                //用户名为空时给用户id
                if (trim($userObj->username) == '') {
                    $userObj->username = $uid;
                    $userObj->save();
                }
                
                $data = ['status' => 1];
            } else {
                $user = User::find()->where("mobile = $mobile")->one();

                if (empty($user)) {
                    $data = ['status' => 0, 'msg' => '该手机号还未注册'];
                    return json_encode($data);
                }else {
                    if ($user->validatePassword($password)) {
                        $thirdObj = new OauthThirdLogin();
                        $thirdObj->type = $registerType;
                        $thirdObj->openid = $openid;
                        $thirdObj->unionid = $unionid;
                        $thirdObj->user_id = $user->id;
                        $thirdObj->headimgurl = $headimgurl;
                        $thirdObj->save();
            
                        $user->mobile = $mobile;
                        $user->save();
            
                        $uid = $user->id;
            
                        $data = ['status' => 1];
                    } else {
                        $data = ['status' => -1, 'msg' => '登录密码错误'];
                        return json_encode($data);
                    }
                }
            }
            Yii::$app->user->login(User::findOne($uid),$autoLogin ? 3600 * 24 * 30 : 0);

            //删除用户缓存信息
            $session->remove('firstThirdLogin');
            
            return json_encode($data);
        } else {
            return false;
        }       
    }
    
    //回调登录
    private function NotifyLogin($type,$openid,$userData,$autoLogin,$url)
    {                
        $thirdLogin = OauthThirdLogin::find()->where("type = '$type' AND openid = :openid", [':openid' => $openid])->orderBy('id DESC')->one();
        $session = Yii::$app->session;
        
        if (!empty($thirdLogin)) {
            $userId = $thirdLogin->user_id;
            $user = User::findOne($userId);
            if ($user) {
                Yii::$app->user->login($user,$autoLogin ? 3600 * 24 * 30 : 0);        

                //更新用户信息
//                 if (!$user->mobile) {
//                     $thirdLogin->headimgurl = $userData['headimgurl'];
//                     $user->username = Functions::userTextEncode($userData['nickname']);
//                     $thirdLogin->save();
//                     $user->save();
//                 }
            }           
        } else {
            //存回调信息           
            $session->set('firstThirdLogin', [
                'unionid' => $userData['unionid'],
                'openid' => $userData['openid'],
                'nickname' => $userData['nickname'],
                'headimgurl' => $userData['headimgurl'],
                'logintype' => $type,
            ]);
        }
        
        if ($url) {
            return $this->redirect($url);
        } else {
            return $this->redirect(['site2/index']);
        }        
    }
    
    //其他域名回调登录
    private function LoginRedirect($url,$type)
    {
        $nowHost =Yii::$app->getRequest()->getHostInfo();
        $urlArr = parse_url($url);
        $urlHost = $urlArr['scheme'].'://'.$urlArr['host'];
        if ($nowHost != $urlHost) {
            $urlParams = Yii::$app->getRequest()->queryString;
            if ($type == 'wx') {
                $notifyStr = '/notify/wx-login-notify';
            } elseif ($type == 'qq') {
                $notifyStr = '/notify/qq-login-notify';
            }
            return $urlHost.$notifyStr.'?'.$urlParams;
        } else {
            return 1;
        }
    }
    
    //支付宝即时到账异步回调
    public function actionZfbJsdzNotify(){
        $alipay = new \AlipayPay();
        $time = time();
        ksort($_REQUEST);
        reset($_REQUEST);
        $sign = '';
        foreach ($_REQUEST AS $key=>$val)
        {
            if ($key != 'sign' && $key != 'sign_type' && $key != 'code')
            {
                $sign .= "$key=$val&";
            }
        }
        $data = json_encode($_REQUEST,JSON_UNESCAPED_UNICODE);
        $sign = substr($sign, 0, -1);
        $signmd5 = md5($sign . $alipay->key);
        $signsss = $_REQUEST['sign'];
        $GET = json_decode($data,TRUE);
         
        if($signmd5==$signsss){
            //  本站订单号
            $out_trade_no   = $GET['out_trade_no'];
    
            //  支付宝交易号
            $trade_no       = $GET['trade_no'];
    
            //  交易状态
            $trade_status   = $GET['trade_status'];
    
            //  订单金额
            $total_amount   = $GET['total_fee'];
    
            //  实收金额
            $receipt_amount = $GET['price'];
    
            //  回调通知的发送时间
            $notify_time    = $GET['notify_time'];
    
            //  支付时间
            $gmt_payment    = $GET['gmt_payment'];
    
            if ($GET['trade_status'] == 'TRADE_SUCCESS') {
                if ($GET['seller_id'] == $alipay->partner
                    && $receipt_amount != 0 && ($total_amount == $receipt_amount))
                {
                    $hasRecord = SoftPdfOrder::find()
                    ->where(['out_trade_no' => $out_trade_no])
                    ->one();
    
                    // update
                    if ($hasRecord && $hasRecord->pay_status != 1)
                    {
                        $hasRecord->pay_status  = 1;
                        $hasRecord->notify_at = strtotime($notify_time);
                        $hasRecord->receipt_amount = $receipt_amount;
                        $hasRecord->trade_no = $trade_no;
                        $hasRecord->gmt_payment = strtotime($gmt_payment);
                        if ($hasRecord->save()) {
    
                        }else{
    
    
                        };
                    }
                }
    
            }
    
            echo "success";
    
        } else {
            echo "fail";
    
        }
    }
    
    //支付宝即时到账同步回调
    public function actionZfbJsdzReturn(){
        $alipay = new \AlipayPay();
        $time = time();
        ksort($_REQUEST);
        reset($_REQUEST);
        $sign = '';
        foreach ($_REQUEST AS $key=>$val)
        {
            if ($key != 'sign' && $key != 'sign_type' && $key != 'code')
            {
                $sign .= "$key=$val&";
            }
        }
        $data = json_encode($_REQUEST,JSON_UNESCAPED_UNICODE);
        $sign = substr($sign, 0, -1);
        $signmd5 = md5($sign . $alipay->key);
        $signsss = $_REQUEST['sign'];
    
        if($signmd5==$signsss){
            //商户订单号
            $out_trade_no = htmlspecialchars($_GET['out_trade_no']);
    
            //收款方id
            $seller_id    = htmlspecialchars($_GET['seller_id']);
    
            //支付宝交易号
            $trade_no = $_GET['trade_no'];
    
            //交易状态
            $trade_status = $_GET['trade_status'];
    
            if($trade_status == 'TRADE_FINISHED' || $trade_status == 'TRADE_SUCCESS') {
                return $this->redirect(Yii::$app->urlManager->createUrl(["pdf-man/order"]))->send();
    
            } else {
                echo '<script>window.close();</script>';
            }
        } else {
            echo "fail";
    
        }
    }
    
    //支付宝即时到账退款回调--弃
    public function actionZfbJsdzRefundNotify(){
        $logHandler= new \CLogFileHandler("../web/logs/".date('Y-m-d').'.log');
        $log = \Log::Init($logHandler, 15);
        \Log::DEBUG("call back:" . json_encode($_POST));
        $errorM = new ErrorLog();
    
        $alipay = new \AlipayPay();
        $time = time();
        ksort($_REQUEST);
        reset($_REQUEST);
        $sign = '';
        foreach ($_REQUEST AS $key=>$val)
        {
            if ($key != 'sign' && $key != 'sign_type' && $key != 'code')
            {
                $sign .= "$key=$val&";
            }
        }
        $data = json_encode($_REQUEST,JSON_UNESCAPED_UNICODE);
        $sign = substr($sign, 0, -1);
        $signmd5 = md5($sign . $alipay->key);
        $signsss = $_REQUEST['sign'];
        $GET = json_decode($data,TRUE);
         
        if($signmd5==$signsss){
            //批次号
            $batch_no = $_POST['batch_no'];
    
            //批量退款数据中转账成功的笔数
            $success_num = $_POST['success_num'];
    
            //批量退款数据中的详细信息
            $result_details = $_POST['result_details'];
             
            //交易号
            $trade_no = substr($result_details,0,strpos($result_details, '^'));
            $orderM = SoftPdfOrder::find()->where("trade_no = '$trade_no' AND pay_type = 'alipay'")->one();
            if ($orderM) {
                $orderM->pay_status = 9;
                 
                $refundM = SoftPdfRefund::find()->where("order_id = '$orderM->id' AND batch_no = '$batch_no'")->one();
                if ($refundM && $orderM->save()) {
                    $refundM->state = 1;
                    if(!$refundM->save()) {
                        ErrorLog::logError(json_encode($refundM->getErrors()));
                    }
                } else {
                    ErrorLog::logError(json_encode($orderM->getErrors()));
                    \Log::DEBUG("call back:" . 'alipay notify error(SoftPdfOrder)');
                }
            } else {
                \Log::DEBUG("call back:" . 'alipay notify error(SoftPdfRefund)');
            }
    
            echo "success";
        } else {
            ErrorLog::logError(json_encode($_REQUEST));
            echo "fail";
        }
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
            $payModel = new PayFun();
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