<?php
namespace frontend\controllers;
use common\models\Activity;
use common\models\SoftUpdateList;
use yii;
use backend\components\Update;
use backend\models\YdqPoint;
use backend\models\ZhqPoint;
use common\models\LoginForm;
use common\models\User;
use common\functions\Functions;
use frontend\models\SignupForm;
use common\models\OauthThirdLogin;
use common\components\Wechat;
use common\models\UninstallFeedback;
use common\models\FunctionFeedback;
use backend\models\SoftUpdate;
use common\models\SoftActCode;
use common\functions\OrderFunctions;
use common\models\OcrPoint;
use common\models\OcrMac;
use backend\models\SoftPdfOrder;
use common\components\PayFun;

require_once("../../common/components/QQAPI/qqConnectAPI.php");

class ApiController extends ApiBaseController
{
    public function init()
    {
        $this->enableCsrfValidation = false;
    }


    /**
     * 打点 post /api/point
     * @para string mac      机器码
     * @para string step     步骤码
     * @para number user_id  用户id
     * @para number version  版本号
     * @para string channel  渠道标识
     * @para number err      错误码(可选)
     */
    public function actionPoint() {
        if(yii::$app->request->isPost){
            $default = "RwQfCLqKAYo/atg4SL0CTQ==";//0
            $mac = $this->jiemi(yii::$app->request->post("mac",$default));
            $user_id = $this->jiemi(yii::$app->request->post("sid",$default));
            $step = $this->jiemi(yii::$app->request->post("step",$default));//步骤
            $version = $this->jiemi(yii::$app->request->post("version",$default));//版本
            $channel = $this->jiemi(yii::$app->request->post("channel",$default));//渠道
            $err = $this->jiemi(yii::$app->request->post("err",$default));//错误码
            $time = time();

            $models = new ZhqPoint();
            $id = ZhqPoint::find()->where("mac='$mac' AND is_new='1'")->asArray()->limit(1)->one();
            $is_new = empty($id) ? 1 : 0;

            $models->mac = $mac;
            $models->step = $step;
            $models->user_id = $user_id ? $user_id : '0';
            $models->version = $version;
            $models->channel = $channel;
            $models->err = $err;
            $models->time = $time;
            $models->is_new = $is_new;

            echo $models->save()?"succeed":"error";
        }else{
            echo "error!";
        }
    }
 
