<?php

namespace common\models;

use Yii;

/**
 * This is the model class for table "error_log".
 *
 * @property integer $id
 * @property string $route
 * @property string $info
 * @property string $created_at
 */
class ErrorLog extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'error_log';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['created_at'], 'integer'],
            [['route'], 'string', 'max' => 256],
            [['info'], 'string', 'max' => 1500],
        ];
    }
    
    public static function logError($info)
    {  
        $route = Yii::$app->request->getHostInfo().'/'.Yii::$app->request->getPathInfo();
        $model = new self();
        $model->route = $route;
        $model->info = $info;
        $model->created_at = time();
        $model->save();
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'route' => 'Route',
            'info' => 'Info',
            'created_at' => 'Created At',
        ];
    }
}
