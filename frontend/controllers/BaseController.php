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
    
    //校验token
    public function checkToken($str) {
        $key = 'PXS69OwvOeeAS6fU32rUUA';
        $time = time();
        $data = $this->jiemi($str);
        $return = ['status' => '1','msg' => '校验成功'];

        if ($data) {
            $dataArr = explode('&', $data);
            $acceptTime = $dataArr[1];

            if ($time - $acceptTime > 300) {
                $return = ['status' => '0','msg' => '校验超时'.'-服务器时间：'.$time.'-发送时间：'.$acceptTime];
                return $return;
            }
            
            $myStr = $this->jiami($key.'&'.$acceptTime);
            if ($str == $myStr) {
                return $return;
            } else {
                $return = ['status' => '0','msg' => '字符串校验失败'.'-接收：'.$str.'-本地：'.$myStr];
                return $return;
            }
        } else {
            $return = ['status' => '0','msg' => '解密失败'.'：'.$str];
            return $return;
        }
    }
    
    //使用openid登录
    public function OpenidLogin($type,$openid)
    {
        $oauth = OauthThirdLogin::find()->where("type = '$type' AND openid = :openid", [':openid' => $openid])->orderBy('id DESC')->one();
    
        if ($oauth) {
            $user = User::findOne($oauth->user_id);
            if ($user) {
                Yii::$app->user->login($user);
            } else {
                $this->actionLogout();
            }
        }
        return $oauth;
    }
    
    //软件跳转页面时登录
    public function softToWebLogin()
    {
        if (isset($_GET['openid']) && isset($_GET['type'])) {
            $type = $this->jiemi(Yii::$app->request->get('type'));
            $openid = $this->jiemi(Yii::$app->request->get('openid'));
            $this->OpenidLogin($type, $openid);
        
            $oauth = OauthThirdLogin::find()->where("openid = '$openid'")->one();

        } elseif (isset($_GET['mobile']) && isset($_GET['password'])) {
            $post['mobile'] = $this->jiemi(Yii::$app->request->get('mobile'));
            $post['password'] = $this->jiemi(Yii::$app->request->get('password'));

            if ($post['password']) {
                $model = new LoginForm();
                $post['LoginForm'] = $post;
        
                $model->load($post) && $model->login();
            } else {
                $model = User::findByUsername($post['mobile']);
                if ($model) {
                    Yii::$app->user->login($model);
                }
            }
        } elseif (isset($_GET['utoken'])) {
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