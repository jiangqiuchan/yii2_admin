<?php
namespace common\models;

use Yii;
use yii\base\Model;

/**
 * Login form
 */
class LoginForm extends Model
{
    public $mobile;
    public $password;
    public $rememberMe;
    public $is_online;

    private $_user;


    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            // username and password are both required
            [['mobile','password'], 'filter', 'filter' => 'trim'],
            [['mobile'], 'required','message' => '请输入正确的手机号码格式'],
            [['password'], 'required','message' => '请输入正确的密码格式'],
            // rememberMe must be a boolean value
            ['rememberMe', 'boolean'],
            // password is validated by validatePassword()
            ['password', 'validatePassword'],
            ['is_online', 'integer'],
        ];
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();
            if (!$user || !$user->password_hash || !$user->validatePassword($this->password)) {
                $this->addError($attribute, '手机号码或密码不正确');
            }
        }
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        if ($this->validate()) {
            $user = $this->getUser();
            $login = Yii::$app->user->login($user, $this->rememberMe ? 3600 * 24 * 30 : 0);
            if ($login) {
                $user->last_login_at = time();
                $user->is_online = 1;
                $user->save();
                return $login;
            }

        } else {
            return false;
        }
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    protected function getUser()
    {
        if ($this->_user === null) {
            $this->_user = User::findByUsername($this->mobile);
        }

        return $this->_user;
    }
}
