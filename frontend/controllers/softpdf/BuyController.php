<?php
namespace frontend\controllers\softpdf;

use common\functions\OrderFunctions;
use Yii;
use frontend\controllers\SoftBaseController;

require_once "../../vendor/WxPay/WxPay.Api.php";
require_once "../../vendor/WxPay/WxPay.NativePay.php";
require_once "../../vendor/WxPay/log.php";


//version2
class BuyController extends SoftBaseController
{
    //购买页
    public function actionIndex()
    {
        //登录
        if (isset($_GET['utoken'])) {
            $this->softToWebLogin();
        } else {
            if (!Yii::$app->user->isGuest) {
                $userId = Yii::$app->user->id;
                $userData = OrderFunctions::getWebTopicUserData($userId);
                $img = $userData['headimgurl'];

                $expireTime = OrderFunctions::getExpireTime($userId,$where="package<>11");

                if (!empty($expireTime)) {
                    $str = '';
                    if ($expireTime != 100) {
                        if ($expireTime < time()) {
                            $str = '<i class="is-gq">（已过期）</i>';
                        }
                        $expireTime = date("Y-m-d H:i:s",$expireTime).$str;
                    } else {
                        $expireTime = '永久';
                    }
                }

                return $this->render('buy',[
                    'expireTime' => $expireTime,
                    'img' => $img
                ]);
            } else {
                echo '登录失败';
            }
        }
    }
    
    //二维码页
    public function actionQrCode()
    {
        if (isset($_GET['itemType']) && $_GET['payType']) {
            
            $page = $_GET['payType'] == 'weixin' ? 'wx' : 'zfb';
            
            return $this->render($page,[
                'itemType' => $_GET['itemType'],
                'payType' => $_GET['payType'],
            ]);
        }
    }
    
    //支付完成页
    public function actionPaid()
    {

        return $this->render('zfok',[

        ]);
    }

    
    
}