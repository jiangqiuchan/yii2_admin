<?php

namespace backend\models;

use Yii;
use common\models\OauthThirdLogin;

/**
 * This is the model class for table "soft_pdf_user".
 *
 * @property integer $id
 * @property string $mobile
 * @property string $auth_key
 * @property string $password_hash
 * @property string $password_reset_token
 * @property string $created_at
 * @property string $last_login_at
 * @property string $updated_at
 */
class SoftPdfUser extends \yii\db\ActiveRecord
{
    public $expire_time;
    public $new_password;
    public $recharges;
    public $is_vip;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'soft_pdf_user';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['auth_key', 'password_hash','new_password'], 'required'],
            [['created_at', 'last_login_at', 'updated_at', 'expire_time'], 'integer'],
            [['mobile','channel'], 'string', 'max' => 50],
            [['auth_key'], 'string', 'max' => 32],
            [['password_hash', 'password_reset_token'], 'string', 'max' => 255],
            [['new_password'], 'string', 'min' => 6],
            ['new_password','match', 'pattern' => '/^[0-9a-zA-Z]{6,}$/', 'message' => '请输入正确的密码格式'],
            [['username'], 'string', 'max' => 255],
        ];
    }
    
    public function getPdfOrder()
    {
        return $this->hasMany(SoftPdfOrder::className(), ['user_id' => 'id']);
    }
    
    public function getLoginLog()
    {
        return $this->hasMany(PdfLoginLog::className(), ['user_id' => 'id']);
    }
    
    public function getOauth()
    {
        return $this->hasMany(OauthThirdLogin::className(), ['user_id' => 'id']);
    }
    
    public static function isExsitMob($mobile)
    {
        $mobile = self::find()->where("mobile = $mobile")->one();
        if ($mobile) {
            return 1;
        }
        return 0;
    }
    
    public static function isOnline($mobile)
    {
        $mobile = self::find()->where("mobile = $mobile")->one();
        return $mobile->is_online;
    }

    //首页数据统计1
    public static function getUserRegData($time = '')
    {
        $reg = self::find()->select("created_at");
        $where = '';
        
        if ($time) {
            $timeon = strtotime(date('Y-m-d',time()));
            $timeend = $timeon + 86399;
            $where = "created_at BETWEEN $timeon AND $timeend";
        }
    
        $num = $reg->where($where)->count();
        return $num;
    }

    //首页数据统计2
    public static function getUserOrderData($time = '')
    {
        $where = '';

        if ($time) {
            $timeon = strtotime(date('Y-m-d',time()));
            $timeend = $timeon + 86399;
            $where = "created_at BETWEEN $timeon AND $timeend";
        }

        $sql = "select * from
                (select id,username from soft_pdf_user where ".$where.") u
                left join
                (select id,user_id from soft_pdf_order where pay_status = 1) o
                on u.id = o.user_id
                where o.id <> ''
                group by u.id";

        $rows = Yii::$app->db->createCommand($sql)->queryAll();

        return count($rows);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => '用户ID',
            'mobile' => '手机号',
            'auth_key' => 'Auth Key',
            'password_hash' => 'Password Hash',
            'password_reset_token' => 'Password Reset Token',
            'created_at' => '注册时间',
            'last_login_at' => 'Last Login At',
            'updated_at' => 'Updated At',
            'expire_time' => '到期时间',
            'new_password' => '新密码',
            'username' => '用户名',
            'recharges' => '充值次数',
            'channel' => '渠道',
            'is_vip' => '会员类型',
        ];
    }
}
