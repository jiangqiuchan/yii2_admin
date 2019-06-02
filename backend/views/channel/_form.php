<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\Channel */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="box">
    <div class="box-body">
        <div class="channel-form">

            <?php $form = ActiveForm::begin(); ?>

            <?= $form->field($model, 'channel_name')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'channel_url')->textInput(['rows' => 6]) ?>

            <?= $form->field($model, 'channel_biaoshi')->textInput(['maxlength' => true]) ?>

            <div class="form-group">
                <?= Html::submitButton('保存', ['class' => 'btn btn-success']) ?>
            </div>

            <?php ActiveForm::end(); ?>

        </div>
    </div>
</div>
