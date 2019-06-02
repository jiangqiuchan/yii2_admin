<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\functions;

use Yii;
require_once "../../common/functions/phpqrcode.php";
/**
 * Description of Functions
 *
 * @author Administrator
 */
class Functions {
    /**
     * 加密函数
     *
     * @param string $txt
     * @param string $key
     * @return string
     */
    public static function passport_encrypt($txt, $key)
    {
        srand((double)microtime() * 1000000);
        $encrypt_key = md5(rand(0, 32000));
        $ctr = 0;
        $tmp = '';
        for($i = 0; $i < strlen($txt); $i++ )
        {
            $ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
            $tmp .= $encrypt_key[$ctr].($txt[$i] ^ $encrypt_key[$ctr++]);
        }
        return base64_encode(self::passport_key($tmp, $key));
    }
    /**
     * 编码函数
     *
     * @param string $txt
     * @param string $key
     * @return string
     */
    public static function passport_key($txt, $encrypt_key)
    {
        $encrypt_key = md5($encrypt_key);
        $ctr = 0;
        $tmp = '';
        for($i = 0; $i < strlen($txt); $i++)
        {
            $ctr = $ctr == strlen($encrypt_key) ? 0 : $ctr;
            $tmp .= $txt[$i] ^ $encrypt_key[$ctr++];
        }
        return $tmp;
    }
    /**
     * 解密函数
     *
     * @param string $txt
     * @param string $key
     * @return string
     */
    public static function passport_decrypt($txt, $key)
    {
        $txt = self::passport_key(base64_decode($txt), $key);
        $tmp = '';
        for ($i = 0;$i < strlen($txt); $i++) {
            $md5 = $txt[$i];
            $tmp .= $txt[++$i] ^ $md5;
        }
        return $tmp;
    }
    /**
     * 创建像这样的查询: "IN('a','b')";
     *
     * @access   public
     * @param    mix      $item_list      列表数组或字符串
     * @param    string   $field_name     字段名称
     *
     * @return   void
     */
    public static function db_create_in($item_list, $field_name = '',$isNot = '')
    {
        $isNot = $isNot ? ' NOT ' : '';
        if (empty($item_list))
        {
            return $field_name . $isNot ." IN ('') ";
        }
        else
        {
            if (!is_array($item_list))
            {
                $item_list = explode(',', $item_list);
            }
            $item_list = array_unique($item_list);
            $item_list_tmp = '';
            foreach ($item_list AS $item)
            {
                if ($item !== '')
                {
                    $item_list_tmp .= $item_list_tmp ? ",'$item'" : "'$item'";
                }
            }
            if (empty($item_list_tmp))
            {
                return $field_name . $isNot ." IN ('') ";
            }
            else
            {
                return $field_name . $isNot .' IN (' . $item_list_tmp . ') ';
            }
        }
    }
    /**
     * 发送HTTP请求方法
     * @param  string $url    请求URL
     * @param  array  $params 请求参数
     * @param  string $method 请求方法GET/POST
     * @return array  $data   响应数据
     */
    static function http_judu($url, $params = array(), $method = 'GET', $header = array(), $multi = false){
        $opts = array(
                CURLOPT_TIMEOUT        => 5,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_SSL_VERIFYHOST => false,
                CURLOPT_HTTPHEADER     => $header
        );

        /* 根据请求类型设置特定参数 */
        switch(strtoupper($method)){
            case 'GET':
                $opts[CURLOPT_URL] = $url . '?' . http_build_query($params);
                break;
            case 'POST':
                //判断是否传输文件
                $params = $multi ? $params : http_build_query($params);
                $opts[CURLOPT_URL] = $url;
                $opts[CURLOPT_POST] = 1;
                $opts[CURLOPT_POSTFIELDS] = $params;
                break;
            default:
                throw new Exception('不支持的请求方式！');
        }

        /* 初始化并执行curl请求 */
        $ch = curl_init();
        curl_setopt_array($ch, $opts);
        $data  = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);
        if($error) throw new Exception('请求发生错误：' . $error);
        return  $data;
    }
    /**
     * 字符串过滤，过滤所有html代码
     * @param string $string
     * @return $string
     */
    static function checkStr($string, $length = 0) {
        $string = preg_replace('/[\\x00-\\x08\\x0B\\x0C\\x0E-\\x1F]/', '', $string);
        $string = str_replace(array("\0", "%00", "\r"), '', $string);
        if($length){
          $string = substr($string,0,$length);
        }
        $string = str_replace(array("%3C", '<'), '&lt;', $string);
        $string = str_replace(array("%3E", '>'), '&gt;', $string);
        $string = str_replace(array('"', "'", "\t"), array('&quot;', '&#39;', '    '), $string);
        return trim($string);
    }
    /**
     * 截取UTF-8编码下字符串的函数
     *
     * @param   string      $str        被截取的字符串
     * @param   int         $length     截取的长度
     * @param   bool        $append     是否附加省略号
     *
     * @return  string
     */
    static function sub_str($str, $length = 0, $append = true){
        $str        = trim($str);
        $strlength  = strlen($str);

        if ($length == 0 || $length >= $strlength)
        {
            return $str;
        }
        elseif ($length < 0)
        {
            $length = $strlength + $length;
            if ($length < 0)
            {
                $length = $strlength;
            }
        }

        if (function_exists('mb_substr'))
        {
            $newstr = mb_substr($str, 0, $length, 'utf-8');
        }
        elseif (function_exists('iconv_substr'))
        {
            $newstr = iconv_substr($str, 0, $length, 'utf-8');
        }
        else
        {
            //$newstr = trim_right(substr($str, 0, $length));
            $newstr = substr($str, 0, $length);
        }

        if ($append && $str != $newstr)
        {
            $newstr .= '...';
        }

        return $newstr;
    }
    /**
     * 获取用户年龄
     * @param  intval $year   出生年份
     * @return intval 用户当前年龄
     */
    public static function getUserAge($year){
        $nowYear    = date('Y');
        $year       = intval($year) ? intval($year) : $nowYear;
        $userYear   = intval($nowYear - $year);

        return $userYear;
    }
    /**
     * [is_moblie 验证手机]
     * @param  [type] $tel [电话号码]
     * @return [type]      [description]
     */
    public static function isMoblie($moblie)
    {
       return  preg_match("/^0?1((3|4|7|8)[0-9]|5[0-35-9]|4[57])\d{8}$/", $moblie);
    }
    /**
     * 验证输入的邮件地址是否合法
     *
     * @access  public
     * @param   string      $email      需要验证的邮件地址
     *
     * @return bool
     */
    public static function isEmail($email)
    {
        $chars = "/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6}\$/i";
        if (strpos($email, '@') !== false && strpos($email, '.') !== false)
        {
            if (preg_match($chars, $email))
            {
                return true;
            }
            else
            {
                return false;
            }
        }
        else
        {
            return false;
        }
    }
    /**
     * [getFirstCharter 获取首字母]
     * @param  [type] $str [description]
     * @return [type]      [description]
     */
    public static function getFirstCharter($str){ 
         if(empty($str)){return '';} 
         $fchar=ord($str{0}); 
         if($fchar>=ord('A')&&$fchar<=ord('z')) return strtoupper($str{0}); 
         $s1=iconv('UTF-8','gbk',$str); 
         $s2=iconv('gbk','UTF-8',$s1); 
         $s=$s2==$str?$s1:$str; 
         $asc=ord($s{0})*256+ord($s{1})-65536; 
         if($asc>=-20319&&$asc<=-20284) return 'A'; 
         if($asc>=-20283&&$asc<=-19776) return 'B'; 
         if($asc>=-19775&&$asc<=-19219) return 'C'; 
         if($asc>=-19218&&$asc<=-18711) return 'D'; 
         if($asc>=-18710&&$asc<=-18527) return 'E'; 
         if($asc>=-18526&&$asc<=-18240) return 'F'; 
         if($asc>=-18239&&$asc<=-17923) return 'G'; 
         if($asc>=-17922&&$asc<=-17418) return 'H'; 
         if($asc>=-17417&&$asc<=-16475) return 'J'; 
         if($asc>=-16474&&$asc<=-16213) return 'K'; 
         if($asc>=-16212&&$asc<=-15641) return 'L'; 
         if($asc>=-15640&&$asc<=-15166) return 'M'; 
         if($asc>=-15165&&$asc<=-14923) return 'N'; 
         if($asc>=-14922&&$asc<=-14915) return 'O'; 
         if($asc>=-14914&&$asc<=-14631) return 'P'; 
         if($asc>=-14630&&$asc<=-14150) return 'Q'; 
         if($asc>=-14149&&$asc<=-14091) return 'R'; 
         if($asc>=-14090&&$asc<=-13319) return 'S'; 
         if($asc>=-13318&&$asc<=-12839) return 'T'; 
         if($asc>=-12838&&$asc<=-12557) return 'W'; 
         if($asc>=-12556&&$asc<=-11848) return 'X'; 
         if($asc>=-11847&&$asc<=-11056) return 'Y'; 
         if($asc>=-11055&&$asc<=-10247) return 'Z'; 
         return '#'; 
    }
    /**
     * 重新获得商品图片与商品相册的地址
     *
     * @param string $image 原商品相册图片地址
     * @param boolean $thumb 是否为缩略图
     *
     * @return string   $url
     */
    public static function get_image_path($image = '', $thumb = false , $width = 200, $height = 200)
    {
        $url = empty($image) ? '' : Yii::$app->params['uploadsUrl'].$image;
        $url = $thumb && $image ? $url.'@!'.$width.'x'.$height.'.jpg' : $url;
        return $url;
    }
    /**
     * [uploadOssimg 上传图片到服务器]
     * @param  [type]  $imgUrl  [图片地址]
     * @param  [type]  $is_del  [是否删除]
     * @return [str]           [图片地址字符串]
     */
    public static function uploadOssimg($imgUrl){

        if(!$imgUrl) return false;

        $imgData    = self::http_judu($imgUrl);
        $imgType    = self::check_image_type($imgData);
        $imgTypeArr = array('jpg','gif','png');

        if(!in_array($imgType,$imgTypeArr)){
            return false;
        }
        //准备上传
        $serverName =  Yii::$app->params['uploadsUrl'];

        $filename   = '';
        $filename   = str_replace($serverName,'uploads/',$imgUrl);
        $dirname    = dirname($filename);

        if (!file_exists($dirname) && !mkdir($dirname, 0777, true)) {
          return false;
        }
        try {
            $return = file_put_contents($filename,$imgData);
        } catch (Exception $e) {
            $return = $e->getMessage();
        }
        return $return;
    }
    /**
     *  作用：格式化参数，签名过程需要使用
     */
    PUBLIC STATIC function formatBizQueryParaMap($paraMap, $urlencode) {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if($urlencode) {
                $v = urlencode($v);
            }
            if ($k != 'sign' && $k != 'sign_type' && $k != 'code'){
                $buff .= $k . $v ;
            }
        }
        $reqPar;
        if (strlen($buff) > 0) {
            $reqPar = $buff;//substr($buff, 0, strlen($buff)-1);
        }
        return $reqPar;
    }
    /**
     * [check_image_type 判断文件流类型]
     * @param  [type] $image [description]
     * @return [type]        [description]
     */
    PUBLIC STATIC  function check_image_type($image){
        $bits = array(
            'jpg' => "\xFF\xD8\xFF",
            'gif' => "GIF",
            'png' => "\x89\x50\x4e\x47\x0d\x0a\x1a\x0a",
            'mbp' => 'BM',
        );
        foreach ($bits as $type => $bit) {
            if (substr($image, 0, strlen($bit)) === $bit) {
                return $type;
            }
        }
        return 'UNKNOWN IMAGE TYPE';
    }
    /**
     * [strIsImg 正则匹配字符串是否是图片样式]
     * @param  [type] $str [description]
     * @return [type]      [description]
     */
    PUBLIC STATIC  function strIsImg($str){
        if(!$str) return '';
        preg_match('/<img (.*?) src=\"(.+?)\".*?>/',$str,$match);
        if($match){
            return '图片';//$match['2']
        }else{
            return Tools::userTextDecode($str);;
        }
    }
    /**
     * [array_sort 二维数组排序]
     * @param  [type] $array [数组]
     * @param  [type] $row   [排序列]
     * @param  [type] $type  [规则]
     * @return [type]        [description]
     */
    PUBLIC STATIC  function array_sort($array,$key,$orderBy = SORT_ASC){
        $array_temp = [];
        foreach($array as $v){
            $array_temp[] = $v[$key];
        }
        array_multisort($array_temp,$orderBy,$array);
        return $array;
    }
    /**
     * [getip description]
     * @return [type] [description]
     */
    PUBLIC STATIC  function getip(){
        if(!empty($_SERVER["HTTP_CLIENT_IP"])) { $cip = $_SERVER["HTTP_CLIENT_IP"]; }
        else if(!empty($_SERVER["HTTP_X_FORWARDED_FOR"])) { $cip = $_SERVER["HTTP_X_FORWARDED_FOR"]; }
        else if(!empty($_SERVER["REMOTE_ADDR"])) { $cip = $_SERVER["REMOTE_ADDR"]; }
        else $cip = "";

        preg_match("/[\d\.]{7,15}/", $cip, $cips);
        $cip = $cips[0] ? $cips[0] : 'unknown';
        unset($cips);
        return $cip;
    }
    /**
     * [get_order_sn 获取新订单号]
     * @return [type] [description]
     */
    PUBLIC STATIC  function get_order_sn()
    {
        /* 选择一个随机的方案 */
        mt_srand((double) microtime() * 1000000);

        return date('Ymd') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
    }
    /**
     * [insertFile 添加文件函数]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    PUBLIC STATIC  function insertFile($params){
        //验证
        $file_size      = (float)$params['fileSize'];
        $fileName       = Functions::checkStr($params['fileName']);
        $type           = Functions::checkStr($params['type']);
        $fileType       = Functions::checkStr($params['fileType']);
        $password       = Functions::checkStr($params['password']);
        $ispay          = PdfFunctions::IpSum($file_size);//验证文件是否收费
        $ispay          = isset($ispay['ispay']) ? $ispay['ispay'] : 0;
        $ip             = self::getip();
        $orderSn        = self::get_order_sn();

        if(!$fileName) return ['code' => '-1','msg' => '文件为空'];

        $fileName   = self::get_image_path($fileName);
        $insertSql  = "  INSERT INTO {{%upload_file}}(file_upload,file_type,file_size,order_sn,type,password,ip,ispay) 
                        VALUES('$fileName','$fileType','$file_size','$orderSn','$type','$password','$ip',$ispay)";

        $return     = Yii::$app->db->createCommand($insertSql)->execute(); 
        $inserId    = Yii::$app->db->getLastInsertID();
        
        return ['status' => 1, 'msg' => $inserId];
    }
    /**
     * [GetDownload 获取下载地址]
     * @param [type] $id [description]
     */
    PUBLIC STATIC  function GetDownload($id){
        //验证
        $id = intval($id);
        if(!$id) return ['status' => 0 ,'msg' => 'not found file'];
        $selectSql = "SELECT f.ispay,f.file_download,o.pay_status FROM  {{%upload_file}} f LEFT JOIN {{%order_info}} o ON f.order_sn = o.order_sn WHERE f.id = '$id'";
        $row  = Yii::$app->db->createCommand($selectSql)->queryOne();
        if(!empty($row['file_download'])){
            if($row['ispay']==0){
                    return ['status' => 1, 'msg' => $row['ispay'],'file_download'=>$row['file_download']]; 
            }else{
                if($row['pay_status']>0){//已付款
                    return ['status' => 1, 'msg' => $row['ispay'], "ispaid"=>"1", 'file_download' => $row['file_download']]; 
                }else{
                    return ['status' => 1, 'msg' => $row['ispay'] ,"ispaid"=>"0"]; 
                }
            }
        }else{
                return ['status' => 0, 'msg' => 'not found download']; 
        }
    }
    /**
     * [insertFeedback 添加留言方法]
     * @param  [type] $params [description]
     * @return [type]         [description]
     */
    PUBLIC STATIC  function insertFeedback($params){
        //验证
        $username       = Functions::checkStr($params['username']);
        $content        = Functions::checkStr($params['content']);
        $contact        = Functions::checkStr($params['contact']);
        $attachment     = Functions::checkStr($params['attachment']);
        
        $ip             = self::getip();
        $attachment     = self::get_image_path($attachment);

        $insertSql = "  INSERT INTO {{%feedback}}(username,content,contact,attachment,ip) 
                        VALUES('$username','$content','$contact','$attachment','$ip')";

        $return     = Yii::$app->db->createCommand($insertSql)->execute(); 
        
        return ['status' => 1, 'msg' => 'success'];
    }
    /**
     * [addEmail description]
     * @param [type] $id    [description]
     * @param [type] $email [description]
     */
    PUBLIC STATIC  function addEmail($id,$email){
        //验证
        $id         = intval($id);
        $email      = Functions::checkStr($email);
        
        if(!$id || !$email) return ['status' => 0,'msg' => '参数错误'];

        $ip         = self::getip();
        $isEmail    = self::isEmail($email);

        if(!$isEmail)  return ['status' => 0,'msg' => 'email格式错误'];

        $updateSql  = " UPDATE {{%upload_file}} SET email = '$email' 
                        WHERE id = '$id'  AND ip = '$ip'";

        Yii::$app->db->createCommand($updateSql)->execute(); 
        
        return ['status' => 1, 'msg' => 'success'];
    }
    /**
     * 邮件发送
     *
     * @param: $name[string]        接收人姓名
     * @param: $email[string]       接收人邮件地址
     * @param: $subject[string]     邮件标题
     * @param: $content[string]     邮件内容
     * @param: $type[int]           0 普通邮件， 1 HTML邮件
     * @param: $notification[bool]  true 要求回执， false 不用回执
     *
     * @return boolean
     */
    PUBLIC STATIC  function send_mail($name, $email, $subject, $content, $type = 0, $notification=false)
    {
        $smtp_mail      = 'mg_51ttfl@sina.com';    //发件邮箱
        $shop_name      = '米冠网络科技有限公司';  //发件方名
        $charset        = 'UTF-8';
        /* 获得邮件服务器的参数设置 */
        $params['host'] = 'smtp.sina.com';
        $params['port'] = 25;
        $params['user'] = $smtp_mail;
        $params['pass'] = 'daisy1987';

        /* 邮件的头部信息 */
        $content_type   = ($type == 0) ?
            'Content-Type: text/plain; charset=' . $charset : 'Content-Type: text/html; charset=' . $charset;
        $content        =  base64_encode($content);

        $headers        = array();
        $headers[] = 'Date: ' . gmdate('D, j M Y H:i:s') . ' +0000';
        $headers[] = 'To: "' . '=?' . $charset . '?B?' . base64_encode($name) . '?=' . '" <' . $email. '>';
        $headers[] = 'From: "' . '=?' . $charset . '?B?' . base64_encode($shop_name) . '?='.'" <' . $smtp_mail . '>';
        $headers[] = 'Subject: ' . '=?' . $charset . '?B?' . base64_encode($subject) . '?=';
        $headers[] = $content_type . '; format=flowed';
        $headers[] = 'Content-Transfer-Encoding: base64';
        $headers[] = 'Content-Disposition: inline';
        if ($notification)
        {
            $headers[] = 'Disposition-Notification-To: ' . '=?' . $charset . '?B?' . base64_encode($shop_name) . '?='.'" <' . $smtp_mail . '>';
        }

        if (empty($params['host']) || empty($params['port']))
        {
            // 如果没有设置主机和端口直接返回 false
            return array('status' => false, 'msg' => 'smtp_setting_error');
        }
        else
        {
            // 发送邮件
            if (!function_exists('fsockopen'))
            {
                //如果fsockopen被禁用，直接返回
                return array('status' => false, 'msg' => 'disabled_fsockopen');
            }
            $send_params = [];
            $send_params['recipients'] = $email;
            $send_params['headers']    = $headers;
            $send_params['from']       = $smtp_mail;
            $send_params['body']       = $content;

            require_once('../../common/components/smtp.php');
            static $smtp;
            if (!isset($smtp))
            {
                $smtp = new \smtp($params);
            }
            if ($smtp->connect() && $smtp->send($send_params))
            {
                return array('status' => true, 'msg' => 'OK');
            }
            else
            {
                $err_msg = $smtp->error_msg();
                if (empty($err_msg))
                {
                    return array('status' => false, 'msg' => 'Unknown Error');
                }
                else
                {
                    if (strpos($err_msg, 'Failed to connect to server') !== false)
                    {
                        return array('status' => false, 'msg' => 'smtp_connect_failure');
                    }
                    else if (strpos($err_msg, 'AUTH command failed') !== false)
                    {
                        return array('status' => false, 'msg' => 'smtp_login_failure');
                    }
                    elseif (strpos($err_msg, 'bad sequence of commands') !== false)
                    {
                        return array('status' => false, 'msg' => 'smtp_refuse');
                    }
                    else
                    {
                        return array('status' => false, 'msg' => $err_msg);
                    }
                }
                return  array('status' => false, 'msg' => 'send failure');
            }
        }
    }
    /**
     * 发送短信接口
     * @param string $m --号码
     * @return $c --内容
     * @auth lzj
     */
    public static function captcha($m,$c)
    {
        $template = '【PDF】';
        
        $url = "https://sms.yunpian.com/v2/sms/single_send.json";
        $apikey = "842b480cd350eda35800598b6387fba4"; //修改为您的apikey(https://www.yunpian.com)登陆官网后获取
        $mobile = $m; //请用自己的手机号代替
        $text = $template."您的验证码是：{$c}。请不要把验证码泄露给其他人。";
        $data = array('text' => $text, 'apikey' => $apikey, 'mobile' => $mobile);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept:text/plain;charset=utf-8', 'Content-Type:application/x-www-form-urlencoded','charset=utf-8'));
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $output = curl_exec($ch);
        curl_close($ch);
        $output = json_decode($output);
        return $output;
    }
    /**
     * [checkCaptcha 验证验证码函数]
     * @param  [type]  $mobile  [手机号]
     * @param  [type]  $captcha [验证码]
     * @param  integer $type    [类型0注册，1重置密码， 2登录注册，4为绑定]
     * @return [type]           [是否通过]
     */
    public static function checkCaptcha($mobile,$captcha,$type = 0){
        $isMoblie = self::isMoblie($mobile);
        $captcha  = intval($captcha);
        $time     = time();
    
        if(!$isMoblie) return false;
        //验证验证码
        $sql    = " SELECT * FROM {{%soft_pdf_mobile_captcha}}
        WHERE mobile = '$mobile' AND expire_time >= '$time' AND type = '$type' AND captcha = '$captcha'
        ORDER BY id DESC";
        $return = Yii::$app->db->createCommand($sql)->queryOne();
    
        return $return ? true : false;
    }
    /**
     * [checkCaptcha 修改验证码为已使用]
     * @param  [type]  $mobile  [手机号]
     * @param  [type]  $captcha [验证码]
     * @param  integer $type    [类型0注册，1重置密码， 2登录注册，4为绑定]
     * @return [type]           [是否通过]
     */
    public static function updateCaptcha($mobile,$captcha,$type = 0){
        $time = time();
        $captchaSql   = "UPDATE  {{%soft_pdf_mobile_captcha}} SET is_use = '1',using_time = '$time' WHERE mobile ='{$mobile}' AND captcha = '{$captcha}' AND type = '{$type}' AND expire_time >= '$time'";
        Yii::$app->db->createCommand($captchaSql)->execute();
    }
    /**
     * [userTextEncode 把用户输入的文本转义（主要针对特殊符号和emoji表情）]
     * @param  [type] $str [需要转换的字符]
     * @return [type]      [转换后的字符]
     */
    static function userTextEncode($str,$insert = '0'){
        if(!is_string($str))return $str;
        if(!$str || $str=='undefined')return $str;
    
        $text = json_encode($str); //暴露出unicode
        $text = preg_replace_callback("/(\\\u[ed][0-9a-f]{3})/i",function($str){
            return addslashes($str[0]);
        },$text); //将emoji的unicode留下，其他不动，这里的正则比原答案增加了d，因为我发现我很多emoji实际上是\ud开头的，反而暂时没发现有\ue开头。
        return $insert ? addslashes(json_decode($text)) : json_decode($text);
    }
    /**
     * [userTextDecode 反解码转义]
     * @param  [type] $str [转义后的字符]
     * @return [type]      [解析后的字符]
     */
    static function userTextDecode($str){
    
        $text = preg_replace_callback('/\\\\\\\\(u[ed][0-9a-f]{3})/i',function($str){
            return '\\' . $str[1];
        },$str); //将两条斜杠变成一条，其他不动
        $text = json_encode($text); //暴露出unicode
         
        $text = preg_replace_callback('/\\\\\\\\(u[ed][0-9a-f]{3})/i',function($str){
            return '\\' . $str[1];
        },$text); //将两条斜杠变成一条，其他不动
        return json_decode($text);
    }
    /**
     * XML转为array
     */
    static function xmlToArray($xml){
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $values;
    }
    /**
     * 数组转XML
     */
    static function arrayToXml($arr){
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                 $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }
    /**
     * 生成二维码图片
     */
    static function getQrcode($data,$outfile,$level = 'L',$size = 5,$margin = 0.7){
        return \QRcode::png($data,$outfile, $level,$size,$margin);
    }
    /**
     * 清空文件夹
     */
    static function deldir($dir,$baseDir) {
        //先删除目录下的文件：
        $dh=opendir($dir);
        while ($file=readdir($dh)) {
            if($file!="." && $file!="..") {
                $fullpath=$dir."/".$file;
                if(!is_dir($fullpath)) {
                    unlink($fullpath);
                } else {
                    self::deldir($fullpath,$baseDir);
                }
            }
        }
    
        closedir($dh);
        //删除当前文件夹：
        if ($dir != $baseDir) {
            if(rmdir($dir)) {
                return true;
            } else {
                return false;
            }
        } else {
            return true;
        }

    }
    
    //curl请求
    static function requestPost($url = '', $param = '') {
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

    //判断微信头像是否失效
    static function isDisHeadimg($headimgurl) {
        $oCurl = curl_init();
        // 设置请求头, 有时候需要,有时候不用,看请求网址是否有对应的要求
        $header[] = "Content-type: application/x-www-form-urlencoded";
        $user_agent = "Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/33.0.1750.146 Safari/537.36";
        curl_setopt($oCurl, CURLOPT_URL, $headimgurl);
        curl_setopt($oCurl, CURLOPT_HTTPHEADER,$header);
        // 返回 response_header, 该选项非常重要,如果不为 true, 只会获得响应的正文
        curl_setopt($oCurl, CURLOPT_HEADER, true);
        // 是否不需要响应的正文,为了节省带宽及时间,在只需要响应头的情况下可以不要正文
        curl_setopt($oCurl, CURLOPT_NOBODY, true);
        // 使用上面定义的 ua
        curl_setopt($oCurl, CURLOPT_USERAGENT,$user_agent);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1 );
        // 不用 POST 方式请求, 意思就是通过 GET 请求
        curl_setopt($oCurl, CURLOPT_POST, false);
        $sContent = curl_exec($oCurl);
        // 获得响应结果里的：头大小
        $headerSize = curl_getinfo($oCurl, CURLINFO_HEADER_SIZE);
        // 根据头大小去获取头信息内容
        $header = substr($sContent, 0, $headerSize);
        curl_close($oCurl);
        if(strstr($header,'X-ErrNo')){
            return 0;
        } else {
            return 1;
        }

    }
}
