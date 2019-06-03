<?php
namespace frontend\controllers\softpdf;

use common\functions\OrderFunctions;
use Yii;
use frontend\controllers\SoftBaseController;
use backend\models\SoftPdfOrder;

require_once "../../vendor/WxPay/WxPay.Api.php";
require_once "../../vendor/WxPay/WxPay.NativePay.php";
require_once "../../vendor/WxPay/log.php";


//version2
class Buy2Controller extends SoftBaseController
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
                        $expireTime = date("Y-m-d",$expireTime).$str;
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
    
    //支付完成页
    public function actionPaid($param)
    {
        $isExsit = SoftPdfOrder::find()->where("out_trade_no = '$param'")->one();
        if ($isExsit) {
            $order = [];
            $order['from'] = date('Y-m-d',$isExsit->created_at);
            $order['to'] = $isExsit->expire_time == '100' ? '永久' : date('Y-m-d',$isExsit->expire_time);
            $packageArr = Yii::$app->params['payPackage'];
            $order['long'] = $packageArr[$isExsit->package];

            return $this->render('zfok',[
                'order' => $order
            ]);
        } else {
            echo "获取支付信息失败";
        }
    }

    
    
}