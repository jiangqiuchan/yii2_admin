<?php 
namespace backend\components;

use backend\models\Channel;
use backend\models\SoftDownCategory;
use yii\data\Pagination;
use yii\log\FileTarget;
use yii;
/**
 * Class commonality
 * @package backend\components
 * 公共方法
 * 
 * 当前文件编码是utf8编码（unicode编码）
 */
class commonality{
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
     * [checkCaptcha 验证验证码函数]
     * @param  [type]  $mobile  [手机号]
     * @param  [type]  $captcha [验证码]
     * @param  integer $type    [类型0注册，1重置密码， 2登录，3登录注册,4为绑定]
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
    
}