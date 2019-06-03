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

function CallCppNewBuyWeb() {
    if(typeof(window.external.CallNewBuyWeb) == 'undefined') {

        return 0;
    }
    /*获取版本id,旧版无此接口,新版返回1*/
    return window.external.CallNewBuyWeb();
}

if(CallCppNewBuyWeb() == 0) {
    //隐藏头部
    $('.header').addClass('hide');
}

//刷新当前页
$('.new').on('click', function() {
    location.reload();
});
//关闭窗体
$('.close').on('click', function() {
    CallCppCloseWnd();
});
//联系QQ
$('#lxQQ').on('click', function() {
    CallCppQQ();
});
//------------------------------------------------------------------------------

//获取订单信息
var wxPayTimer = '';
var orderId = '';
function getOrderData(itemtype,now) {
    var url = "/soft-base/get-ewm2";
    var data = {itemtype:itemtype,referer:'pdf'};

    if(orderId) {
        url = "/soft-base/refresh-order2";
        data = {orderId:orderId};
    }

    $.ajax({
        type: "POST",
        dataType:'json',
        url: url,
        data: data,
        async: false,
        success: function success(data) {
            if(data.status == '1') {
                $('.ewmC .ewm').attr('src',data.img);
                orderId = data.orderid;

                if(now){
                    getMa({time: 120},$('#cdtime'));
                }else{
                    getMa(ewmCd,$('#cdtime'));
                }

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
                                window.location.href = "http://pdf.66zip.cn/softpdf/buy2/paid?param="+orderId;
                            } else if(data.status == '2') {
                                clearInterval(wxPayTimer);
                                alert("订单支付失败");
                            }
                        }
                    });
                }, 3000);
            } else {
                //活动
//        		alert(data.msg);
                console.log(data.msg);
            }
        }
    });
}

//计时器秒
var ewmCd = {
    time: 120
};

var mes = '';
function getMa(now,ele) {
    var _this = ele;
    mes = setInterval(function() {
        now.time--;
        _this.text(now.time);
        // _this.text(now.time);
        //时间到，二维码失效
        if(now.time == 0) {
            clearInterval(mes);
            $('.icon-reload').removeClass('hide');

            now.time = 120;

            //停止请求
            clearInterval(wxPayTimer);
        }
    }, 1000)
}

//获取二维码
$('.icon-reload').on('click',function(){
    var item = $('#xj').data('item');
    getOrderData(item,1);

    $('.icon-reload').addClass('hide');
});

//页面价格
var item = 6;
$('.package ul li,.pay ul li').on('click', function() {
    $(this).addClass('pay-cur').siblings('li').removeClass('pay-cur');
    var currentDate = $.trim($(this).data('time'));
    if(currentDate == '7天') {

    } else if(currentDate == '半年') {
        $('#xj').text('38');
        $('#yj').text('已优惠 10元');
        $('#endTimeN').text(endTime(180));
        $('#xj').data('item','7');
        item = 7;
    } else if(currentDate == '1年') {
        $('#xj').text('48');
        $('#yj').text('已优惠 20元');
        $('#endTimeN').text(endTime(370));
        $('#xj').data('item','3');
        item = 3;
    }else if(currentDate == '永久') {
        $('#xj').text('78');
        $('#yj').text('已优惠 30元');
        $('#endTimeN').text('永久');
        $('#xj').data('item','6');
        item = 6;
    }

    clearInterval(mes);
    clearInterval(wxPayTimer);
    $('.icon-reload').addClass('hide');
    orderId = '';
    setTimeout(function () {
        getOrderData(item,1);
    },100)
});

function endTime(long){
    var d = new Date();
    d.setDate(d.getDate() + long);
    var yy1 = d.getFullYear();
    var mm1 = d.getMonth()+1;//因为getMonth（）返回值是 0（一月） 到 11（十二月） 之间的一个整数。所以要给其加1
    var dd1 = d.getDate();
    if (mm1 < 10 ) {
        mm1 = '0' + mm1;
    }
    if (dd1 < 10) {
        dd1 = '0' + dd1;
    }
    // console.log(yy1 + '-' + mm1 + '-' + dd1);
    return (yy1 + '-' + mm1 + '-' + dd1);
}

$('.package ul li').mouseenter(function(){
    if(!$(this).hasClass('pay-cur')){
//					console.log("in");
        $(this).addClass('hover');
    }
}).mouseleave(function(){
    // if(!$(this).hasClass('pay-cur')){
//				console.log("out");
        $(this).removeClass('hover');
    // }

});

function headImg() {
    var img = getQueryString('headimg');
    var src = '';
    if(img){
        src = '/images/images_2/softpdf/buy2/'+img+'.png';
    }

    if(src){
        $('.userImg').attr('src',src);
    }
}

//获取 url 参数
function getQueryString(name) {
    var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
    var r = window.location.search.substr(1).match(reg);
    if(r != null) return unescape(r[2]);
    return null;
}

function paySuccess() {
    //改变窗口大小
    CallCppSetWndSize(550,273);
    //支付成功
    CallCppBuySuccess();

}
$('#knowBtn').on('click', function() {
    CallCppCloseWnd();
});