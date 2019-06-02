<?php

use yii\helpers\Html;
use yii\grid\GridView;
use backend\components\commonality;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\SoftPdfOrderSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$module_type = yii::$app->getRequest()->get('module_type');
if (isset($module_type)) {
    switch ($module_type)
    {
        case 5:
            $this->title = '订单总数 ';
            break;
        case 6:
            $this->title = '今日订单 ';
            break;
        default:
            $this->title = 'pdf订单';
    }
} else {
    $this->title = '订单列表';
}
if ($this->context->action->id == 'refund') {
    $this->title = '退款列表';
}
$this->params['breadcrumbs'][] = $this->title;

$date1 = yii::$app->getRequest()->get('start_at1');
$date2 = yii::$app->getRequest()->get('end_at1');
$date3 = yii::$app->getRequest()->get('gmt_payment');
?>

<?php
use yii\bootstrap\Modal;
use backend\models\SoftPdfOrder;
//创建修改modal
Modal::begin([
    'id' => 'update-modal',
    'header' => '<h4 class="modal-title">更新</h4>',
]);
Modal::end();
?>
<div class="soft-pdf-order-index">

    <h3><?= Html::encode($this->title) ?></h3>
    
    <?php  echo $this->render('_search', ['model' => $searchModel]); ?>

    <div class="box">
        <div class="box-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
    //         'filterModel' => $searchModel,
            'showFooter' => true,
            "layout" => "{items}\n{pager}\n{summary}",
            'columns' => [
                [
                    'attribute' => 'user_id',
                    'options' => ['width' => '5%'],
                    'footer' => '总金额：<span class="show-money" data-query="'.$dataProvider->query->createCommand()->getRawSql().'"></span>',
                ],
                [
                    'attribute' => 'trade_no',
                    'options' => ['width' => '7%'],
    //                 'value' => function ($model) {
    //                     return substr($model->trade_no,0,15);
    //                 },
    //                 'footerOptions' => ['class'=>'hide']
                ],
                [
                    'attribute' => 'username',
                    'options' => ['width' => '7%'],
                    'value' => function ($model) {
                        return isset($model->pdfUser) ? commonality::userTextDecode($model->pdfUser->username) : '';
                    }
                ],
                [
                    'attribute' => 'channel',
                    'value' => function($model){
                        return $model->pdfUser->channel ? $model->pdfUser->channel : '';
                    },
                    'options' => ['width' => '5%'],
                ],
                [
                    'attribute' => 'referer',
                    'value' => function($model){
                        return (!$model->referer || $model->package == 11) ? '' : $model->referer;
                    },
                    'options' => ['width' => '5%'],
                ],
                [
                    'attribute' => 'created_at',
                    'options' => ['width' => '10%'],
                    'value' => function($model){
                    return date('Y-m-d H:i:s', $model->created_at);
                    },
                    'filter' => Html::input('text', 'start_at1', $date1, ['class' => 'required','id' => 'start_at1','style' => 'width:80px;']) ."--".Html::input('text', 'end_at1', (!empty($date2))?$date2:date('Y-m-d',time()), ['class' => 'required','id' => 'end_at1','style' => 'width:80px;']),
                ],
                [
                    'attribute' => 'recharges',
                    'value' => function($model){
                        return $model->recharges ? $model->recharges : '0';
                    },
                    'options' => ['width' => '5%'],
                ],
                [
                    'attribute' => 'gmt_payment',
                    'options' => ['width' => '10%'],
                    'value' => function($model){
                    return date('Y-m-d H:i:s', $model->gmt_payment);
                    },
                    'filter' => Html::input('text', 'gmt_payment', $date3, ['class' => 'required','id' => 'gmt_payment','style' => 'width:80px;']),
                ],
                [
                    'attribute' => 'receipt_amount',
                    'options' => ['width' => '5%'],
                ],
                [
                    'attribute' => 'package',
                    'options' => ['width' => '10%'],
                    'value' => function ($model) {
                        $arr = SoftPdfOrder::getPackageArr(1);
                        return isset($arr[$model->package]) ? $arr[$model->package] : '';
                    },
                    'filter' => Html::activeDropDownList($searchModel,
                        'package',SoftPdfOrder::getPackageArr(2),
                        ['prompt' => '所有'])
                ],
                [
                    'attribute' => 'pay_type',
                    'options' => ['width' => '5%'],
                    'filter' => Html::activeDropDownList($searchModel,
                        'pay_type',['alipay' => 'alipay','weixin' => 'weixin'],
                        ['prompt' => '所有'])
                ],

                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{refund}',
                    'options' => ['width' => '5%'],
                    'header' => '操作',
                    'buttons' => [
                        'update' => function ($url, $model, $key) {
                            return Html::a('<span class="glyphicon glyphicon-pencil"></span>', 'javascript:void(0)', [
                                'data-toggle' => 'modal',
                                'data-target' => '#update-modal',
                                'class' => 'data-update',
                                'data-id' => $model->user_id,
                            ]);
                        },
                        'refund' => function ($url, $model, $key) {
                            if ($model->pay_status == '1' && $model->package != 11) {
                                return Html::a('退款','#',[
                                    'title' => '退款',
                                    'class' => 'btn btn-default btn-xs refundbtn',
                                    'data-target' => '#refund',
                                    'data-toggle' => 'modal',
                                    'data-id' => $model->id,
                                    'data-money' => $model->receipt_amount,
                                    'data-type' => $model->pay_type,
                                ]);
                            }else{
                                return '';
                            }
                        },
                    ]
                ],
            ],
        ]); ?>
        </div>
    </div>
    
    <!-- 确认退款 -->
    <div class="modal fade" id="refund" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                    <h4 class="modal-title" id="myModalLabel">确认退款</h4>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label class="control-label">密码</label>
                        <input type="password" class="refund-psw form-control" placeholder="请输入退款密码">
                        <br>
                        <label class="control-label">退款金额</label>
                        <input type="text" class="refund-money form-control">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" id="refundact">确认</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal -->
    </div>
