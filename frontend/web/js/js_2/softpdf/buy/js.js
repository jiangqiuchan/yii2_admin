$(function() {
	//c++ 交互

	function CallCppQQ() {
		/*点击QQ回调函数*/
		window.external.CallShowQQWnd();
	}

	function CallCppBuySuccess() {
		/*购买结束回调函数
		@param x  bool值
		*/
		window.external.CallBuyEnd(true);
	}
	function CallCppSetWndSize(w, h) {
		/*改变窗口大小回调函数 CallSetWndSize(w=-1, h=-1)
			w  新的窗口宽度(-1,使用原宽度)
			h  新的窗口高度(-1,使用原高度)
		*/
		window.external.CallSetWndSize(w, h);
	}

	function CallCppCloseWnd() {
		/*关闭窗口回调函数*/
		window.external.CallCloseWnd();
	}

	function CallCppSetPrice(price)
	{
	    /*设置标题栏价格,参数为*/
	    window.external.CallSetPrice(price);
	}

	//刷新当前页
	$('.new').on('click', function() {
		location.reload();
	});
	//关闭窗体
//	$('.close').on('click', function() {
//		CallCppCloseWnd();
//	});
	//联系QQ
	$('#lxQQ').on('click', function() {
		CallCppQQ();
	});
	
	//微信支付 weixin  ，支付宝支付alipay
	var payType='weixin';
	//微信支付点击
	$('#wxPaySel').on('click',function(){
		payType='weixin';
	});
	$('#zfbPaySel').on('click',function(){
		payType='alipay';
	});
	
	// 点击支付
	$('#zf').on('click', function() {
		
		var Baseurl = 'http://pdf.66zip.cn/softpdf/buy/qr-code';
		var itemType = $('#xj').data('item');
		var price = $('#xj').text();
		//活动2
		var saleType = getSaleType();
		
        if ($('.userinfo .userName').text() == '- -') {
            alert("登录失败");
        } else if ($('.userinfo .date').text() == '永久') {
            alert("你已支付过永久套餐，不需再支付");
        } else {  
			Baseurl+='?itemType='+itemType+'&payType='+payType+'&saleType='+saleType;
			
			location.href=Baseurl;
        }
        
        CallCppSetPrice(price)
//		CallCppSetWndSize(715, -1);
	});
	
	
	
});
//获取 url 参数
function getQueryString(name) { 
        var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i"); 
        var r = window.location.search.substr(1).match(reg); 
        if (r != null) return unescape(r[2]); 
        return null; 
    } 

//获取订单信息
var wxPayTimer = '';
var orderId = '';
function getOrderData() {
    var itemtype = $('.ewmC').data('itemtype');
    var pay_type = $('.ewmC').data('paytype');
	var user_id = $('.ewmC').data('userid');

	//活动2-选择优惠券
	var saleType = $('.ewmC').data('saletype');
	
	var url = "/soft-base/pay";
	var data = {user_id:user_id,itemtype:itemtype,pay_type:pay_type,saleType:saleType,referer:'pdf'};
	
	if(orderId) {
		var url = "/soft-base/refresh-order";
		var data = {orderId:orderId};
	}

    $.ajax({
        type: "POST",
        dataType:'json',
        url: url,
        data: data,
        async: false,
        success: function success(data) {
        	if(data.status == '1') {
		    	$('.ewmC img').remove();
                $('.ewmC').append(data.img);
              
                orderId = data.orderid;
                getMa(ewmCd,$('#cdtime'));
                
		        //微信有无支付成功
                wxPayTimer = setInterval(function() {
	                $.ajax({
		                type: "POST",
		                url: "/pay/wx-pay-status",
		                data: {orderId:orderId},
		                dataType:'json',
		                success: function success(data) {
		            	    if (data.status == '1') {
		            	    	clearInterval(wxPayTimer);
	            		    	window.location.href = "http://pdf.66zip.cn/softpdf/buy/paid";
		            	    } else if(data.status == '2') {
		            	    	clearInterval(wxPayTimer);
		            		    alert("订单支付失败");
		            	    }	  			                  
		                }
	                });
		        }, 5000);
        	} else {
        		//活动
//        		alert(data.msg);
        	}
        }
    });
}

//计时器秒
var ewmCd = {
	time: 120
};
	
function getMa(now, ele) {
	var _this = ele;
	var mes = setInterval(function() {
		now.time--;
		_this.text(now.time);
		//时间到，二维码失效
		if(now.time == 0) {
			clearInterval(mes);
			$('.ewmC .mask').removeClass('hide');
			$('.invalid').removeClass('hide');
			$('#payTips').addClass('hide');
			
			now.time = 120;
			
			//停止请求
			clearInterval(wxPayTimer);
		}
	}, 1000)
}

