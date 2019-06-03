<?php

namespace common\models;

use Yii;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "{{%third_login}}".
 *
 * @property integer $id
 * @property string $type
 * @property string $openid
 * @property string $user_id
 * @property string $created_at
 */
class OauthThirdLogin extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%oauth_third_login}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['id', 'user_id'], 'integer'],
            [['type', 'openid', 'unionid'], 'string', 'max' => 255],
        ];
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                //'value' => new Expression('NOW()'),
                //'value'=>$this->timeTemp(),
            ],
        ];
    }
    
    //记录回调信息
    public static function logOauthNotify()
    {
        $errorM = new ErrorLog();
        $url = Yii::$app->request->hostInfo.Yii::$app->request->getUrl();
        ErrorLog::logError($url);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'type' => 'Type',
            'openid' => 'Openid',
            'unionid' => 'Unionid',
            'user_id' => '用户id',
            'created_at' => '创建时间',
        ];
    }
}
