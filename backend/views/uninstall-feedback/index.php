<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\UninstallFeedbackSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '卸载反馈列表';
$this->params['breadcrumbs'][] = $this->title;

$date1 = yii::$app->getRequest()->get('start_at1');
$date2 = yii::$app->getRequest()->get('end_at1');
?>
<div class="uninstall-feedback-index">

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
                    'attribute' => 'version',
                    'options' => ['width' => '5%'],
                ],
                [
                    'attribute' => 'reason',
                    'format'    => 'raw',
                    'value'     => function($model){
                        return $model->reason;
                    },
                    'options' => ['width' => '35%'],
                    'contentOptions' => ['style' => 'word-break: break-all;max-width:15%'],
                ],
                [
                    'attribute' => 'content',
                    'value'     => function($model){
                        return $model->content;
                    },
                    'options' => ['width' => '35%'],
                    'contentOptions' => ['style' => 'word-break: break-all;max-width:15%'],
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
                    'options' => ['width' => '50px'],
                    'template' => '{delete}',
                    'header' => '操作',
                    'buttons' => [
                        'delete' => function ($url, $model, $key) {
                            return Html::a('<span class="glyphicon glyphicon-trash"></span>', ['/uninstall-feedback/delete','id' => $model->id], [
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
JS;
$this->registerJs($script);
?>