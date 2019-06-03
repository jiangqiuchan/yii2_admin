<?php 
namespace common\components;

use yii;

require_once "../../vendor/AliPay/dmf/AlipayTradePrecreateContentBuilder.php";
require_once "../../vendor/AliPay/dmf/AlipayTradeRefundContentBuilder.php";
require_once "../../vendor/AliPay/dmf/AlipayTradeService.php";
require_once "../../common/functions/phpqrcode.php";
require_once "../../vendor/WxPay/WxPay.NativePay.php";
require_once "../../vendor/WxPay/WxPay.JsApiPay.php";

/*
 * 第三方支付模块
 */
class PayFun{
    //支付宝--当面付--扫码付--获取二维码链接
    public function zfbDmf1Url($outTradeNo,$money){
        // 创建请求builder，设置请求参数
        $qrPayRequestBuilder = new \AlipayTradePrecreateContentBuilder();
        $qrPayRequestBuilder->setOutTradeNo($outTradeNo);
        $qrPayRequestBuilder->setTotalAmount($money);
        $qrPayRequestBuilder->setTimeExpress('5m');
        $qrPayRequestBuilder->setSubject('光速PDF服务支付');
        $qrPayRequestBuilder->setBody('光速PDF支付');

        // 调用qrPay方法获取当面付应答
        $qrPay = new \AlipayTradeService();
        $qrPayResult = $qrPay->qrPay($qrPayRequestBuilder);

        //	根据状态值进行业务处理
        $data['status'] = '0';
        switch ($qrPayResult->getTradeStatus()){
            case "SUCCESS":
                $response = $qrPayResult->getResponse();
//                 $qrcode = $qrPay->create_erweima($response->qr_code);
                $url = $response->qr_code;

                return $url;

                break;
            case "FAILED":
                echo "支付宝创建订单二维码失败!!!"."<br>--------------------------<br>";
                if(!empty($qrPayResult->getResponse())){
                    print_r($qrPayResult->getResponse());
                }
                break;
            case "UNKNOWN":
                echo "系统异常，状态未知!!!"."<br>--------------------------<br>";
                if(!empty($qrPayResult->getResponse())){
                    print_r($qrPayResult->getResponse());
                }
                break;
            default:
                echo "不支持的返回状态，创建订单二维码返回异常!!!";
                break;
        }
    }

    //微信--公众号支付
    public function wxJszf($outTradeNo,$money,$package){
        $arr = [];
        try{
            //①、获取用户openid
            $tools = new \JsApiPay();
            $openId = $tools->GetOpenid();
            if ($openId) {
                //②、统一下单
                $input = new \WxPayUnifiedOrder();
                $input->SetBody("光速PDF支付");
                $input->SetAttach("光速PDF服务支付");
                $input->SetOut_trade_no($outTradeNo);
                $input->SetTotal_fee($money*100);
                $input->SetTime_start(date("YmdHis"));
                $input->SetTime_expire(date("YmdHis", time() + 600));
                $input->SetGoods_tag($package);
                $input->SetNotify_url("http://pdf.66zip.cn/pay/notify");
                $input->SetTrade_type("JSAPI");
                $input->SetOpenid($openId);
                $order = \WxPayApi::unifiedOrder($input);

                $jsApiParameters = $tools->GetJsApiParameters($order);

                //获取共享收货地址js函数参数
                $editAddress = $tools->GetEditAddressParameters();

                $arr['jsApiParameters'] = $jsApiParameters;
                $arr['editAddress'] = $editAddress;
                return $arr;
            } else {
                echo '请重新扫码支付';die;
            }
        } catch(Exception $e) {
            \Log::ERROR(json_encode($e));
        }
    }
    
    //微信--扫码支付--查询订单
    public function actionWxSmzfQueryorder($transaction_id)
    {
        $input = new \WxPayOrderQuery();
        $input->SetTransaction_id($transaction_id);
        $result = \WxPayApi::orderQuery($input);
        \Log::DEBUG("query:" . json_encode($result));
        if(array_key_exists("return_code", $result)
            && array_key_exists("result_code", $result)
            && $result["return_code"] == "SUCCESS"
            && $result["result_code"] == "SUCCESS")
        {
            return true;
        }
        return false;
    }
    
    //微信退款
    public static function WxRefund($transaction_id,$total_fee,$refund_fee)
    {
        $total_fee = $total_fee*100;
        $refund_fee = $refund_fee*100;
        $input = new \WxPayRefund();
        $input->SetTransaction_id($transaction_id);
        $input->SetTotal_fee($total_fee);
        $input->SetRefund_fee($refund_fee);
        $input->SetOut_refund_no(\WxPayConfig::MCHID.date("YmdHis"));
        $input->SetOp_user_id(\WxPayConfig::MCHID);
        $result = \WxPayApi::refund($input);
        \Log::DEBUG("call back:" . json_encode($result));
        
        return $result;
    }
    
    //支付宝退款新
    public static function AliRefundNew($out_trade_no,$trade_no,$total_fee,$refund_fee,$reason,$out_request_no)
    {
        //商户订单号，商户网站订单系统中唯一订单号
        $out_trade_no = trim($out_trade_no);
        
        //支付宝交易号
        $trade_no = trim($trade_no);
        //请二选一设置
        
        //需要退款的金额，该金额不能大于订单金额，必填
        $refund_amount = trim($refund_fee);
        
        //退款的原因说明
        $refund_reason = trim($reason);
        
        //标识一次退款请求，同一笔交易多次退款需要保证唯一，如需部分退款，则此参数必传
//         $out_request_no = $out_request_no;
        
        //构造参数
        $RequestBuilder=new \AlipayTradeRefundContentBuilder();
        $RequestBuilder->setOutTradeNo($out_trade_no);
        $RequestBuilder->setTradeNo($trade_no);
        $RequestBuilder->setRefundAmount($refund_amount);
        $RequestBuilder->setOutRequestNo($out_request_no);
        $RequestBuilder->setRefundReason($refund_reason);
        
        $aop = new \AlipayTradeService();
        
        $result = $aop->refund2($RequestBuilder);
    
        return $result;
    }
}