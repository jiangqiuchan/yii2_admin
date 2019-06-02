<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Channel */

$this->title = '编辑渠道: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => '渠道列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = '编辑';
?>
<div class="channel-update">

    <h3><?= Html::encode($this->title) ?></h3>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
