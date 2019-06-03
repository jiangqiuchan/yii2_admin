<?php
namespace frontend\controllers;

use common\functions\Functions;
use Yii;
use common\models\User;
use common\models\OauthThirdLogin;
use common\functions\OrderFunctions;
use common\models\SoftLoginLog;
use common\models\Activity;
use common\models\BaiduApiAccounts;
use common\models\BaiduApiAccountsChild;
use common\models\BaiduApiAccountsLog;

class ApiBaseController extends BaseController
{
    /**
     * PDF 回调登录或存信息
     * @para string channel      渠道20180510
     */
    public function notifyLogin($type,$openid,$userData,$channel)
    {
        $userInfo = 'status=1';
        $thirdLogin = OauthThirdLogin::find()->select("id,user_id,headimgurl")->where("type = '$type' AND openid = :openid", [':openid' => $openid])->orderBy('id DESC')->one();
    
        if (!empty($thirdLogin)) {
            $userId = $thirdLogin->user_id;
            $user = User::findOne($userId);
            if ($user) {
                Yii::$app->user->login($user);

                //更新用户失效头像
                if (!Functions::isDisHeadimg($thirdLogin->headimgurl)) {
                    $thirdLogin->headimgurl = $userData['headimgurl'];
                    $thirdLogin->save();
                }

                //获取登录token
                $this->getUserToken($userId);
                
                //软件登录需要返回用户信息
                $userInfo = $userInfo."&".$this->ReturnUserInfo($userId,$userData['headimgurl'],$openid);
                return $this->renderPartial("login-notify",[
                    'userInfo' => $this->jiami($userInfo)
                ]);
            }
        } else {
            //存回调信息
            $session = Yii::$app->session;
            $session->set('firstThirdLogin', [
                'unionid' => $userData['unionid'],
                'openid' => $userData['openid'],
                'nickname' => $userData['nickname'],
                'headimgurl' => $userData['headimgurl'],
                'logintype' => $type,
            ]);
    
            $userInfo = $userInfo.'&headimgurl='.$userData['headimgurl'];
            return $this->renderPartial("login-notify",[
                'userInfo' => $this->jiami($userInfo)
            ]);
        }
    }
    
    /**
     * 拼接返回用户信息
     */
    public function returnUserInfo($userId,$headimgurl,$openid)
    {
        //获取用户与订单信息
        $user = OrderFunctions::getApiUserOrderData($userId);
    
        $user['headimgurl'] = $headimgurl;
        $user['openid'] = $openid;

        $return = urldecode(http_build_query($user));
    
        return $return;
    }
    
    //设置密码
    public function setPassword($userM,$password)
    {
        $userM->setPassword($password);
        if (!$userM->save()) {
            $return = ['status' => '-1','msg' => '密码修改失败'];
        } else {
            $return = ['status' => '1','msg' => '密码修改成功'];
        }
        
        return $return;
    }
    
    //获取用户登录token
    public function getUserToken($userId)
    {
        $returnToken = '';
        $userM = User::findOne($userId);
        
        if ($userM) {
            $time = time();
            $token = $userM->login_token;
            $expires = $userM->login_token_expires;
            $newExpires = strtotime("+10 day",$time);
            
            //生成token
            if (!$token || $expires <= $time) {
                $newToken = md5($userId.$time);
                $newExpires = strtotime("+10 day",$time);
                
                $userM->login_token = $newToken;
                $userM->login_token_expires = $newExpires;
                
                if ($userM->save()) {
                    $returnToken = $newToken;
                }
            } elseif ($token && $expires > $time) {
                $userM->login_token_expires = $newExpires;
                
                if ($userM->save()) {
                    $returnToken = $userM->login_token;
                }
            }
        }
        
        return $returnToken;
    }
    
    
    
}