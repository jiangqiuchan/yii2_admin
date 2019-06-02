<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model backend\models\SoftPdfUser */

$this->title = 'Update Soft Pdf User: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Soft Pdf Users', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="soft-pdf-user-update">

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
