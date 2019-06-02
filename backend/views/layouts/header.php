<?php
use yii\helpers\Html;

/* @var $this \yii\web\View */
/* @var $content string */
?>
<style>
    .dropdown-menu > li > a {
        padding: 6px 20px;
    }
    .nav > li > a {
        padding: 15px 43px;
    }
</style>

<header class="main-header">

    <?= Html::a('<span class="logo-mini">APP</span><span class="logo-lg">' . Yii::$app->name . '</span>', Yii::$app->homeUrl, ['class' => 'logo']) ?>

    <nav class="navbar navbar-static-top" role="navigation">

        <a href="#" class="sidebar-toggle" data-toggle="push-menu" role="button">
            <span class="sr-only">Toggle navigation</span>
        </a>

        <div class="navbar-custom-menu">

            <ul class="nav navbar-nav">

                <li class="dropdown user user-menu">
                    <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                        <span class="hidden-xs"><?=yii::$app->user->identity->username; ?></span>
                        <img src="<?= $directoryAsset ?>/img/user2-160x160.jpg" class="user-image" alt="User Image"/>
                    </a>
                    <ul class="dropdown-menu">
                        <li><a href="/site/reset-password?id=<?=Yii::$app->user->identity->id ?>"><i class="glyphicon glyphicon-user"></i> 修改密码</a></li>
                        <li><a href="/site/logout"><i class="glyphicon glyphicon-off"></i> 注销</a></li>
                    </ul>
                </li>

            </ul>
        </div>
    </nav>
</header>
