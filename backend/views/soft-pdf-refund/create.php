<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model backend\models\SoftPdfRefund */

$this->title = 'Create Soft Pdf Refund';
$this->params['breadcrumbs'][] = ['label' => 'Soft Pdf Refunds', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="soft-pdf-refund-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
