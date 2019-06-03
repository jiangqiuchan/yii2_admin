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
     * [is_moblie 验证手机]
     * @param  [type] $tel [电话号码]
     * @return [type]      [description]
     */
    public static function isMoblie($moblie)
    {
       return  preg_match("/^0?1((3|4|7|8)[0-9]|5[0-35-9]|4[57])\d{8}$/", $moblie);
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
     * @param  integer $type    [类型0注册，1重置密码， 2登录注册]
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
