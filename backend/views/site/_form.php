<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use common\models\Admin;

/* @var $this yii\web\View */
/* @var $model common\models\Admin */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="admin-form">

    <?php $form = ActiveForm::begin(); ?>

    <?php $state = !empty($model->username)?true:false; ?>

    <?= $form->field($model, 'username')->textInput(['disabled' => $state]) ?>

    <?= $form->field($model, 'password')->passwordInput([]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? '创建' : '更新', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>


<?php


$script = <<<JS

$('#birth_date').datepicker({
    autoclose: true,
    format : 'yyyy-mm-dd',
    'language' : 'zh-CN',
});

$(document).on("blur",'#admincreate-username',function(){
    
    var name = $(this).val();
    var url = '/admins/check-name';
    var _this = $(this);
    var id = "$model->id";
    
    $.get(url, { id : id , name: name },
        function (data) {
            if(data == 1){
                window.setTimeout(function(){
                    _this.parent("div").addClass("has-error");
                    _this.parent("div").find(".help-block").html("  名称已经存在。");
                },100); 
            }
        }  
    );
    
});

$('form').on('beforeSubmit', function (e) {
 
    var name = $("#admincreate-username").val();
    var url = '/admins/check-name';
    var check = 1;
    var id = "$model->id";
    
     $.ajax({
        url: url,
        type: 'get',
        dataType: 'json',
        async: false,
        data:{ id : id , name: name },
        success : function(data) {
            if(data == 1){
                window.setTimeout(function(){
                    $("#admincreate-username").parent("div").addClass("has-error");
                    $("#admincreate-username").parent("div").find(".help-block").html("  名称已经存在。");
                },100);
                check = 0;
            }
        },
    });
    
    if(check == 0){
        return false;
    }
    
    $(':submit').attr('disabled', true).addClass('disabled');
});

JS;
$this->registerJs($script);
?>
