<?php 
use backend\components\commonality;

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
		<link rel="stylesheet" type="text/css" href="/css/css_2/softpdf/buy/index.css?v=20180831">
	</head>

	<body>
		<div class="container buy">
			<!--<div class="header clearfix">
				<div class="left">
					<img src="/images/images_2/softpdf/buy/logo.png" class="logo" />
					<span class="logoT">VIP充值</span>
				</div>
				<div class="right">
					<img class="new" src="/images/images_2/softpdf/buy/new.png" />
					<img class="close" src="/images/images_2/softpdf/buy/close.png" />
				</div>
			</div>-->
			<div class="buy-content">
				<div class="right">
					<div class="userinfo">
						<img class="userimg" src="<?=$img ? $img : '/images/images_2/softpdf/buy/userImg.png' ?>" />
						<p class="userName"><?=$is_login ? commonality::userTextDecode(Yii::$app->user->identity->username) : '- -' ?></p>
						<p class="date"><?=$expireTime ? $expireTime : '- -' ?></p>
					</div>
					<div class="package">
						<span>特权套餐：</span>
                        <ul class="clearfix">
                            <li data-time='半年'>半年
                                <p>4.83元/月</p>
                                <!--     								<i class="icon ico-half"></i>
                                                                    <i class="icon ico-buysel"></i> -->
                            </li>
                            <li data-time='1年' class="icon-pr pay-cur">
                                1年
                                <p>3.25元/月</p>
                                <!-- 								<i class="icon ico-half"></i>
                                                                <i class="icon ico-buysel"></i> -->
                            </li>
                            <li data-time='永久' class="last">永久
                                <p>0.09元/月</p>
                                <!--             	                <i class="icon ico-sub"></i>
                                                                <i class="icon ico-buysel"></i> -->
                            </li>
                        </ul>
					</div>
					<div class="pay">
						<span>支付方式：</span>
						<ul class="clearfix">
							<li id="wxPaySel" class="wx pay-cur"><i class="icon ico-buysel"></i></li>
							<li id="zfbPaySel" class="zfb"><i class="icon ico-buysel"></i></li>

						</ul>
					</div>
                    <p class="money">支付金额：<span id="xj" data-item="3" data-channel="<?php $channel ?>">48</span>元</p>
					<div id="zf" class="zfBtn"></div>
				   <div class="DiscountcheckC ">
						<div class="li" id="half">
							<i class="icon ico-checkBox checkBox on" ></i>
							半价特权（第二次购买享受半价优惠）
							<i class="icon ico-yhj"></i>
						</div>
						<div class="li" id="subMoney">
							<i class="icon ico-checkBox checkBox" ></i>
							5元红包（满10元可用）
							<i class="icon ico-hb"></i>
						</div>
					</div>
					<div class="tkC">
						支付则默认同意
						<a href="/site2/clause" target="_blank">《光速服务条款》</a>
					</div>
					<img class="hytq" src="/images/images_2/softpdf/buy/hytq.png" />
					<span class="lineQQ icon" id="lxQQ"></span>
				</div>
			</div>
			<!-- 活动2 -->
			<div class="mask hide">
				<div class="pop">
					<div class="close" id="popclose">
						<img src="/images/images_2/softpdf/buy/mask-close-h.png" class="hide"/>
					</div>
					<p class="date">
						活动时间：2018年8月30日-2018年9月6日
					</p>
					<div class="okBtn" id="popYes">
						<img src="/images/images_2/softpdf/buy/yes-h.png" class="hide"/>
						<img src="/images/images_2/softpdf/buy/yes-a.png" class="hide"/>
					</div>
				</div>
			</div>
		</div>
		<script src="/js/js_2/softpdf/buy/jquery.min.js"></script>
		 <!-- 活动2 -->
	    <script src="/js/js_2/softpdf/buy/store.legacy.min.js"></script>
		<script src="/js/js_2/softpdf/buy/js.js?v=20181207"></script>
		<script type="text/javascript">
		</script>
	</body>

</html>