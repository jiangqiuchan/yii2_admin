<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use backend\models\SoftPdfOrder;
use backend\models\Channel;

/* @var $this yii\web\View */
/* @var $model common\models\SoftPdfOrderSearch */
/* @var $form yii\widgets\ActiveForm */

$this->registerCssFile('@web/style/plugins/bootstrap-daterangepicker/daterangepicker.css',['position' => $this::POS_BEGIN]);

?>

<div class="box">
    <div class="box-body">
        <div class="soft-pdf-order-search">

            <?php $form = ActiveForm::begin([
                'action' => ['index'],
                'method' => 'get',
            ]); ?>

            <?= $form->field($model, 'user_id',['options'=>['style'=>'width: 100px;display: inline-block']]) ?>

            <?= $form->field($model, 'trade_no',['options'=>['style'=>'width: 15%;display: inline-block;margin-left: 10px']]) ?>

            <?= $form->field($model, 'username',['options'=>['style'=>'width: 10%;display: inline-block;margin-left: 10px']]) ?>

            <?= $form->field($model, 'channel',['options'=>['style'=>'width: 5%;display: inline-block;margin-left: 10px']])->dropDownList(yii\helpers\ArrayHelper::map(Channel::find()->all(), 'channel_biaoshi', 'channel_biaoshi'),['prompt'=>'全部']) ?>

            <?= $form->field($model, 'referer',['options'=>['style'=>'width: 5%;display: inline-block;margin-left: 10px']])->dropDownList(['pdf' => 'pdf','ocr' => 'ocr'],['prompt'=>'全部']) ?>

            <?= $form->field($model, 'created_at',['options'=>['style'=>'width: 11%;display: inline-block;margin-left: 10px']])->input('text') ?>

            <?= $form->field($model, 'recharges',['options'=>['style'=>'width: 100px;display: inline-block;margin-left: 10px']]) ?>

            <?php //echo $form->field($model, 'gmt_payment',['options'=>['style'=>'width: 5%px;display: inline-block']]) ?>

            <?= $form->field($model, 'receipt_amount',['options'=>['style'=>'width: 100px;display: inline-block;margin-left: 10px']]) ?>

            <?= $form->field($model, 'package',['options'=>['style'=>'width: 100px;display: inline-block;margin-left: 10px']])->dropDownList(SoftPdfOrder::getPackageArr(1),['prompt'=>'全部']) ?>

            <?= $form->field($model, 'pay_type',['options'=>['style'=>'width: 5%;display: inline-block;margin-left: 10px']])->dropDownList(['alipay' => 'alipay','weixin' => 'weixin'],['prompt'=>'全部']) ?>

            <span class="form-group" style="margin-left: 10px">
                <?= Html::submitButton('搜索', ['class' => 'btn btn-primary']) ?>
                <?= Html::a('全部',  Url::to(['index']), ['class' => 'btn btn-default']) ?>
            </span>

            <?php ActiveForm::end(); ?>

        </div>
    </div>
</div>
