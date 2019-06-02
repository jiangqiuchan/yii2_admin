<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\Channel */

$this->title = '新增渠道';
$this->params['breadcrumbs'][] = ['label' => '渠道列表', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="channel-create">

    <h3><?= Html::encode($this->title) ?></h3>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
