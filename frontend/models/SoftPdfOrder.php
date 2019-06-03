<?php

namespace frontend\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "soft_pdf_order".
 *
 * @property integer $id
 * @property integer $user_id
 * @property string $pay_type
 * @property string $money
 * @property string $receipt_amount
 * @property integer $package
 * @property string $out_trade_no
 * @property string $trade_no
 * @property integer $pay_status
 * @property string $expire_time
 * @property string $created_at
 * @property string $notify_at
 * @property string $updated_at
 */
class SoftPdfOrder extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'soft_pdf_order';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'package', 'pay_status', 'expire_time', 'created_at', 'notify_at', 'updated_at','gmt_payment','pay_type_method'], 'integer'],
            [['money', 'receipt_amount'], 'number'],
            [['pay_type'], 'string', 'max' => 10],
            [['out_trade_no'], 'string', 'max' => 70],
            [['trade_no'], 'string', 'max' => 70],
        ];
    }
    
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::className(),
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                //'value' => new Expression('NOW()'),
                //'value'=>$this->timeTemp(),
            ],
        ];
    }
    

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'pay_type' => 'Pay Type',
            'money' => 'Money',
            'receipt_amount' => 'Receipt Amount',
            'package' => 'Package',
            'out_trade_no' => 'Out Trade No',
            'trade_no' => 'Trade No',
            'pay_status' => 'Pay Status',
            'expire_time' => 'Expire Time',
            'created_at' => 'Created At',
            'notify_at' => 'Notify At',
            'updated_at' => 'Updated At',
        ];
    }
}
