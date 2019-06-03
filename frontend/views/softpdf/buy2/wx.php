<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>光速PDF微信支付</title>
</head>
<body>
<br>
<div align="center">
    <h7>交易进行中...</h7>
</div>
<hr>
订单号：<?=$data['out_trade_no'] ?><br/>
金额：<?=$data['money'] ?><br/>
<br>
<div align="center">
    <button style="width:100%; height:40px; border-radius: 15px;background-color:#FE6714; border:0px #FE6714 solid; cursor: pointer;  color:white;  font-size:16px;" type="button" onclick="callpay()" >重新支付</button>
</div>

</body>
<script type="text/javascript">
    //调用微信JS api 支付
    function jsApiCall()
    {
        WeixinJSBridge.invoke(
            'getBrandWCPayRequest',
            <?php echo $jsApiParameters; ?>,
            function(res){
                WeixinJSBridge.log(res.err_msg);
//                    alert(res.err_code+res.err_desc+res.err_msg);
                var str = res.err_msg;
                var msgArr = str.split(':');
                if(msgArr[1] == 'ok'){
                    document.getElementsByTagName('body')[0].innerHTML='支付成功，可关闭页面';
                } else if(msgArr[1] == 'cancel') {
                } else {
                    document.getElementsByTagName('body')[0].innerHTML='支付失败';
                }
            }
        );
    }

    function callpay()
    {
        if (typeof WeixinJSBridge == "undefined"){
            if( document.addEventListener ){
                document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
            }else if (document.attachEvent){
                document.attachEvent('WeixinJSBridgeReady', jsApiCall);
                document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
            }
        }else{
            jsApiCall();
        }
    }

    callpay();
</script>
</html>