<?php
include_once 'AlipaySubmit.php';
include_once 'AlipayNotify.php';
include_once 'AlipayCore.php';
include_once 'AlipayMD5.php';
class AlipayPay {

    //↓↓↓↓↓↓↓↓↓↓请在这里配置您的基本信息↓↓↓↓↓↓↓↓↓↓↓↓↓↓↓
    /**
     * @var String 合作身份者id，以2088开头的16位纯数字
     */
    public $partner = '2088621667318220';

    /**
     * @var String 收款支付宝账号
     */
    public $seller_email = 'zhongtai@miguantech.com';

    /**
     * @var String 安全检验码，以数字和字母组成的32位字符
     */
    public $key = '5gnsjqvaxgzhazmtg983tl7715sm79e7';

    //↑↑↑↑↑↑↑↑↑↑请在这里配置您的基本信息↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑

    /**
     * @var String 签名方式 不需修改
     */
    public $sign_type = 'MD5';

    /**
     * @var String 字符编码格式 目前支持 gbk 或 utf-8
     */
    public $input_charset = 'utf-8';

    /**
     * @var String ca证书路径地址，用于curl中ssl校验
     * 请保证cacert.pem文件在当前文件夹目录中
     */
    public $cacert = 'D:\phpStudy\WWW\pdf_server2\branches\vendor\AliPay\cacert.pem';

    /**
     * @var String 访问模式,根据自己的服务器是否支持ssl访问，若支持请选择https；若不支持请选择http
     */
    public $transport = 'http';

    /**
     * @var String 服务器异步通知页面路径
     * 需http://格式的完整路径，不能加?id=123这类自定义参数
     */
    public $notify_url = 'http://pdf.66zip.cn/notify/zfb-jsdz-notify';

    /**
     * @var String 页面跳转同步通知页面路径
     * 需http://格式的完整路径，不能加?id=123这类自定义参数，不能写成http://localhost/
     */
    public $return_url = '/notify/zfb-jsdz-return';

    public $extra_common_param = '';

    /**
     * @name requestPay
     * @desc
     * @param $out_trade_no String 商户订单号，商户网站订单系统中唯一订单号，必填
     * @param $subject String 订单名称
     * @param $total_fee String 付款金额
     * @param $body String 订单描述
     * @param $show_url String 商品展示地址
     * @param $target String 跳转方式
     * @return String 跳转HTML
     */
    public function requestPay($out_trade_no, $subject, $total_fee, $body, $show_url,$target) {
        /*         * ************************请求参数************************* */
        //支付类型
        $payment_type = "1";
        //必填，不能修改
        //防钓鱼时间戳
        $anti_phishing_key = "";
        //若要使用请调用类文件submit中的query_timestamp函数
        //客户端的IP地址
        $exter_invoke_ip = "";
        //非局域网的外网IP地址，如：221.0.0.1

        /*         * ********************************************************* */

        //构造要请求的参数数组，无需改动
        $parameter = array(
            "service" => "create_direct_pay_by_user",
            "partner" => trim($this->partner),
            "seller_email" => trim($this->seller_email),
            "payment_type" => $payment_type,
            "notify_url" => $this->notify_url,
            "return_url" => Yii::$app->getRequest()->getHostInfo().$this->return_url,
            "out_trade_no" => $out_trade_no,
            "subject" => $subject,
            "total_fee" => $total_fee,
            "body" => $body,
            "show_url" => $show_url,
            "extra_common_param" => $this->extra_common_param,
            "anti_phishing_key" => $anti_phishing_key,
            "exter_invoke_ip" => $exter_invoke_ip,
            "_input_charset" => trim(strtolower($this->input_charset)),
        );

        //建立请求
        $alipaySubmit = new AlipaySubmit($this->bulidConfig($target));
        $html_text = $alipaySubmit->buildRequestForm($parameter, "get", "确认");
        return $html_text;
    }
    
