<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "uninstall_feedback".
 *
 * @property integer $id
 * @property string $reason
 * @property string $content
 * @property string $version
 * @property string $created_at
 */
class UninstallFeedback extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'uninstall_feedback';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at'], 'integer'],
            [['reason', 'content'], 'string', 'max' => 1000],
            [['version'], 'string', 'max' => 15],
        ];
    }
    
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if($insert) {
                $this->created_at = time();
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'reason' => '原因',
            'content' => '附加内容',
            'version' => '版本号',
            'created_at' => '时间',
        ];
    }
}
