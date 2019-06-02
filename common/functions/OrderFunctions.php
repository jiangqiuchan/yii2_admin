<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\functions;
use Yii;
use backend\models\SoftPdfOrder;
use common\models\User;
use backend\models\SoftPdfUser;
use frontend\models\activity\Act1;
/**
 * all functions abount order
 *
 * @author Administrator
 */
class OrderFunctions {
    /**
     * 单独获取最新有效订单model 
     * @param int    user_id 用户id
     * @param string referer 来源 pdf,ocr 
     * @param string where   查询条件 
     */
    public static function getOrderModel($userId,$where='1')
    {
        $order = SoftPdfOrder::find()
            ->where("user_id = $userId AND pay_status = 1")
            ->andWhere($where)
            ->orderBy("created_at DESC")
            ->one();

        return $order;
    }
    

    /**
     * 单独获取到期时间--官网购买页
     * @param int    user_id 用户id
     * @param string where   查询条件
     */
    public static function getExpireTime($userId,$where='1')
    {
        $order = SoftPdfOrder::find()
            ->where("user_id = $userId AND pay_status = 1 AND referer LIKE '%pdf%'")
            ->andWhere($where)
            ->orderBy('created_at DESC')
            ->one();
        if ($order) {
            $expireTime = $order->expire_time ? $order->expire_time : '';
            return $expireTime;
        }
        return '';
    }

    /**
     * 单独获取用户所有有效订单model 
     * @param int    user_id 用户id
     * @param string referer 来源 pdf,ocr 
     * @param string where   查询条件 
     */
    public static function getOrderModels($userId,$referer,$where='1')
    {
        $order = SoftPdfOrder::find()
            ->where("user_id = $userId AND pay_status = 1 AND referer LIKE '%pdf%'")
            ->andWhere($where)
            ->orderBy('id DESC')
            ->all();
    
        return $order;
    }
    
    /**
     * 获取到期时间字符串--接口
     * @param obj $orderM 订单模型
     */
    public static function getApiExpireTimeStr($orderM)
    {
        $str = 'null';
    
        if ($orderM) {
            if ($orderM->expire_time == 100) {
                $str = "forever";
            } else {
                $str = $orderM->expire_time;
            }
        }
    
        return $str;
    }
    
    /**
     * 获取用户信息与其订单信息数组--接口
     * @param int    user_id 用户id
     * @param string referer 来源 pdf,ocr
     */
    public static function getApiUserOrderData($userId)
    {
        $user = User::find()
            ->alias('u')
            ->select("u.id,u.username,u.login_token,o.expire_time,o.package,t.headimgurl")
            ->leftJoin("(SELECT headimgurl,user_id FROM oauth_third_login WHERE user_id = $userId) AS t",'t.user_id = u.id')
            ->leftJoin("(SELECT user_id,package,case when expire_time is null or expire_time='' then 'null' when expire_time='100' then 'forever' else expire_time end AS expire_time FROM soft_pdf_order WHERE pay_status=1 AND user_id = $userId AND package <> 11 ORDER BY created_at DESC) o",'o.user_id = u.id')
            ->where("u.id = $userId")
            ->asArray()
            ->one();

        if (!$user['expire_time']) {
            $user['expire_time'] = 'null';
        }

        //套餐名称
        $arr = SoftPdfOrder::getPackageArr(1);
        $user['package'] = isset($arr[$user['package']]) ? $arr[$user['package']] : '';
        
        $user['username'] = Functions::userTextDecode($user['username']);
        $user['server_time'] = time();
    
        return $user;
    }
    
    /**
    /**
     * 获取用户信息与其订单信息数组--官网菜单栏,pdf软件购买
     * @param int    user_id 用户id
     */
    public static function getWebTopicUserData($userId)
    {
        $data = [];
    
        $data = SoftPdfUser::find()
            ->alias('u')
            ->select("u.username,u.mobile,u.id,t.headimgurl,o.expire_time")
            ->leftJoin("(SELECT headimgurl,user_id FROM oauth_third_login WHERE user_id = $userId) AS t",'t.user_id = u.id')
            ->leftJoin("(SELECT user_id,expire_time FROM soft_pdf_order WHERE pay_status=1 AND user_id = $userId AND package <> 11 AND referer LIKE '%pdf%' ORDER BY created_at DESC) AS o",'o.user_id = u.id')
            ->where("u.id = $userId")
            ->asArray()
            ->one();

//        if ($data['mobile']) {
//            $data['headimgurl'] = '';
//        }

        if ($data['expire_time'] != 100) {
            $str = '';
            $data['is_vip'] = 0;
            if ($data['expire_time'] < time()) {
                $str = ' 已过期';
            } else {
                $data['is_vip'] = 1;
                $str = ' 过期';
            }
            $data['expire_time'] = date("Y-m-d",$data['expire_time']).$str;
        } else {
            $data['is_vip'] = 1;
            $data['expire_time'] = '永久';
        }
    
        return $data;
    }
    
    /**
     * 是否为会员及返回套餐类型--在线转换
     * @param int    user_id 用户id
     * @param string referer 来源 pdf,ocr
     * @param string where   查询条件 
     */
    public static function getVipPackage($userId,$referer='pdf',$where='1')
    {
        $time = time();
        $order = SoftPdfOrder::find()
            ->where("user_id = $userId AND pay_status = 1 AND expire_time >= '$time' AND referer LIKE '%$referer%'")
            ->andWhere($where)
            ->orderBy('created_at DESC')
            ->one();
        if ($order) {
            return $order->package;
        } else {
            return false;
        }
    }
    
