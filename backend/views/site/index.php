<?php

/* @var $this yii\web\View */

$this->title = '首页数据';
?>
<div class="site-index">
    <style>
        .small-box h3 {
            color: white;
        }
        sup {
            top: -0.3em;
        }
        .small-box>.inner {
            padding: 15px;
        }

        .col-lg-3 {
            width: 27%;
        }
    </style>
    <label>用户注册</label>
    <div class="row">
        <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3><?=$data['total_register'] ?><sup style="font-size: 20px"> / </sup><?=$data['today_register'] ?></h3>

                    <p>总注册 / 今日注册</p>
                </div>
                <div class="icon">
                    <!--                 <i class="ion-person-add"></i> -->
                </div>
                <a href="/soft-pdf-user/index?module_type=1" class="small-box-footer" target="_blank">More info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3><?=$data['total_register'] ?><sup style="font-size: 20px"> / </sup><?=$data['total_user_order'] ?></h3>

                    <p>总注册人数 / 总充值人数</p>
                </div>
                <div class="icon">
                    <!--                 <i class="ion-person-add"></i> -->
                </div>
                <a href="/soft-pdf-user/index?module_type=1" class="small-box-footer" target="_blank">More info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-aqua">
                <div class="inner">
                    <h3><?=$data['today_register_order'] ?></h3>

                    <p>今日注册并充值</p>
                </div>
                <div class="icon">
                    <!--                 <i class="ion-person-add"></i> -->
                </div>
                <a href="/soft-pdf-user/index?module_type=5&SoftPdfUserSearch[created_at]=<?=$today ?>" class="small-box-footer" target="_blank">More info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>

    <label>订单</label>
    <div class="row">
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-green">
                <div class="inner">
                    <h3><?=$data['total_order'] ?><sup style="font-size: 20px"> - </sup><?=$data['total_order_amount'] ?><sup style="font-size: 20px"> - </sup><?=$data['total_order_amount_avg'] ?></h3>

                    <p>(总)订单数 / 订单总额 / 平均订单金额</p>
                </div>
                <div class="icon">
                    <!--                 <i class="ion ion-stats-bars"></i> -->
                </div>
                <a href="/soft-pdf-order/index?module_type=5" class="small-box-footer" target="_blank">More info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
        <!-- ./col -->
        <div class="col-lg-3 col-xs-6">
            <!-- small box -->
            <div class="small-box bg-green">
                <div class="inner">
                    <h3><?=$data['today_order'] ?><sup style="font-size: 20px"> - </sup><?=$data['today_order_amount'] ?><sup style="font-size: 20px"> - </sup><?=$data['today_order_amount_avg'] ?></h3>

                    <p>(今日)订单数/订单总额/平均订单金额</p>
                </div>
                <div class="icon">
                    <!--                 <i class="ion ion-stats-bars"></i> -->
                </div>
                <a href="/soft-pdf-order/index?SoftPdfOrderSearch[created_at]=<?=$today ?>" class="small-box-footer" target="_blank">More info <i class="fa fa-arrow-circle-right"></i></a>
            </div>
        </div>
    </div>
</div>
