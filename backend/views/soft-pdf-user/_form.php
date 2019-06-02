<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\SoftPdfUser */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="soft-pdf-user-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'mobile')->textInput(['disabled' => true]) ?>

    <?= $form->field($model, 'new_password')->textInput(['placeholder' => '不低于6位数，不能含特殊字符']) ?>

        到期时间：<?=$model->expire_time ?>
    <br><br>
    <div class="form-group">
        <?= Html::submitButton('确定', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
