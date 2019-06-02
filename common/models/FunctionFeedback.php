<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "function_feedback".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $type
 * @property string $content
 * @property string $contact
 * @property string $img
 * @property integer $is_deal
 * @property string $created_at
 * @property string $updated_at
 */
class FunctionFeedback extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'function_feedback';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'type', 'is_deal', 'created_at', 'updated_at'], 'integer'],
            [['content'], 'string', 'max' => 1000],
            [['contact'], 'string', 'max' => 50],
            [['img'], 'string', 'max' => 255],
        ];
    }
    
    public function beforeSave($insert)
    {
        if (parent::beforeSave($insert)) {
            if($insert) {
                $this->created_at = time();
                $this->updated_at = time();
            }else{
                $this->updated_at = time();
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
            'user_id' => '用户ID',
            'type' => '类型',
            'content' => '内容',
            'contact' => '联系方式',
            'img' => '图片',
            'is_deal' => '是否已处理',
            'created_at' => '时间',
            'updated_at' => 'Updated At',
        ];
    }
}
