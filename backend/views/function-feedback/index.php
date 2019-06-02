<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\FunctionFeedbackSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '功能反馈列表';
$this->params['breadcrumbs'][] = $this->title;

$date1 = yii::$app->getRequest()->get('start_at1');
$date2 = yii::$app->getRequest()->get('end_at1');
?>
<style>
.card-box {
    width: 100%;
}
.filters select,input{
	height:34px
}
.table td,th{
 	text-align: center; 
	font-size: 15px;
}
/* .table td:nth-child(4){ */
/*  	text-align: left;  */
/* } */
</style>
<div class="function-feedback-index">

    <h3><?= Html::encode($this->title) ?></h3>

    <div class="box">
        <div class="box-body">
            <?= GridView::widget([
                'dataProvider' => $dataProvider,
                'filterModel' => $searchModel,
                'options' => ['class' => ''],
                'columns' => [
                    [
                        'attribute' => 'id',
                        'options' => ['width' => '5%'],
                    ],
                    [
                        'attribute' => 'user_id',
                        'options' => ['width' => '8%'],
                    ],
                    [
                        'attribute' => 'type',
                        'value' => function($model){
                            return $model->type == '1' ? '产品建议' : '程序错误';
                        },
                        'filter' => Html::activeDropDownList($searchModel,
                            'type', ['1' => '产品建议', '2'=> '程序错误'],
                            ['prompt'=>'所有']
                        ),
                        'options' => ['width' => '12%'],
                    ],
                    [
                        'attribute' => 'content',
                        'value'     => function($model){
                            return $model->content;
                        },
                        'options' => ['width' => '30%'],
                        'contentOptions' => ['style' => 'word-break: break-all;max-width:15%'],
                    ],
                    [
                        'attribute' => 'contact',
                        'value'     => function($model){
                            return $model->contact;
                        },
                        'options' => ['width' => '10%'],
                    ],
                    [
                        'attribute' => 'img',
                        'format' => 'raw',
                        'options' => ['width' => '5%'],
                        'value' => function ($model) {
                            return empty($model->img) ? '-' : Html::a('查看图片',Yii::$app->params['uploadsUrl'].$model->img,['target' => '_blank']);
                        }
                    ],
                    [
                        'attribute' => 'is_deal',
                        'format' => 'raw',
                        'options' => ['width' => '10%'],
                        'value' => function($model){
                            if($model->is_deal == 1){
                                return "<button class='btn btn-success btn-xs btnstatus' data-status='1' data-type='is_deal' data-id='".$model->id."'>已处理</button>";
                            }else{
                                return "<button class='btn btn-xs btnstatus' data-status='0' data-type='is_deal' data-id='".$model->id."'>未处理</button>";
                            }
                        },
                        'filter' => Html::activeDropDownList($searchModel,
                            'is_deal', ['1' => '已处理', '0'=> '未处理'],
                            ['prompt'=>'所有']
                        ),
                    ],
                    [
                        'attribute' => 'created_at',
                        'options' => ['width' => '13.5%'],
                        'value' => function($model){
                            return date('Y-m-d H:i:s', $model->created_at);
                        },
                        'filter' => Html::input('text', 'start_at1', $date1, ['class' => 'required','id' => 'start_at1','style' => 'width:80px;']) ."--".Html::input('text', 'end_at1', (!empty($date2))?$date2:date('Y-m-d',time()), ['class' => 'required','id' => 'end_at1','style' => 'width:80px;']),
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'options' => ['width' => '5%'],
                        'template' => '{delete}',
                        'header' => '操作',
                        'buttons' => [
                            'delete' => function ($url, $model, $key) {
                                return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['/function-feedback/delete','id' => $model->id], [
                                    'data-method' => 'post',
                                    'data-confirm' => '您确定要删除此项吗？',
                                ]);
                            },
                        ]
                    ],
                ],
            ]); ?>
        </div>
    </div>
</div>

<?php 
$script = <<<JS
$(function(){
    // 时间搜索框
    $('#start_at1,#end_at1,#expire_time').datepicker({
        autoclose: true,
        format : 'yyyy-mm-dd',
        language:"zh-CN", 
    });
});

//ajax修改状态
$(document).on("click",'.btnstatus',function(){
	var id = $(this).attr("data-id");
	var status = $(this).attr("data-status");
    var type = $(this).attr("data-type");
    var url = '/function-feedback/change-status';
    var box = $(this);
    
	if(status == '0') {
        var btnval = '已处理';
	} else {
        var btnval = '未处理';
	}
    
    $.ajax({
        url: url,
        type: 'post',
        dataType: 'json',
        data:{id:id,status:status,type:type},
        success : function(data) {
            if (data.status == "1") {
                if(status == 1){
                    var d = "<button class='btn btn-xs btnstatus' data-status='0' data-type='"+type+"' data-id='"+ id +"'>"+btnval+"</button>";
                }else{
                    var d = "<button class='btn btn-success btn-xs btnstatus' data-status='1' data-type='"+type+"' data-id='"+ id +"'>"+btnval+"</button>";
                }
                box.parent("td").html(d);

				if(refresh){
					window.location.reload();
				}
                //alert('操作成功！');
            }
        },
        beforeSend : function(data) {
            box.text("loading...");
        },
        error : function(data) {
            alert('操作失败！');
        }
    });
});
JS;
$this->registerJs($script);
?>
