<!DOCTYPE html>
<html lang="en">

	<head>
		<meta charset="utf-8" />
		<title>极速PDF转Word软件官网，简单好用的PDF转换成Word转换器，最新版免费下载！</title>
		<meta name="Keywords" content="pdf转换成word，pdf转换成word转换器，pdf转换器免费下载，极速pdf转word">
		<meta name="Description" content="极速PDF转Word是一款国内最强最好用的PDF转换成Word转换器，小巧快速、启动快，极速完美转换，支持图文混合排版；非强度加密文档，1秒轻松解密，支持批量解密、转换；界面简洁大方，操作简单，支持会员任意选择页码进行转换；省时省力，大大提高办公效率，现在就推荐给身边的朋友使用！">
		<link rel="stylesheet" type="text/css" href="/css/css_2/softpdf/buy/index.css">
	</head>

	<body>
		<div class="zf" style="margin:0 auto;">
			<!--<div class="header clearfix">
				<div class="left">
					<span class="yf">应付：</span>
					<span class="money">￥<i id='payMoneyNum'>19.9</i>元</span>
				</div>
				<div class="right">
					<img class="new" src="/images/images_2/softpdf/buy/new.png"/>
					<img class="close" src="/images/images_2/softpdf/buy/close.png"/>
				</div>
			</div>-->
			<div class="zfC">
				<div class="left">
					<div class="tit">
						<i class="icon icon-zfb"></i>
						<span>支付宝扫码支付</span>
					</div>
					<!-- 活动 -->
					<div class="ewmC"  data-itemtype="<?=$itemType ?>" data-paytype="<?=$payType ?>" data-userid="<?=Yii::$app->user->identity->id ?>" >
						<img src=""/>
						<div class="mask hide">
							<span id="getEwm">点击刷新二维码</span>
						</div>
					</div>
					<p class="tips" id="payTips">请支付宝扫码支付，二维码有效期还剩 <span id="cdtime">120</span>s</p>
					<p class="invalid hide">二维码已过期</p>
				</div>
				<img class="right zfb" src="/images/images_2/softpdf/buy/zfbsm.png"/>
			</div>
		</div>
		<script src="/js/js_2/softpdf/buy/jquery.min.js"></script>
		<script src="/js/js_2/softpdf/buy/js.js?v=20180921"></script>
		
	</body>
	<script type="text/javascript">
		getOrderData()
	</script>

</html>