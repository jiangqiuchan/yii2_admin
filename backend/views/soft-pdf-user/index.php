<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\ActiveForm;
use backend\components\commonality;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\SoftPdfUserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$module_type = yii::$app->getRequest()->get('module_type');
if (isset($module_type)) {
    switch ($module_type)
    {
        case 1:
            $this->title = '总注册 ';
            break;
        case 2:
            $this->title = '昨日DAU';
            break;
        case 3:
            $this->title = '一周在线';
            break;
        case 4:
            $this->title = '一月在线';
            break;
        case 5:
            $this->title = '今日注册并充值';
            break;
        default:
            $this->title = 'pdf用户';
    }
} else {
    $this->title = '用户列表';
}

$this->params['breadcrumbs'][] = $this->title;

$date1 = yii::$app->getRequest()->get('start_at1');
$date2 = yii::$app->getRequest()->get('end_at1');
$date3 = yii::$app->getRequest()->get('expire_time');

?>

<?php
use yii\bootstrap\Modal;
//创建修改modal
Modal::begin([
    'id' => 'update-modal',
    'header' => '<h4 class="modal-title">更新</h4>',
]);
Modal::end();
?>
<div class="soft-pdf-user-index">

    <h3><?= Html::encode($this->title) ?></h3>

    <?php  echo $this->render('_search', ['model' => $searchModel]); ?>

    <div class="box">
        <div class="box-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
    //        'filterModel' => $searchModel,
            "layout" => "{items}\n{pager}\n{summary}",
            'columns' => [
                [
                    'class' => 'yii\grid\SerialColumn',
                    'options' => ['width' => '20px'],
                ],
                [
                    'attribute' => 'id',
                    'options' => ['width' => '50px'],
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
                    'attribute' => 'username',
                    'value' => function($model){
                        return commonality::userTextDecode($model->username);
                    },
                    'options' => ['width' => '100px'],
                ],
                [
                    'attribute' => 'mobile',
                    'options' => ['width' => '100px'],
                ],
                [
                    'attribute' => 'channel',
                    'value' => function($model){
                        return $model->channel ? $model->channel : '';
                    },
                    'options' => ['width' => '100px'],
                ],
                [
                    'attribute' => 'recharges',
                    'value' => function($model){
                        return $model->recharges ? $model->recharges : '0';
                    },
                    'options' => ['width' => '50px'],
                ],
                [
                    'attribute' => 'expire_time',
                    'options' => ['width' => '13.5%'],
                    'value' => function($model){
                        return empty($model->expire_time) ? '' : ($model->expire_time == 100 ? '永久' : date('Y-m-d H:i:s', $model->expire_time));
                    },
                    'filter' => Html::input('text', 'expire_time', $date3, ['class' => 'required','id' => 'expire_time','style' => 'width:80px;']),
                ],

    //             [
    //                 'class' => 'yii\grid\ActionColumn',
    //                 'template' => '{update} &nbsp;{delete}',
    //                 'options' => ['width' => '80px'],
    //                 'header' => '操作',
    //                 'buttons' => [
    //                     'update' => function ($url, $model, $key) {
    //                         return Html::a('<span class="glyphicon glyphicon-pencil"></span>', 'javascript:void(0)', [
    //                             'data-toggle' => 'modal',
    //                             'data-target' => '#update-modal',
    //                             'class' => 'data-update',
    //                             'data-id' => $key,
    //                         ]);
    //                     },
    //                     'delete' => function ($url, $model, $key) {
    //                         return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['/soft-pdf-user/delete','id' => $model->id], [
    //                                 'data-method' => 'post',
    //                                 'data-confirm' => '您确定要删除此项吗？',
    //                         ]);
    //                     },
    //                 ]
    //             ],
            ],
        ]); ?>
    </div>
</div>

<?php 
$script = <<<JS
$(function(){
    // 时间搜索框
    // $('#start_at1,#end_at1,#expire_time,#softpdfusersearch-created_at').datepicker({
    //     autoclose: true,
    //     format : 'yyyy-mm-dd',
    //     language:"zh-CN", 
    // });
    $('input[name="SoftPdfUserSearch[created_at]"]').daterangepicker({
         format: 'YYYY-MM-DD',
         autoUpdateInput:false
     });
    //清空modal
    $('.modal').on('hidden.bs.modal', function () {
        $(".modal-body").empty();
    })  
    //创建修改modal
    $('.data-update').on('click', function () {
    var id = $(this).attr("data-id");
    if (id == '') {
        $('.modal-title').html("添加");
        var url = '/soft-pdf-user/create';
    } else {
        $('.modal-title').html("编辑分类");
        var url = '/soft-pdf-user/update';
    }
        $.get(url, { id: id },
            function (data) {
                $('.modal-body').html(data);
            }  
        );
    });
});
JS;
$this->registerJs($script);
?>