    /**
     * PDF 客户端返回码
         0 失败
         1 成功
         2 用户未登录
        -1 密码重置失败
        -2 请输入正确的手机号码格式
        -3 该手机号已注册
        -4 手机验证码不正确或已过期
        -5 用户不存在
        -6 该手机号还未注册
        -7 短信发送失败
        -8 密码不正确
        -9 请输入正确的密码格式
        -10 用户已登录
        -11 原密码错误
        -12 两次密码不一致
     */
    /**
     * 登录（手机+密码/手机+验证码） post /api/login
     * @para string mobile      手机号
     * @para string password    密码
     * @para string channel     渠道标识
     * @para number captcha     验证码
     */
    public function actionLogin() {    
        $post['mobile'] = $this->jiemi(Yii::$app->request->post('mobile'));
        $post['password'] = $this->jiemi(Yii::$app->request->post('password'));
        $post['channel'] = $this->jiemi(Yii::$app->request->post('channel'));
        $post['captcha'] = $this->jiemi(Yii::$app->request->post('captcha'));
        $data = 'status=0';

        $loginSucc = 0;
        $isExsitMob = User::isExsitMob($post['mobile']);
        
        if ($post['mobile'] && $post['password']) {
            if (!$isExsitMob) {
                $data = 'status=-6';
                return $this->jiami($data);
            }

            //密码登录
            $model = new LoginForm();
            $post['LoginForm'] = $post;

            if ($model->load($post) && $model->login()) {
                $loginSucc = 1;
            } else {
                $data = 'status=-8';
                return $this->jiami($data);
            }
            
        } elseif ($post['mobile'] && $post['captcha']) {
            //验证码登录
            $isCaptcha = Functions::checkCaptcha($post['mobile'],$post['captcha'],2);
            if (!$isCaptcha) {
                $data = 'status=-4';
                return $this->jiami($data);
            }
            
            if (!$isExsitMob) {
                //验证码注册
                $model = new SignupForm();
                $post['username'] = $post['mobile'];
                $post['channel'] = $post['channel'] ? $post['channel'] : null;
                $post['type'] = 'captcha';
                $signupForm['SignupForm'] = $post;
                $model->load($signupForm);

                if ($user = $model->signup()) {
                    //修改验证码状态
                    Functions::updateCaptcha($post['mobile'], $post['captcha'],2);
                
                    if (Yii::$app->getUser()->login($user)) {
                        $loginSucc = 1;
                    } else {
                        $data = 'status=0';
                        return $this->jiami($data);
                    }
                
                } else {
                    $data = 'status=-9';
                }
            } else {                
                $model = User::findByUsername($post['mobile']);
                if ($model) {
                    if (Yii::$app->user->login($model)) {
                        $loginSucc = 1;
                    } else {
                        $data = 'status=0';
                        return $this->jiami($data);
                    }
                }
            }
        }
        
        if ($loginSucc) {
            $userId = Yii::$app->user->id;
            
            //获取登录token
            $this->getUserToken($userId);
            
            //获取用户与订单信息
            $user = OrderFunctions::getApiUserOrderData($userId);

            $userInfo = urldecode(http_build_query($user));
            $data = 'status=1&'.$userInfo;
        }
        
        return $this->jiami($data);
    }

    /**
     * 手机号注册 post /api/signup
     * @para string mobile      手机号
     * @para string password    密码
     * @para string channel     渠道标识
     * @para number captcha     验证码
     */
    public function actionSignup() {
        $post['mobile'] = $this->jiemi(Yii::$app->request->post('mobile'));
        $post['captcha']  = $this->jiemi(Yii::$app->request->post('captcha'));
        $post['password']  = $this->jiemi(Yii::$app->request->post('password'));
        $post['channel']  = $this->jiemi(Yii::$app->request->post('channel'));

        // 验证手机号
        $isTel = Functions::isMoblie($post['mobile']);

        if(!$isTel){
            $data = 'status=-2';
            return $this->jiami($data);
        }
        
        if (!preg_match('/^[0-9a-zA-Z]{6,}$/',$post['password'])) {
            $data = 'status=-9';
            return $this->jiami($data);
        }

        $isExsitMob = User::isExsitMob($post['mobile']);
        if ($isExsitMob) {
            $data = 'status=-3';
            return $this->jiami($data);
        } else {
            $model    = new SignupForm();
            $post['username'] = $post['mobile'];
            $post['channel'] = $post['channel'] ? $post['channel'] : null;
            $post['type'] = 'password';
            $signupForm['SignupForm'] = $post;
            $model->load($signupForm);

            $isCaptcha = Functions::checkCaptcha($post['mobile'],$post['captcha'],0);
            
            if ($isCaptcha) {
                if ($user = $model->signup()) {
                    //修改验证码状态
                    Functions::updateCaptcha($post['mobile'], $post['captcha']);
                
                    if (Yii::$app->getUser()->login($user)) {
                            $data = 'status=1';
                    }

                } else {
                    $data = 'status=-9';
                }
            } else {
                $data = 'status=-4';
            }
            
            return  $this->jiami($data);
        }
    }

