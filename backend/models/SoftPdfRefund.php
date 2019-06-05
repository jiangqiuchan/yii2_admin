<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "soft_pdf_refund".
 *
 * @property integer $id
 * @property integer $order_id
 * @property string $money
 * @property string $batch_no
 * @property integer $batch_num
 * @property string $reason
 * @property integer $state
 * @property string $created_at
 */
class SoftPdfRefund extends \yii\db\ActiveRecord
{
    public $username;
    public $trade_no;
    public $package;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'soft_pdf_refund';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['order_id', 'batch_num', 'state', 'created_at', 'type', 'admin_id', 'user_id', 'package'], 'integer'],
            [['money'], 'number'],
            [['batch_no'], 'string', 'max' => 70],
            [['reason'], 'string', 'max' => 255],
        ];
    }
    
    public function getSoftPdfUser()
    {
        return $this->hasOne(SoftPdfUser::className(), ['id' => 'user_id']);
    }
    
    public function getSoftPdfOrder()
    {
        return $this->hasOne(SoftPdfOrder::className(), ['id' => 'order_id']);
    }
    
    public function getAdmin()
    {
        return $this->hasOne(User::className(), ['id' => 'admin_id']);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'order_id' => 'Order ID',
            'user_id' => '用户ID',
            'money' => '金额',
            'batch_no' => 'Batch No',
            'batch_num' => 'Batch Num',
            'reason' => '原因',
            'state' => 'State',
            'created_at' => '退款时间',
            'username' => '用户名',
            'admin_id' => '管理员',
            'trade_no' => '交易订单号',
            'package' => '套餐'
        ];
    }
}
