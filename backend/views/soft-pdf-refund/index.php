<?php

use yii\helpers\Html;
use yii\grid\GridView;
use backend\models\User;
use backend\models\SoftPdfOrder;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\SoftPdfRefundSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '退款列表';
$this->params['breadcrumbs'][] = $this->title;
?>
<style>
.filters select,input{
	height:34px
}
.table td,th{
	text-align: center;
	font-size: 16px;
}
</style>
<div class="soft-pdf-refund-index">

    <h3><?= Html::encode($this->title) ?></h3>

    <?php  echo $this->render('_search', ['model' => $searchModel]); ?>
    
    <div class="card-box">
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
//        'filterModel' => $searchModel,
        'columns' => [
            [
                'attribute' => 'user_id',
                'options' => ['width' => '5%'],
            ],
            [
                'attribute' => 'username',
                'options' => ['width' => '12%'],
            ],
            [
                'attribute' => 'trade_no',
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
                'attribute' => 'money',
            ],
            [
                'attribute' => 'reason',
            ],
            [
                'attribute' => 'admin_id',
                'value' => function ($model) {
                    return $model->admin ? $model->admin->username : '';
                },
                'filter' => Html::activeDropDownList($searchModel,
                    'admin_id',yii\helpers\ArrayHelper::map(User::find()->all(), 'id', 'username'),
                    ['prompt' => '所有'])
            ],
            [
                'attribute' => 'created_at',
                'options' => ['width' => '15%'],
                'value' => function($model){
                    return date('Y-m-d H:i:s', $model->created_at);
                },                
            ],

//             ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
    </div>
</div>

<?php
$script = <<<JS
$(function(){
    $('input[name="SoftPdfRefundSearch[created_at]"]').daterangepicker({
         format: 'YYYY-MM-DD',
         autoUpdateInput:false
     });
})
JS;
$this->registerJs($script);
?>
