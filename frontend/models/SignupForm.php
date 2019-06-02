<?php
namespace frontend\models;

use common\models\User;
use yii\base\Model;
use Yii;

/**
 * Signup form
 */
class SignupForm extends Model
{
    public $mobile;
    public $last_login_at;
    public $password;
    public $username;
    public $channel;
    public $referer;
    public $type;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['mobile','password'], 'filter', 'filter' => 'trim'],
            ['mobile', 'required'],
            ['mobile', 'unique', 'targetClass' => '\common\models\User', 'message' => '手机号已存在'],
            [['mobile','username'], 'string', 'min' => 2, 'max' => 255],

//             ['email', 'filter', 'filter' => 'trim'],
//             ['email', 'required'],
//             ['email', 'email'],
//             ['email', 'string', 'max' => 255],
//             ['email', 'unique', 'targetClass' => '\common\models\User', 'message' => 'This email address has already been taken.'],

            ['password','required','when' => function($model){ return ($model->type == 'password'); }],
            ['password', 'string', 'min' => 6],
            [['last_login_at','channel','referer','type'],'safe']
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if (!$this->validate()) {
            return null;
        }

        $user = new User();
        $user->mobile = $this->mobile;
        $user->username = $this->username;
        $user->last_login_at = time();
        $user->channel = $this->channel;
        $user->referer = $this->referer;
        $this->password ? $user->setPassword($this->password) : '';
        $user->generateAuthKey();
        
        return $user->save() ? $user : null;
    }
}
