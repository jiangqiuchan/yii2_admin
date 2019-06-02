<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace backend\models;
use common\models\Admin;
use Yii;
/**
 * Description of CommentCreate
 *
 * @author Administrator
 */
class AdminCreate extends Admin{
    public $password;
//     public $access;
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            [['username'], 'string', 'max' => 255],
            ['password', 'string', 'length' => [6, 18]],
            [['username'], 'unique'],
        ];
    }
    public function saveData(){
        if(!$this->validate()){
            return false;
        }
        
        $this->setPassword($this->password);
        $this->status = 10;
        $this->auth_key = '';
        // $this->email = '';
        
        if ($this->save()) {
            $sql = "SELECT * FROM {{%auth_assignment}} where user_id = $this->id";
            if (empty(Yii::$app->db->createCommand($sql)->execute())) {
                $time = time();
                $sql = "INSERT INTO  {{%auth_assignment}} (item_name,user_id,created_at) VALUES ('运营人员',$this->id,$time)";
                Yii::$app->db->createCommand($sql)->execute();
            }
        }
        
        return $this;
    }
    
    /**
     * Generates password hash from password and sets it to the model
     *
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password_hash = Yii::$app->security->generatePasswordHash($password);
    }
    
    
    public function attributeLabels() {
        return array_merge(parent::attributeLabels(),[
            'password'=>'密码',
//             'access' => '分配权限'
        ]);
    }
}