//获取二维码
$('#getEwm').on('click',function(){
	getOrderData();
	
	$('.ewmC .mask').addClass('hide');
	$('#payTips').removeClass('hide');
	$('.invalid').addClass('hide');
//	getMa(ewmCd,$('#cdtime'));
	//微信支付 1  ，支付宝支付 2
//	payType;
});

//页面价格
//12,13,14为临时价格
$('.package ul li,.pay ul li').on('click', function() {
	$(this).addClass('pay-cur').siblings('li').removeClass('pay-cur');
	var currentDate = $.trim($(this).data('time'));
	if(currentDate == '7天') {
		$('#xj').text('15');
		$('#yj').text('原价30元');
		$('#xj').data('item','1');
	} else if(currentDate == '1个月') {
		$('#xj').text('19');
		$('#yj').text('原价59元');
		$('#xj').data('item','2');
	} else if(currentDate == '1年') {
		$('#xj').text('48');
		$('#yj').text('原价99元');
		$('#xj').data('item','3');
	} else if(currentDate == '永久') {
		$('#xj').text('78');
		$('#yj').text('原价999元');
		$('#xj').data('item','6');
	} else if(currentDate == '半年') {
		$('#xj').text('38');
		$('#yj').text('原价79元');
		$('#xj').data('item','7');
	} else if(currentDate == '半年2') {
        $('#xj').text('38');
        $('#yj').text('原价79元');
        $('#xj').data('item','12');
    } else if(currentDate == '1年2') {
        $('#xj').text('48');
        $('#yj').text('原价79元');
        $('#xj').data('item','13');
    } else if(currentDate == '永久2') {
        $('#xj').text('78');
        $('#yj').text('原价79元');
        $('#xj').data('item','14');
    } else if(currentDate == '1个月2') {
        $('#xj').text('28');
        $('#xj').data('item','15');
    }
});
//活动2
var isAccord=false;//是第二次购买 ，默认 false
var halfCan=false;//可以享受半价
var subMoneyCan=false;//可以减5元
var halfIsCheck=true;//已经享受了半价
var subMoneyIsCheck=false;//已经-了5元
var selTC=3;//选择的套餐
//优惠类型：0无 1半价 2减5 3半价且减5 4减25 5减30
var saleType=0;

isAccord=false;//$('.actParam').attr('discount');//是第二次购买 ，默认 false
halfCan=false;//$('.actParam').attr('discount');//可以享受半价
subMoneyCan=false;//$('.actParam').attr('prize');//可以减5元

// 赋值 isAccord 可以不用js 直接用php赋值和显示 DiscountcheckC 更好
if(isAccord){
	$('.DiscountcheckC').show();
	if(subMoneyCan){
		//默认一年 39/2 -5 = 14.5
		$('#xj').text('19.5');
	}else{
		//第三次不优惠5元
		//默认一年 39/2 = 14.5
		$('#xj').text('19.5');
		$('#subMoney .checkBox').addClass('n');
		subMoneyIsCheck=false;
	}
}
//checkbox
//半价
$('#half').click(function(){
	if(isAccord){
		var checkbox=$(this).find('.checkBox');
			if(checkbox.hasClass('n')){
				return ;
			}else if(checkbox.hasClass('on')){
				//取消
				halfIsCheck=false;
				checkbox.removeClass('on');
				var money = $('#xj').text()-0;
//				console.log(money);
				if(subMoneyIsCheck==true && subMoneyCan==true){
					//(14.5+5)*2-5
					money=(money+5)*2-5;
//					console.log(money);
					$('#xj').text(money);
				}else{
					money=money*2;
					$('#xj').text(money);
				}
				
			}else{
				//勾选
				halfIsCheck=true;
				checkbox.addClass('on');
				var money = $('#xj').text()-0;
				if(subMoneyIsCheck==true && subMoneyCan==true){
					//34+5
					money=(money+5)/2-5;
					$('#xj').text(money);
				}else{
					money=money/2;
					$('#xj').text(money);
				}

				
			}
	}
			
});
//-5元
$('#subMoney').click(function(){
	if(isAccord){
		var checkbox=$(this).find('.checkBox');
			if(checkbox.hasClass('n')){
				return ;
			}else if(checkbox.hasClass('on')){
				//取消
				subMoneyIsCheck=false;
				checkbox.removeClass('on');
				var money = $('#xj').text()-0;
//				console.log(money);
					money=money+5;
					$('#xj').text(money);

				
			}else{
				//勾选
				subMoneyIsCheck=true;
				checkbox.addClass('on');
				var money = $('#xj').text()-0;
					money=money-5;
					$('#xj').text(money);

				
			}
	}
})

