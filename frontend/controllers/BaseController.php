<?php
namespace frontend\controllers;

use yii\web\Controller;
use Yii;
use common\components\Aes;
use common\models\User;
use common\models\OauthThirdLogin;
use common\models\LoginForm;

class BaseController extends Controller 
{
    //初始化
    public function init()
    {
        parent::init();
    }
    
    //接收数据解密
    public function jiemi($data,$key=Aes::pdf){
        header("Content-type: text/html; charset=utf-8");
        $data = str_replace(' ', '+', $data);
        $dmodel = new Aes($key);
        return $dmodel->decrypt($data);
    }
    
    //返回清单加密
    public function jiami($data,$key=Aes::pdf) {
        $ase = new Aes($key);
        $str = ($ase->encrypt($data));
        return $str;
    }
    
    //软件跳转页面时登录
    public function softToWebLogin()
    {
        if (isset($_GET['utoken'])) {
            //token登录
            $time = time();
            $utoken = $this->jiemi($_GET['utoken']);
            
            $userM = User::find()
                ->select('id,login_token_expires')
                ->where("login_token = '$utoken'")
                ->one();
            
            if ($userM) {
                if ($userM->login_token_expires > $time) {
                    Yii::$app->user->login($userM);
                }
            }
        }

        if (!Yii::$app->user->isGuest && $_GET) {
            $url = Yii::$app->request->url;
            $url = substr($url,0,strpos($url, '?'));
            if (isset($_GET['headimg'])) {
                $urlParams = '?headimg='.$_GET['headimg'];
            } else {
                $urlParams = '';
            }
            $url = $url.$urlParams;
            
            return $this->redirect([$url]);
        }

    }

    
    
}