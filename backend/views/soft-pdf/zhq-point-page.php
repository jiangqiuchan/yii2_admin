<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\SoftPdfSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'PDF转换器实时打点数据';
$this->params['breadcrumbs'][] = $this->title;

$date = yii::$app->getRequest()->get('time');
?>
<div class="soft-pdf-index">

    <h3><?= Html::encode($this->title) ?></h3>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>


    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            [
                'attribute' => 'id',
                'options' => ['width' => '7%'],
            ],
            [
                'attribute' => 'user_id',
                'options' => ['width' => '7%'],
            ],
            [
                'attribute' => 'is_new',
                'value' => function ($model) {
                    return empty($model->is_new) ? 'old' : 'new';
                },
                'filter' => Html::activeDropDownList($searchModel,
                    'is_new',['1' => '新用户','0' => '老用户'],
                    ['prompt' => '所有','class' => 'form-control'])
            ],
            [
                'attribute' => 'mac',
            ],
            [
                'attribute' => 'step',
            ],
            [
                'attribute' => 'err',
            ],
            [
                'attribute' => 'channel',
            ],
            [
                'attribute' => 'version',
            ],
            [
                'attribute' => 'time',
                'options' => ['width' => '15%'],
                'value' => function($model){
                return date('Y-m-d H:i:s', $model->time);
                },
                'filter' => Html::input('text', 'time', $date, ['class' => 'required form-control','id' => 'time']),
            ],
            // ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>

<?php 
$script = <<<JS
$(function(){
    // 时间搜索框
    $('#time').datepicker({
        autoclose: true,
        format : 'yyyy-mm-dd',
        language:"zh-CN", 
    });
});
JS;
$this->registerJs($script);
?>