<?php 
namespace common\components;
/**
 * Class Aes
 * @package backend\components
 * AES加密模式：ECB
 * 填充方式：pkcs5padding
 * 当前文件编码是utf8编码（unicode编码）
 */
class Aes{
    //密钥
    private $_secrect_key;
    const pdf  = 'FXnj9SZ2UUnicdHS';
      
    public function __construct($key){
        $this->_secrect_key = $key;
    }

    public  function encrypt($input) {
        $key = $this->_secrect_key;
        $data = openssl_encrypt($input, 'AES-128-ECB', $key, OPENSSL_RAW_DATA);
        $data = base64_encode($data);
        return $data;
    }

    private static function pkcs5_pad ($text, $blocksize) {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }

    public  function decrypt($sStr) {
        $sKey = $this->_secrect_key;
        $decrypted = openssl_decrypt(base64_decode($sStr), 'AES-128-ECB', $sKey, OPENSSL_RAW_DATA);
//         $charset = mb_detect_encoding($decrypted,['GB2312','GBK','UTF-8']);
        //if (($charset == 'EUC-CN' || $charset == 'GBK' || $charset == 'GB2312') && $decrypted) {
//             return iconv('GBK','UTF-8//IGNORE',$decrypted);
        //}

        return $decrypted;
    }
}