    /**
     * 发送验证码 post /api/send-msg
     * @para string mobile      手机号
     * @para number type        类型 0注册 1找回密码 2登录注册
     */
    public function actionSendMsg() {    
        $mobile = $this->jiemi(Yii::$app->request->post('mobile'));
        $type  = $this->jiemi(Yii::$app->request->post('type'));
        
        $isEnviron  =  Yii::$app->params['isOnline'];
        $captcha = $isEnviron ? rand(1000, 9999) : '1111';
        $time = time();
        $expire_time = $time + 300;
        
        if (empty($mobile) || !Functions::isMoblie($mobile)) {
            $data = 'status=-2';
            return $this->jiami($data);
        }

        $isExsitMob = User::isExsitMob($mobile);
        if ($type == 1) {
            if (!$isExsitMob) {
                $data = 'status=-6';
                return $this->jiami($data);
            }
        } elseif ($type == 0) {
            if ($isExsitMob) {
                $data = 'status=-3';
                return $this->jiami($data);
            }
        }
        
        //开始发送短信验证码
        $output = Functions::captcha($mobile, $captcha);
        if ($output->code == 0 || !$isEnviron){
            //入库
            $sql    = "INSERT INTO {{%soft_pdf_mobile_captcha}} (`mobile`, `captcha`, `expire_time`,`type`,`created_at`)
            VALUES ('$mobile', '$captcha','$expire_time','$type','$time')";
        
            $return = Yii::$app->db->createCommand($sql)->execute();
            $data = 'status=1';
        }else{
            $data = 'status=-7';
        }
        return $this->jiami($data);
    }

    /**
     * 找回密码--下一步（验证验证码） post /api/check-captcha
     * @para string mobile      手机号
     * @para number type        类型 0注册 1找回密码
     * @para number captcha      验证码
     */
    public function actionCheckCaptcha(){
        $mobile = $this->jiemi(Yii::$app->request->post('mobile'));
        $type = $this->jiemi(Yii::$app->request->post('type'));
        $captcha = $this->jiemi(Yii::$app->request->post('captcha'));

        if ($type == 1) {
            $isExsitMob = User::isExsitMob($mobile);
            if (!$isExsitMob) {
                $data = 'status=-6';
                return $this->jiami($data);
            }
        }
        
        $checkCap = Functions::checkCaptcha($mobile,$captcha,$type);
        if (!$checkCap) {
            $data = 'status=-4';
            return $this->jiami($data);
        }

        $data = 'status=1';
        return $this->jiami($data);
    }

    /**
     * 找回密码--设置新密码 post /api/reset-password
     * @para string mobile       手机号
     * @para string password     密码
     */
    public function actionResetPassword()
    {
        $mobile = $this->jiemi(Yii::$app->request->post('mobile'));
        $password = $this->jiemi(Yii::$app->request->post('password'));

        if (!preg_match('/^[0-9a-zA-Z]{6,}$/',$password)) {
            $data = 'status=-9';
            return $this->jiami($data);
        }
    
        $user = User::find()->where("mobile = $mobile")->one();
        $user->setPassword($password);
    
        if ($user->save()) {
            $data = 'status=1';
        } else {
            $data = 'status=-1';
        }
        return $this->jiami($data);
    }

    /**
     * 获取用户订单 post /api/get-expire-time
     * @para number user_id       用户id
     */
    public function actionGetExpireTime()
    {
        $userId = $this->jiemi(Yii::$app->request->post('user_id'));

        //获取用户订单信息
        $order = OrderFunctions::getOrderModel($userId, $where="package<>11");
        $expire_time = OrderFunctions::getApiExpireTimeStr($order);
        
        //套餐名称
        $arr = SoftPdfOrder::getPackageArr(1);
        $package = $order ? (isset($arr[$order->package]) ? $arr[$order->package] : '') : '';
        
        $data = "status=1&expire_time=$expire_time&package=$package";
        
        return $this->jiami($data);
    }
    
