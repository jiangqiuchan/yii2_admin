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
use common\models\ErrorLog;
use common\models\BaiduApiAccountsLog;
use yii\base\Object;

class ApiBaseController extends BaseController
{
    /**
     * PDF 回调登录或存信息
     * @para string channel      渠道20180510
     */
    public function notifyLogin($type,$openid,$userData,$channel,$referer)
    {
        $userInfo = 'status=1';
        $thirdLogin = OauthThirdLogin::find()->select("id,user_id,headimgurl")->where("type = '$type' AND openid = :openid", [':openid' => $openid])->orderBy('id DESC')->one();
    
        if (!empty($thirdLogin)) {
            $userId = $thirdLogin->user_id;
            $user = User::findOne($userId);
            if ($user) {
                Yii::$app->user->login($user);

                //单独添加登录日志
                $channel = $channel ? $channel : '';
                $this->AddSoftLoginLog($userId, $channel);

                //更新用户失效头像
                if (!Functions::isDisHeadimg($thirdLogin->headimgurl)) {
                    $thirdLogin->headimgurl = $userData['headimgurl'];
                    $thirdLogin->save();
                }

                //获取登录token
                $this->getUserToken($userId);
                
                //软件登录需要返回用户信息
                $userInfo = $userInfo."&".$this->ReturnUserInfo($userId,$userData['headimgurl'],$openid,$referer);
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
    public function returnUserInfo($userId,$headimgurl,$openid,$referer)
    {
        //获取用户与订单信息
        $user = OrderFunctions::getApiUserOrderData($userId, $referer);
    
        $user['headimgurl'] = $headimgurl;
        $user['openid'] = $openid;

        $return = urldecode(http_build_query($user));
    
        return $return;
    }

    //单独添加登录日志
    public function addSoftLoginLog($uid,$channel)
    {
        $logTime = time();
        $newLog = new SoftLoginLog();
        $newLog->user_id = $uid;
        $newLog->login_time = $logTime;
        $newLog->channel = $channel ? $channel : '';
        $newLog->save();
    }
    
    //登录绑定mac
    public function bindMac($mac,$user_id)
    {
        $user = User::findOne($user_id);
        $macArr = json_decode($user->mac) ? json_decode($user->mac) : [];
        if (count($macArr) < 3) {
            array_push($macArr,$mac);
            $user->mac = json_encode($macArr);
            $user->save();
            return true;
        } else {
            return false;
        }
    }
    
    //ocr获取可用的百度账号
    public function getApiAccounts($useTime,$childId,$errorCode)
    {
        $time = time();
        $return = ['status' => 1,'msg' => '获取成功'];
        
        //开启错误账号
        $todayTime = strtotime(date('Y-m-d'));
        $errorCount = BaiduApiAccounts::find()
            ->where("is_close = 1 AND close_time < $todayTime AND close_code <> 0")
            ->count();
        if ($errorCount > 0) {
            BaiduApiAccounts::updateAll(['is_close'=> 0,'close_time'=>0,'close_code'=>0],"is_close = 1 AND close_time < $todayTime AND close_code <> 0");
        }
        
        //关闭错误账号一天
        if ($childId && $errorCode) {
            $child = BaiduApiAccountsChild::findOne($childId);
            if (!$child) {
                $return = ['status' => 0,'msg' => '获取失败'];
                return $return;
            } else {
                $dayCodes = ['17','216110'];
                $timeCodes = ['282006','18','110','111'];
                
                if (in_array($errorCode, $dayCodes)) {
                    $accountsM = BaiduApiAccounts::findOne($child->baiduApiAccounts->id);
                    $accountsM->is_close = 1;
                    $accountsM->close_time = $time;
                    $accountsM->close_code = $errorCode;
                    $accountsM->save();
                } elseif (in_array($errorCode, $timeCodes)) {
                    BaiduApiAccountsChild::updateAll(['start_time'=>$time,'effec_end_time'=> $time+80],"effec_end_time < $time");
                }
            }
        } 

        $data = BaiduApiAccountsChild::find()
            ->joinWith('baiduApiAccounts a')
            ->where("effec_end_time < $time")
            ->andWhere("a.is_close = 0")
            ->orderBy('use_times ASC')
            ->one();
        
        if ($data && $data->baiduApiAccounts) {
            $tokenExpires = ($data->baiduApiAccounts->token_expires)-86400;
            $accountsM = BaiduApiAccounts::findOne($data->baiduApiAccounts->id);
            $tokenError = 0;

            if ($tokenExpires < $time || $tokenExpires < 0) {
                //更新token
                $url = 'https://aip.baidubce.com/oauth/2.0/token';
                $post_data['grant_type'] = 'client_credentials';
                $post_data['client_id'] = $data->baiduApiAccounts->api_key;
                $post_data['client_secret'] = $data->baiduApiAccounts->secret_key;
                $o = "";
                foreach ( $post_data as $k => $v )
                {
                    $o.= "$k=" . urlencode( $v ). "&" ;
                }
                $post_data = substr($o,0,-1);
            
                $res = $this->requestPost($url, $post_data);
                $res = json_decode($res,true);

                if ($res && isset($res['access_token'])) {
                    $token = $res['access_token'];
                    
                    $accountsM->access_token = $token;
                    $accountsM->token_expires = $time + 2592000;
            
                    if (!$accountsM->save()) {
                        $tokenError = 1;
                        $return = ['status' => 0,'msg' => '更新token失败'];
                    }                    
                } else {
                    $tokenError = 1;
                    $return = ['status' => 0,'msg' => '更新token失败'];
                }
                
                if ($tokenError) {
                    $accountsM->is_close = 1;
                    $accountsM->close_time = $time;
                    $accountsM->close_code = 1;
                    $accountsM->save();
                    
                    return $return;
                }
            }
            
            if (!$tokenError) {
                $data->start_time = $time;
                $data->use_time = $useTime;
                $data->end_time = $time+$useTime;
                $data->effec_end_time = $time+$useTime;
                $data->beat_time = $time;
                $data->use_times += 1;
                
                if (!$data->save()) {
                    $return = ['status' => 0,'msg' => '获取失败2'];
                    return $return;
                }
                
                $returnData = [];
                $returnData['child_id'] = $data->id;
                $returnData['app_id'] = $accountsM->app_id;
                $returnData['api_key'] = $accountsM->api_key;
                $returnData['secret_key'] = $accountsM->secret_key;
                $returnData['access_token'] = $accountsM->access_token;

                $return = ['status' => 1,'msg' => '获取成功', 'data' => $returnData];
                return $return;
            }
        } else {
            $return = ['status' => 0,'msg' => '无可用账号'];
            return $return;
        } 
    }
    
    //ocr回收可用的百度账号
    public function reApiAccounts($id,$expiresTime)
    {
        $time = time();
        $return = ['status' => 0,'msg' => '回收失败'];
        
        $model = BaiduApiAccountsChild::find()
            ->where("id = '$id' AND use_time = '$expiresTime'")
            ->one();
        
        if ($model) {
            $model->effec_end_time = $time;
            if (!$model->save()) {
                $return['msg'] = '回收失败2';
            } else {
                $return['status'] = '1';
                $return['msg'] = '回收成功';
            }
        } else {
            $return['msg'] = '参数有误';            
        }
        
        return $return;
    }
    
    //curl请求
    private function requestPost($url = '', $param = '') {
        if (empty($url) || empty($param)) {
            return false;
        }
    
        $postUrl = $url;
        $curlPost = $param;
        $curl = curl_init();//初始化curl
        curl_setopt($curl, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($curl, CURLOPT_HEADER, 0);//设置header
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($curl, CURLOPT_POST, 1);//post提交方式
        curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $data = curl_exec($curl);//运行curl

        curl_close($curl);
    
        return $data;
    }

    //打log
    public function AddLog()
    {
        $errorM = new ErrorLog();
        ErrorLog::logError(json_encode($_REQUEST));
    }
    
    //百度账号log
    public function AddBaiduAccountLog()
    {
        $errorM = new BaiduApiAccountsLog();
        $errorM->info = json_encode($_REQUEST);
        $errorM->created_at = time();
        
        $errorM->save();
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
    
    //ocr记录百度账号使用心跳
    public function saveBeat($id,$expiresTime)
    {
        $time = time();
        $return = ['status' => 0,'msg' => '接收失败'];
        
        $model = BaiduApiAccountsChild::find()
            ->where("id = '$id' AND use_time = '$expiresTime'")
            ->one();

        if ($model) {
            $model->beat_time = $time;
            if (!$model->save()) {
                $return['msg'] = '接收失败2';
            } else {
                $return['status'] = '1';
                $return['msg'] = '接收成功';
            }
        } else {
            $return['msg'] = '参数有误';            
        }
        
        return $return;
    }
    
    
    
}