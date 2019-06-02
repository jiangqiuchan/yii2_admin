<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "channel".
 *
 * @property int $id
 * @property string $channel_name 渠道名称
 * @property string $channel_url 软件连接
 * @property string $soft_versions 软件版本
 * @property string $channel_biaoshi 渠道标识
 * @property string $add_time
 */
class Channel extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'channel';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['channel_url'], 'string'],
            [['add_time'], 'safe'],
            [['channel_name'], 'string', 'max' => 100],
            [['soft_versions', 'channel_biaoshi'], 'string', 'max' => 20],
            [['channel_biaoshi','channel_name'], 'required'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'channel_name' => '渠道名称',
            'channel_url' => '渠道包链接',
            'soft_versions' => '软件版本',
            'channel_biaoshi' => '渠道标识',
            'add_time' => '创建时间',
        ];
    }
}