    /**
     * 用户退出登录
     * @para number user_id      用户id
     */
    public function actionLogout()
    {
        $userId = $this->jiemi(Yii::$app->request->post('user_id'));
    
        $user = User::findOne($userId);
        if ($user) {
            if ($user->is_online == 0) {
                $data = "status=2";
            } else {
                $user->is_online = 0;
                $data = 'status='.($user->save(false) ? '1' : '0');
            }
        } else {
            $data = "status=-5";
        }
            
        return $this->jiami($data);
    }
    
    /**
     * PDF 获取第三方登录地址20180907
     * @para string type         类型 wx qq
     * @para string channel      渠道
     * @para string referer      pdf,ocr 20180912
     */
    public function actionGetLoginUrl()
    {
        $type = $this->jiemi(Yii::$app->request->post('type'));
        $channel = $this->jiemi(Yii::$app->request->post('channel'));
        $referer = $this->jiemi(Yii::$app->request->post('referer','k53OKGy0PtTIlGqP3/cDMw=='));
        $url = '';
        
        if ($type && $channel) {
            $redirectUri = urlEncode("http://pdf.66zip.cn/api-pdf/".$type."-login-notify?channel=".$channel."&referer=".$referer);            
            
            if ($type == 'wx') {
                $url = "https://open.weixin.qq.com/connect/qrconnect?appid=wx191ee548485d286c&redirect_uri=".$redirectUri."&response_type=code&scope=snsapi_login&state=STATE#wechat_redirect";
            } elseif ($type == 'qq') {
                $url = "https://graph.qq.com/oauth2.0/authorize?response_type=code&client_id=101470612&redirect_uri=".$redirectUri."&scope=get_user_info&state=STATE";
            }
        }
        
        return $this->jiami($url);
    }
    
    /**
     * PDF 微信登录回调
     * @para string channel      渠道20180510
     */
    public function actionWxLoginNotify()
    {
        if(isset($_GET['code'])) {
            $appId          = Yii::$app->params['weixinLogin']['APPID'];
            $appSecret      = Yii::$app->params['weixinLogin']['APPKEY'];
            $option         = ['appid' => $appId, 'appsecret' => $appSecret];
            $wexin          = new Wechat($option);
            $access_token = $wexin->getOauthAccessToken();
    
            //检验授权凭证（access_token）是否有效
            $data = $wexin->checkAvail($access_token['access_token'],$access_token['openid']);
    
            if($data['errcode'] != '0' || $data['errmsg'] != 'ok'){
                //刷新access_token
                $access_token = $wexin->getOauthRefreshToken($access_token['refresh_token']);
            }
    
            //得到拉取用户信息
            $userData = $wexin->getOauthUserinfo($access_token['access_token'],$access_token['openid']);

            $channel = Yii::$app->request->get('channel');
            $referer = Yii::$app->request->get('referer','pdf');
            //回调登录
            return $this->NotifyLogin('wx', $access_token['openid'],$userData,$channel,$referer);
        } else {
            //记录回调信息
            OauthThirdLogin::logOauthNotify();
        }
    }
    
