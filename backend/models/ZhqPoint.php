<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "soft_pdf".
 *
 * @property integer $id
 * @property integer $is_new
 * @property string $mac
 * @property integer $step
 * @property integer $err
 * @property string $channel
 * @property string $version
 * @property integer $time
 */
class ZhqPoint extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'zhq_point';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['is_new', 'step', 'err', 'time', 'user_id'], 'integer'],
            [['mac'], 'string', 'max' => 17],
            [['channel', 'version'], 'string', 'max' => 15],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => '用户ID',
            'is_new' => '用户类型',
            'mac' => 'Mac',
            'step' => '操作步骤',
            'err' => '错误码',
            'channel' => '渠道',
            'version' => '版本',
            'time' => '时间',
        ];
    }
}