// $('.package ul li,.pay ul li').on('click', function() {
// 	$(this).addClass('pay-cur').siblings('li').removeClass('pay-cur');
// 	var currentDate = $.trim($(this).data('time'));
// 	if(currentDate == '7天') {
// 		$('#xj').data('item','1');
// 		selTC=1;
// 		if(halfCan){
// 			$('#xj').text('7.5');
// 			halfIsCheck=true;
// 			$('#half .checkBox').removeClass('n').addClass('on');
//
// 		}else{
// 			$('#xj').text('15');
// 		}
// 		$('#subMoney .checkBox').addClass('n');
// 		subMoneyIsCheck=false;
// 	} else if(currentDate == '1个月') {
// 		$('#xj').data('item','2');
// 		selTC=2;
// 		if(halfCan){
// 			$('#xj').text('9.5');
// 			halfIsCheck=true;
// 			$('#half .checkBox').removeClass('n').addClass('on');
//
// 		}else{
// 			$('#xj').text('19');
// 		}
// 		$('#subMoney .checkBox').addClass('n');
// 		subMoneyIsCheck=false;
// //
// 	} else if(currentDate == '1年') {
// 		$('#xj').data('item','3');
// 		selTC=3;
// 		if(halfCan){
// 			$('#xj').text(39/2);
// 			halfIsCheck=true;
// 			$('#half .checkBox').removeClass('n').addClass('on');
// 			if(subMoneyCan){
// //				$('#xj').text((39/2)-5);
// 				$('#subMoney .checkBox').removeClass('n').removeClass('on');
// //
// //				subMoneyIsCheck=true;
// 			}else{
//
// 				$('#subMoney .checkBox').addClass('n');
// 				subMoneyIsCheck=false;
// 			}
// 		}else{
// 			$('#xj').text('39');
// 		}
//
// 	} else if(currentDate == '永久') {
// 		$('#xj').data('item','6');
// 		selTC=4;
// 		halfIsCheck=false;
// 			$('#half .checkBox').removeClass('on').addClass('n');
// 			if(subMoneyCan){
// 				$('#subMoney .checkBox').removeClass('n').removeClass('on');
// //				subMoneyIsCheck=true;
// 			}
// 			if(isAccord){
// 				$('#xj').text(99-25);
// 			}else{
// 				$('#subMoney .checkBox').addClass('n');
// 				subMoneyIsCheck=false;
// 				$('#xj').text(99);
// 			}
// //
// 	} else if(currentDate == '半年') {
// 		$('#xj').data('item','7');
// 		selTC=5;
// 		if(halfCan){
// 			$('#xj').text(29/2);
// 			halfIsCheck=true;
// 			$('#half .checkBox').removeClass('n').addClass('on');
// 			if(subMoneyCan){
// 				$('#xj').text(29/2);
// 				$('#subMoney .checkBox').removeClass('n').removeClass('on');
// //
// //				subMoneyIsCheck=true;
// 			}else{
//
// 				$('#subMoney .checkBox').addClass('n');
// 				subMoneyIsCheck=false;
// 			}
// 		}else{
// 			$('#xj').text('29');
// 		}
//
// 	}
// });

//活动2--获取优惠内容
function getSaleType() {
//	console.log("套餐值selTC---"+selTC);
	//优惠类型：0无 1半价 2减5 3半价且减5 4减25 5减30
//	halfIsCheck=true;//已经享受了半价
//	subMoneyIsCheck=true;//已经-了5元
	if(isAccord){
		//第二次购买
		if(selTC==4){
			//永久
			if(subMoneyIsCheck){
				//-30
				saleType=5;
			}else{
				//-25
				saleType=4;
			}
		}else{
			//其它套餐
			if(halfIsCheck==true && subMoneyIsCheck==false){
			//只用半价劵
			saleType=1;
			}else if(halfIsCheck==false && subMoneyIsCheck==true){
				//只用-5劵
				saleType=2;
			}else if(halfIsCheck==true && subMoneyIsCheck==true){
				//都用
				saleType=3;
			}else if(halfIsCheck==false && subMoneyIsCheck==false){
				saleType=0;
			}
		}
		
		
	}else{
		//第一次购买
		saleType=0;
	}
	
//	console.log("优惠类型---"+saleType);
	return saleType;
}

//只有第一次显示
//alert(store.get('first'));
//if(store.get('first')!='true'){
//	$('.mask').removeClass('hide');
//}
//
//$('#popclose,#popYes').click(function(){
//	$('.mask').addClass('hide');
//	store.set('first', 'true');
//});
//	store.clearAll();