    /**
     * PDF qq登录回调
     */
    public function actionQqLoginNotify()
    {
        if(isset($_GET['code'])) {
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

            $channel = Yii::$app->request->get('channel');
            $referer = Yii::$app->request->get('referer','pdf');
            //回调登录
            return $this->NotifyLogin('qq', $openid,$userData,$channel,$referer);
        } else {
            //记录回调信息
            OauthThirdLogin::logOauthNotify();
        }
    }
    
    
    /**
     * PDF 第三方登录
     * @para int btnType         类型 1直接登录 2绑定登录
     * @para string mobile       手机号
     * @para string password     密码
     * @para string channel      渠道20180510
     * @para string referer       pdf,ocr 20180912
     */
    public function actionOauthLogin()
    {
        $channel = $this->jiemi(Yii::$app->request->post('channel'));
        $referer = $this->jiemi(Yii::$app->request->post('referer','k53OKGy0PtTIlGqP3/cDMw=='));
        
        $session = Yii::$app->session;
        $firstThirdLogin = $session->get('firstThirdLogin');
        if (!empty($firstThirdLogin)) {
            $unionid = $firstThirdLogin['unionid'];
            $openid = $firstThirdLogin['openid'];
            $nickname = $firstThirdLogin['nickname'];
            $headimgurl = $firstThirdLogin['headimgurl'];
            $registerType = $firstThirdLogin['logintype'];
            $btnType =  $this->jiemi(Yii::$app->request->post('btnType'));
            $mobile = empty(Yii::$app->request->post('mobile')) ? '' : $this->jiemi(Yii::$app->request->post('mobile'));
            $password = empty(Yii::$app->request->post('password')) ? '' : $this->jiemi(Yii::$app->request->post('password'));
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
                $userObj->channel = $channel ? $channel : null;
                $userObj->referer = 'soft_'.$referer;
                $userObj->last_login_at = time();
                $userObj->setPassword($openid);
                $userObj->generateAuthKey();
                $userObj->save();
    
                $thirdObj->user_id = $userObj->id;
                $thirdObj->save();
    
                $uid = $userObj->id;  
                
                //用户名为空时给用户id
                if (trim($userObj->username) == '') {
                    $userObj->username = $uid;
                    $userObj->save();
                }
            } else {
                $user = User::find()->where("mobile = $mobile")->one();
    
                if (empty($user)) {
                    $data = "status=-6";;
                    return $this->jiami($data);
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
                        
                    } else {
                        $data = "status=-8";
                        return $this->jiami($data);
                    }
                }
            }
            Yii::$app->user->login(User::findOne($uid));
            //活动
//             Act1::otherAddDrawTimes('1','soft',Yii::$app->params['actId'],$uid);
            //单独添加登录日志
            $channel = $channel ? $channel : '';
            $this->AddSoftLoginLog($uid, $channel);

            //获取登录token
            $this->getUserToken($uid);
            
            $data = "status=1&".$this->ReturnUserInfo($uid,$headimgurl,$openid,$referer);
    
            //删除用户缓存信息
            $session->remove('firstThirdLogin');
        } else {
            $data = "status=0";
        }
        
        return $this->jiami($data);
    }

    /**
     * 公共--token登录 20180927
     * @para string  utoken      token
     * @para string channel      渠道
     * @para string referer      pdf,ocr
     * @return string
     */
    public function actionTokenLogin() {
        $utoken = $this->jiemi(yii::$app->request->post('utoken'));
        $channel = $this->jiemi(Yii::$app->request->post('channel'));
        $referer = $this->jiemi(Yii::$app->request->post('referer','k53OKGy0PtTIlGqP3/cDMw=='));
        $data = "status=0";
        $time = time();
        
        $userM = User::find()
                ->select("id,login_token_expires")
                ->where("login_token = '$utoken'")
                ->one();

        if ($userM) {
            if ($userM->login_token_expires > $time) {
                $userM->login_token_expires = strtotime("+10 day",$time);
                $userM->save();
                
                $user = User::find()
                ->select("u.*,o.openid,o.headimgurl")
                ->alias('u')
                ->where("id = '{$userM->id}'")
                ->leftJoin("(SELECT user_id,openid,headimgurl FROM oauth_third_login) o",'o.user_id = u.id')
                ->asArray()
                ->one();
                
                $headimgurl = empty($user['headimgurl']) ? '' : $user['headimgurl'];
                $openid =  empty($user['openid']) ? '' : $user['openid'];
                
                Yii::$app->user->login($userM);
                
                //单独添加登录日志
                $channel = $channel ? $channel : '';
                $this->AddSoftLoginLog($userM->id, $channel);
                
                $userInfo = $this->ReturnUserInfo($userM->id,$headimgurl,$openid,$referer);
                
                $data = "status=1&".$userInfo;
            } else {
                $data = "status=0";
            }
        }  
        
        return $this->jiami($data);
    }


}
