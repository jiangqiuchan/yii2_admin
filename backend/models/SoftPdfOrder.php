<?php

namespace backend\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use common\models\PdfManConvertData;

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
    public $mobile;
    public $username;
    public $recharges;
    public $channel;
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
            [['user_id', 'package', 'pay_status', 'expire_time', 'created_at', 'notify_at', 'updated_at','gmt_payment'], 'integer'],
            [['money', 'receipt_amount'], 'number'],
            [['pay_type','referer'], 'string', 'max' => 10],
            [['out_trade_no'], 'string', 'max' => 70],
            [['trade_no'], 'string', 'max' => 70],
            [['username'], 'string', 'max' => 255],
            [['channel'], 'string', 'max' => 50],
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

    public function pdfLoginLog(){
        return $this->hasOne(PdfLoginLog::className(),'user_id','user_id');
    }
    
    public static function getOrderData($time = '',$type = '')
    {
        $order = SoftPdfOrder::find()->select("id,receipt_amount")->where("pay_status = 1");
        
        if ($time) {
            $timeon = strtotime(date('Y-m-d',$time));
            $timeend = $timeon + 86399;
            $where = "created_at BETWEEN $timeon AND $timeend";
        } else {
            $where = '';
        }
        
        $order = $order->andWhere($where);

        if ($type == 'user') {
            $return['user_order_num'] = $order->groupBy('user_id')->count();
        } else {
            $return['order_num'] = $order->count();
            $return['order_amount'] = empty($order->sum('receipt_amount')) ? '0' : $order->sum('receipt_amount');
        }
        
        return $return;
    }
    
    public function getPdfUser()
    {
        return $this->hasOne(SoftPdfUser::className(), ['id' => 'user_id']);
    }
    
    public function getPdfManConvertData()
    {
        return $this->hasMany(PdfManConvertData::className(), ['order_id' => 'id']);
    }
    
    public static function getRecharges($uid)
    {
        $recharges = self::find()->where("user_id = $uid AND pay_status = '1'")->count();
        return $recharges;
    }
    
    public static function getPackageArr($type) {
        switch ($type)
        {
            case 1:
                $arr = ['1' => '7天', '2' => '1个月', '3' => '1年', '4' => '2年', '5' => '3年', '6' => '永久', '7' => '半年', '10' => '免费体验','11'=>'人工转换'];
                break;
            case 2:
                $arr = ['1' => '7天', '2' => '1个月', '3' => '1年', '4' => '2年', '5' => '3年', '6' => '永久', '7' => '半年','11'=>'人工转换'];
                break;
            default:
                $arr = [];
        }
    
        return $arr;
    }

    //首页数据统计
    public static function getUserOrderLogData()
    {
        $num = SoftPdfOrder::find()
            ->select('user_id')
            ->where('pay_status','1')
            ->joinWith('pdfLoginLog')
            ->groupBy("user_id")
            ->all()
            ->count();

        return $num;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => '用户ID',
            'pay_type' => '支付方式',
            'money' => '支付金额',
            'receipt_amount' => '金额',
            'package' => '详细信息',
            'out_trade_no' => 'Out Trade No',
            'trade_no' => '交易订单号',
            'pay_status' => 'Pay Status',
            'expire_time' => 'Expire Time',
            'created_at' => '创建时间',
            'notify_at' => 'Notify At',
            'updated_at' => 'Updated At',
            'gmt_payment' => '支付时间',
            'mobile' => '用户名',
            'username' => '用户名',
            'recharges' => '充值次数',
            'channel' => '渠道',
            'referer' => '软件来源'
        ];
    }
}
