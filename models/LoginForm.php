<?php

namespace app\models;

use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 *
 * @property User|null $user This property is read-only.
 *
 */
class LoginForm extends Model
{

    public $username;
    public $password;
    public $rememberMe = true;

    private $_user = false;


    public function rules()
    {
        return [
            [['username', 'password'], 'required'],
            [['username'], 'string', 'max' => 20],
            ['rememberMe', 'boolean'],
            ['password', 'validatePassword'],
        ];
    }


    public function attributeLabels()
    {
        return [
            'username' => 'Login',
            'password' => 'Password',
            'rememberMe' => 'Remember me',
        ];
    }


    public function validatePassword($attribute, $params)
    {
        if (!$this->hasErrors()) {
            $user = $this->getUser();

            if (!$user || !$user->validatePassword($this->password)) {
                $this->addError($attribute, 'Incorrect username or password.');
            }
        }
    }


    public function login()
    {
        if ($this->validate()) {
            if (!$this->isActive()){
                Yii::$app->session->setFlash('error', "Аккаунт необходимо активировать перейдя по ссылке из email");
                return false;
            }
            return Yii::$app->user->login($this->getUser(), $this->rememberMe ? 3600*24*30 : 0);
        }
        return false;
    }


    public function getUser()
    {
        if ($this->_user === false) {
            $this->_user = User::findByUsername($this->username);
        }

        return $this->_user;
    }


    public function isActive(){
        return $this->getUser()->active == 1 ? true : false;
    }

}
