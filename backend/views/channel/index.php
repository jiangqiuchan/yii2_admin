<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\ChannelSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = '渠道列表';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="channel-index">

    <h3><?= Html::encode($this->title) ?></h3>

    <p>
        <?= Html::a('新增', ['create'], ['class' => 'btn btn-success']) ?>
    </p>
    <div class="box">
        <div class="box-body">
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
//            'filterModel' => $searchModel,
            'columns' => [
                ['class' => 'yii\grid\SerialColumn'],

                'id',
                'channel_name',
                'channel_url:ntext',
                'soft_versions',
                'channel_biaoshi',
                //'add_time',

                [
                    'class' => 'yii\grid\ActionColumn',
                    'template' => '{update}&nbsp;{delete}',
                    'header' => '操作',
                ],
            ],
        ]); ?>
        </div>
    </div>
</div>