    //退款
    public function requestRefund($out_trade_no, $total_fee, $batch_no) {
        $parameter = [
            "service" => "refund_fastpay_by_platform_pwd",
            "partner" => trim($this->partner),
            "seller_user_id" => trim($this->partner),
            'refund_date' => date('Y-m-d H:i:s'),
            'batch_no' => $batch_no,
            'batch_num' => '1',
            'detail_data' => $out_trade_no.'^'.$total_fee.'^协商退款',
            'notify_url' => 'http://pdf.66zip.cn/notify/zfb-jsdz-refund-notify',
            '_input_charset' => trim(strtolower($this->input_charset)),
        ];
    
        $alipaySubmit = new AlipaySubmit($this->bulidConfig());
        $parameter = $alipaySubmit->buildRequestPara($parameter);
    
        //建立请求
        $html_text = $alipaySubmit->buildRequestForm($parameter, "get", "确认");
    
        return $html_text;
    }

    public function verifyNotify() {
        $alipayNotify = new AlipayNotify($this->bulidConfig());
        $verify_result = $alipayNotify->verifyNotify();

        return $verify_result;
    }

    public function verifyReturn() {
        $alipayNotify = new AlipayNotify($this->bulidConfig());
        $verify_result = $alipayNotify->verifyReturn();

        return $verify_result;
    }

    private function bulidConfig($target='_self') {
        //构造要请求的配置数组
        $alipay_config = array(
            'partner' => $this->partner,
            'seller_email' => $this->seller_email,
            'key' => $this->key,
            'sign_type' => $this->sign_type,
            'input_charset' => $this->input_charset,
            'cacert' => $this->cacert,
            'transport' => $this->transport,
            'target' => $target
        );
        return $alipay_config;
    }
    
    public function Notify($GET){
        $GET = json_decode($GET,TRUE);

        /* 检查支付的金额是否相符 */
        if ($this->check_money($GET['out_trade_no'], $GET['total_fee'])==0)
        {
            return false;
        }
        if ($GET['trade_status'] == 'TRADE_SUCCESS')
        {
            /* 改变订单状态 */
            $this->update_payment($GET['out_trade_no'],$GET['total_fee'],$GET['gmt_payment']);
            $id = $this->GetId($GET['out_trade_no']);
            $this->pdf_pay_log($id, $GET['total_fee']);
            return true;
        }
        else
        {
            return false;
        }
    }
    
        /**
     * [判断返回结果金额与订单金额是否一致]
     * @param type $order_sn  [订单编号]
     * @param type $total_fee [通知金额]
     */
    public function check_money($order_sn,$total_fee){
        $sql = "SELECT order_amount FROM {{%order_info}} WHERE order_sn = $order_sn";
        $file_data = Yii::$app->db->createCommand($sql)->queryScalar();
        return ($total_fee!=$file_data)?"0":"1";

    }
    
    /**
     * []
     * @param type $order_sn    [订单编号]
     * @param type $total_fee   [通知金额]
     * @param type $gmt_payment [支付时间]
     */
    public function update_payment($order_sn,$total_fee,$gmt_payment){
        $pay_time = strtotime($gmt_payment);
        $sql = "UPDATE {{%order_info}} SET pay_status='2' ,money_paid='$total_fee', pay_time='$pay_time' WHERE order_sn='$order_sn'";
        $order_id = Yii::$app->db->createCommand($sql)->execute();
    }
    
    /**
     * pdf_pay_log [入库]
     * @param type $id
     * @param type $total_fee
     */
    public function pdf_pay_log($id,$total_fee){
        $insertSql = "  INSERT INTO {{%pay_log}}(order_id,order_amount,is_paid) 
                        VALUES('$id','$total_fee','1')";
        $return     = Yii::$app->db->createCommand($insertSql)->execute(); 
    }
    
    /**
     * GetId [通过订单号获取ID]
     * @param type $order_id
     */
    PUBLIC STATIC function GetId($order_id){
        $sql = "SELECT order_id FROM {{%order_info}} WHERE order_sn = $order_id";
        $id = Yii::$app->db->createCommand($sql)->queryScalar();
        return $id;
    }
    
}
