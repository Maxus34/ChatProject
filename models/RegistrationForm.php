<?php

namespace app\models;

use yii\base\Model;
use Yii;

class RegistrationForm extends Model
{
    public $username;
    public $email;
    public $password;
    public $repeatPassword;

    public function rules()
    {
        return [
            [['username', 'password', 'repeatPassword', 'email'], 'required'],
            [['username', 'email'], 'trim'],
            [['email'], 'email'],
            [['username'], 'validateUsername'],
            [['password'], 'validatePassword'],
        ];
    }

    public function validatePassword($attribute, $params){
        if (!$this->hasErrors()) {
            if ($this->password !== $this->repeatPassword) {
                $this->addError($attribute, 'Пароли не совпадают!');
            }
        }
    }

    public function validateUsername($attribute, $params){
        if (!$this->hasErrors()) {

            if (!empty(User::findByUsername($this->username))) {
                $this->addError($attribute, 'Имя пользователя занято!');
            }
        }
    }

    public function register () {
        if (!$this->validate()){
            return false;

        } else {
            $user = $this->createNewUser();
            $this->assignUserRole('user', $user);
            return true;
        }
    }

    protected function createNewUser(){
        $user                 = new User();
        $user->username       = $this->username;
        $user->email          = $this->email;
        $user->password       = Yii::$app->getSecurity()->generatePasswordHash($this->password);
        $user->activationKey  = Yii::$app->security->generateRandomString(40);
        $user->save();

        $this->sendEmail();
        return $user;
    }

    protected function assignUserRole($role, $user){
        $userRole = Yii::$app->authManager->getRole($role);
        Yii::$app->authManager->assign($userRole, $user->getId());
    }

    protected function sendEmail(){

    }
}