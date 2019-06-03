<?php
use backend\components\commonality;
use frontend\models\activity\Act2;

$channel = Yii::$app->user->identity->channel;
$channelArr = ['P5','P6'];

if (!Yii::$app->user->isGuest) {
    $is_login = 1;
    $userId = Yii::$app->user->id;
} else {
    $is_login = 0;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>极速PDF转Word软件官网，简单好用的PDF转换成Word转换器，最新版免费下载！</title>
    <meta name="Keywords" content="pdf转换成word，pdf转换成word转换器，pdf转换器免费下载，极速pdf转word">
    <meta name="Description" content="极速PDF转Word是一款国内最强最好用的PDF转换成Word转换器，小巧快速、启动快，极速完美转换，支持图文混合排版；非强度加密文档，1秒轻松解密，支持批量解密、转换；界面简洁大方，操作简单，支持会员任意选择页码进行转换；省时省力，大大提高办公效率，现在就推荐给身边的朋友使用！">
    <link rel="stylesheet" type="text/css" href="/css/css_2/softpdf/buy2/index.css?v=20180832">
</head>

<body style="background-color: #f4f4f4;">
<div class="container buy">
    <div class="header clearfix">
        <div class="left">
            <img src="/images/images_2/softpdf/buy2/logo.png" class="logo" />
            <!--<span class="logoT">VIP充值</span>-->
        </div>
        <div class="right">
            <!--<img class="new" src="/images/images_2/softpdf/buy2/new.png" />-->
            <img class="close" src="/images/images_2/softpdf/buy2/close.png" />
        </div>
    </div>
    <div class="userInfoC">
        <img src="<?=$img ? $img : '/images/images_2/softpdf/buy/userImg.png' ?>" class="userImg"/>
        <img src="/images/images_2/softpdf/buy2/headMask.png" class="userImg2"/>
        <div class="userNameC">
            <span class="userName"><?=$is_login ? commonality::userTextDecode(Yii::$app->user->identity->username) : '- -' ?></span><?=$expireTime ? '<i class="icon ico-vip"></i>' : '' ?>
        </div>
        <div class="vipInfo">
            <?=$expireTime ? '到期时间：'.$expireTime : '您还不是光速PDF会员' ?>
        </div>
    </div>
    <div class="proInfo ">

    </div>
    <div class="buySeleC">
        <p class="tit">套餐选择：</p>
        <div class="package">
            <ul class="clearfix">
                <li data-time='半年'>
                    38
                    <span>/半年</span>
<!--                    <p class="tips">赠送：N次人工服务</p>-->
                </li>
                <li data-time='1年' class="icon-pr ">
                    48
                    <span>/1年</span>
<!--                    <p class="tips">赠送：N次人工服务</p>-->
                </li>

                <li data-time='永久' class="last pay-cur">
                    78
                    <span>/永久</span>
                    <i class="icon ico-cz"></i>
<!--                    <p class="tips">赠送：N次人工服务</p>-->
                </li>
            </ul>
        </div>
    </div>
    <div class="pauAway">
        <p class="tit">支付方式：</p>
        <div class="payC">
            <div class="ewmC">
                <img class="ewm" src=""/>
                <img class="icon-reload hide" src="/images/images_2/softpdf/buy2/ico-reload.png"/>
            </div>
            <div id="cdtime" class="hide">120</div>
            <p class="money">支付金额：<span id="xj">78</span>&nbsp元<i>（<span id="yj">已优惠 30元</span>）</i></p>
            <div class="endDate">
                权益截止时间：<i id="endTimeN">永久</i>
            </div>
            <a class="tk" href="/site2/clause" target="_blank">《光速服务条款》</a>
        </div>

    </div>

</div>
<script src="/js/js_2/softpdf/buy2/jquery.min.js"></script>
<script src="/js/js_2/softpdf/buy2/js.js?v=20190108"></script>
<script language="javascript" type="text/javascript">
    getOrderData(6);
    headImg();
</script>
<!--<script language="javascript" type="text/javascript">
function foramtTelNum(str)
{
var content="";
for(var i=0;i<str.length;i++)
{
content += "<img src='" + str.substring(i,i+1)+ ".gif' />";
}
return content;
}
//比如我要显示123456789电话号码，我需要这样写
var c = foramtTelNum("123456789");
document.write(c);
</script>-->
</body>

</html>