    /**
     * 获取套餐价格
     * @param int    package 套餐类型编号
     * @param type   pdf or ocr
     * @param int    drawId  优惠券ID
     */
    public static function getPackageMoney($package,$type = 'pdf',$drawId = '')
    {
        if ($type == 'pdf') {
            //12,13,14,15为临时价格
            $moneyArr = ['1' => '15','2' => '19','3' => '39','6' => '99','7' => '29','12' => '38','13' => '48','14' => '78','15' => '28'];
        } elseif ($type == 'ocr') {
            $moneyArr = ['7' => '38','3' => '58','6' => '88','66' => '118'];
        } elseif ($type == 'test') {
            $moneyArr = ['1' => '15','2' => '19','3' => '48','6' => '78','7' => '38'];
        }
        $return = [];
        if (isset($moneyArr[$package])) {
            $money = $moneyArr[$package];
    
            if ($drawId) {
                if ($package == '6') {
                    $data = ['status'=>'-1','msg'=>'购买永久会员不能使用优惠券'];
    
                    return 	json_encode($data);
                }
    
                $useCoupon = Act1::useCoupon($_POST['draw_id'],Yii::$app->user->id, $money, $package);
                if ($useCoupon['status'] != '1') {
                    $data = $useCoupon;
                    return 	json_encode($data);
                } else {
                    $money = $useCoupon['msg'];
                }
            }
            if(Yii::$app->user->id == '1'){
                $money = '0.02';
            }
            $return['status'] = '1';
            $return['money'] = $money;
        } else {
            $return['status'] = '0';
        }
    
        return $return;
    }

    /**
     * 下单时获取套餐开始计算时间
     * @param int    package  套餐类型编号
     * @param int    referer  pdf,ocr
     */
    public static function getExpireStartTime($userId,$referer = 'pdf',$where="1")
    {
        $time = time();
        $user = SoftPdfOrder::find()
            ->where("user_id = $userId AND pay_status = 1 AND referer LIKE '%$referer%' AND (expire_time >= $time OR expire_time = 100)")
            ->andWhere($where)
            ->orderBy("created_at DESC")
            ->one();
        if ($user) {
            if ($user->expire_time && $user->pay_status == 1) {
                if ($user->expire_time == 100) {
                    $package = 6;
                    $startTime = time();
                } else {
                    $startTime = $user->expire_time;
                }
            } else {
                $startTime = time();
            }
        } else {
            $startTime = time();
        }
    
        return $startTime;
    }
    
    /**
     * 下单时获取套餐到期时间
     * @param int    package  套餐类型编号
     * @param int    user_id  用户id
     * @param int    time     开始计算时间戳
     */
    public static function getPackageExpireTime($package,$userId,$time)
    {
        $return = 0;
    
        switch ($package)
        {
            case 1:
                $return = strtotime("+1 week",$time);
                break;
            case 2:
            case 15:
                $return = strtotime("+30 day",$time);
                break;
            case 3:
            case 13:
                $return = strtotime("+370 day",$time);
                break;
            case 4:
                $return = strtotime("+2 year",$time);
                break;
            case 5:
                $return = strtotime("+3 year",$time);
                break;
            case 6:
            case 14:
                $return = 100;
                break;
            case 7:
            case 12:
                $return = strtotime("+180 day",$time);
                break;
            default:
        }
    
        return $return;
    }

    /**
     * 创建预付订单
     * @param folat   money           金额
     * @param string  outTradeNo      商户订单号
     * @param int     user_id         用户id
     * @param int     payType         支付类型 weixin alipay
     * @param int     package         套餐类型编号
     * @param int     startTime       套餐开始时间
     * @param int     expireTime      套餐到期时间
     * @param int     pay_type_method 支付来源 
     * @param int     drawId          奖品ID
     * @param int     prizeId         奖项/优惠ID
     * @param string  referer         来源，pdf,ocr
     * @param int     $manConvertPass 是否能获取免费人工转换次数
     */
    public static function createOrder($money,$outTradeNo,$userId,$payType,$package,$startTime,$expireTime,$pay_type_method,$drawId='0',$prizeId='0',$referer='pdf',$manConvertPass='0')
    {
        $return = [];
        //创建支付订单
        $order = new SoftPdfOrder();
        $order->money = $money;
        $order->out_trade_no = $outTradeNo;
        $order->user_id = $userId;
        $order->pay_type = $payType;
        //临时价格
        if ($package == 12) {
            $package = 7;
        } elseif ($package == 13) {
            $package = 3;
        } elseif ($package == 14) {
            $package = 6;
        } elseif ($package == 15) {
            $package = 2;
        }
        $order->package = $package;
        $order->start_time = $startTime;
        $order->expire_time = $expireTime;
        $order->pay_type_method = $pay_type_method;
        $order->draw_id = $drawId;
        $order->prize_id = $prizeId;
        $order->referer = $referer;
//        $order->man_convert_pass = $manConvertPass;
        //活动
        //             if (isset($_POST['draw_id']) && !empty($_POST['draw_id'])) {
        //                 $order->draw_id = $_POST['draw_id'];
        //             }
    
        if ($order->save()) {
            $return['status'] = '1';
            $return['data'] = $order;
        } else {
            $return['status'] = '0';
            $return['data'] = json_encode($order->getErrors());
        }
    
        return $return;
    }

}
