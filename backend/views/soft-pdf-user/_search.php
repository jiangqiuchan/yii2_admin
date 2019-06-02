<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use backend\models\Channel;

$this->registerCssFile('@web/style/plugins/bootstrap-daterangepicker/daterangepicker.css',['position' => $this::POS_BEGIN]);
?>

<div class="box">
    <div class="box-body">
        <div class="soft-pdf-user-search">

            <?php $form = ActiveForm::begin([
                'action' => ['index'],
                'method' => 'get',
            ]); ?>

            <?= $form->field($model, 'id',['options'=>['style'=>'width: 100px;display: inline-block']]) ?>

            <?= $form->field($model, 'username',['options'=>['style'=>'width: 15%;display: inline-block;margin-left: 10px']]) ?>

            <?= $form->field($model, 'mobile',['options'=>['style'=>'width: 10%;display: inline-block;margin-left: 10px']]) ?>

            <?= $form->field($model, 'created_at',['options'=>['style'=>'width: 11%;display: inline-block;margin-left: 10px']])->input('text') ?>

            <?= $form->field($model, 'channel',['options'=>['style'=>'width: 5%;display: inline-block;margin-left: 10px']])->dropDownList(yii\helpers\ArrayHelper::map(Channel::find()->all(), 'channel_biaoshi', 'channel_biaoshi'),['prompt'=>'全部']) ?>

            <?= $form->field($model, 'is_vip',['options'=>['style'=>'width: 5%;display: inline-block;margin-left: 10px']])->dropDownList(['1' => '会员','2' => '非会员'],['prompt'=>'全部']) ?>

            <?= $form->field($model, 'recharges',['options'=>['style'=>'width: 100px;display: inline-block;margin-left: 10px']]) ?>


            <span class="form-group" style="margin-left: 10px">
                <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
                <?= Html::a('全部',  Url::to(['index']), ['class' => 'btn btn-default']) ?>
            </span>

            <?php ActiveForm::end(); ?>

        </div>
    </div>
</div>