</div>

<?php 
$script = <<<JS
$(function(){
    // 时间搜索框
    // $('#start_at1,#end_at1,#softpdfordersearch-gmt_payment,#softpdfordersearch-created_at').datepicker({
    //     autoclose: true,
    //     format : 'yyyy-mm-dd',
    //     language:"zh-CN", 
    // });
    $('input[name="SoftPdfOrderSearch[created_at]"]').daterangepicker({
         format: 'YYYY-MM-DD',
         autoUpdateInput:false
     });
    //清空modal
    $('.modal').on('hidden.bs.modal', function () {
        $("#update-modal .modal-body").empty();
    })  
    //创建修改modal
    $('.data-update').on('click', function () {
    var id = $(this).attr("data-id");
    if (id == '') {
        $('#update-modal .modal-title').html("添加");
        var url = '/soft-pdf-user/create';
    } else {
        $('#update-modal .modal-title').html("编辑分类");
        var url = '/soft-pdf-user/update';
    }
        $.get(url, { id: id },
            function (data) {
                $('#update-modal .modal-body').html(data);
            }  
        );
    });
    //微信退款确认
    var orderId = '';
    var orderFee = '';
    var orderType = '';
    $('.refundbtn').on('click', function() {
        orderId = $(this).attr('data-id');
        orderType = $(this).attr('data-type');
        orderFee = $(this).attr('data-money');
    
        $('.refund-money').val(orderFee);
    })
    $('#refundact').on('click', function() {
        var psw = $('.refund-psw').val();
        orderFee = $('.refund-money').val();
    
        $.ajax({
            url:'/soft-pdf-order/refund-act',
            type:'post',
            dataType:'json',
            data:{psw:psw,id:orderId,type:orderType,fee:orderFee},
            success:function(data) {
                if (data.status == 0) {
    				alert(data.msg);    
                } else {
    				alert(data.msg);    
                    window.location.reload();
                }
            },
            error:function(data) {}
        });
    })
    //支付宝退款
//     $('.alirefundbtn').on('click', function() {
//         var id = $(this).attr('data-id');
//         $.get('/soft-pdf-order/ali-refund', { id: id },
//             function (data) {
//                 $('html').html(data);
//             }  
//         );
//     })
    //计算筛选后总金额
//     $('.btn-money').click(function() {
//         var query = $(this).attr('data-query');
//         $.get('/soft-pdf-order/total-money',{ query: query},function(data){
//             $('.show-money').html(data.msg);
// 	    },'json')
//     })
});
//页面加载完执行
window.onload=function (){
    var query = $('.show-money').attr('data-query');
    $.get('/soft-pdf-order/total-money',{query: query},function(data){
        $('.show-money').html(data.msg);
    },'json')
}
JS;
$this->registerJs($script);
?>