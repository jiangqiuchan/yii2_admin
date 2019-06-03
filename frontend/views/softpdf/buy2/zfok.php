<!DOCTYPE html>
<html lang="en">

	<head>
		<meta charset="utf-8" />
		<title>极速PDF转Word软件官网，简单好用的PDF转换成Word转换器，最新版免费下载！</title>
		<meta name="Keywords" content="pdf转换成word，pdf转换成word转换器，pdf转换器免费下载，极速pdf转word">
		<meta name="Description" content="极速PDF转Word是一款国内最强最好用的PDF转换成Word转换器，小巧快速、启动快，极速完美转换，支持图文混合排版；非强度加密文档，1秒轻松解密，支持批量解密、转换；界面简洁大方，操作简单，支持会员任意选择页码进行转换；省时省力，大大提高办公效率，现在就推荐给身边的朋友使用！">
		<link rel="stylesheet" type="text/css" href="/css/css_2/softpdf/buy2/index.css?v=20180831">
	</head>

    <body>

    <div class="paySuccess">
        <img class="icon icon-pS" src="/images/images_2/softpdf/buy2/ico-paySuccess.png" />
        <div class="paydate">
            <p class="tips">购买日期</p>
            <p class="dateValue dateFrom"><?=isset($order['from']) ? $order['from'] : '--' ?></p>
        </div>
        <div class="paytime">
            <p class="tips">本次购买时长</p>
            <p class="dateValue dateLong"><?=isset($order['long']) ? $order['long'] : '--' ?></p>
        </div>
        <div class="endtime">
            <p class="tips">到期日期</p>
            <p class="dateValue dateTo"><?=isset($order['to']) ? $order['to'] : '--' ?></p>
        </div>
        <img src="/images/images_2/softpdf/buy2/nownBtn-h.png" class="hide" />
        <span class="knowBtn" id="knowBtn"></span>
    </div>

    <script src="/js/js_2/softpdf/buy/jquery.min.js"></script>
    <script src="/js/js_2/softpdf/buy2/js.js?v=?v=201901081"></script>
    <script type="text/javascript">
        paySuccess();
    </script>
    </body>

</html>