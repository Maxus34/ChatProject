<?php

namespace app\models;

use developeruz\db_rbac\interfaces\UserRbacInterface;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\web\IdentityInterface;
use yii\behaviors\TimestampBehavior;
use Yii;

class User extends ActiveRecord implements IdentityInterface, UserRbacInterface
{
   // public $id;
   // public $username;
   // public $email;
   // public $password;
   // public $auth_key;

    const SCENARIO_UPDATE = 'update';

    public static function tableName()
    {
        return 'user';
    }

    public function  behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord :: EVENT_BEFORE_INSERT => ['reg_date'],
                ],
            ],
        ];

    }

    public function beforeSave($insert){
        if (parent::beforeSave($insert)){
            if ($this->isNewRecord){
                $this->auth_key = \Yii::$app->security->generateRandomString();
            }
            return true;
        }
        return false;
    }

    public function scenarios()
    {
        $scenarios =  parent::scenarios();
        $scenarios[self::SCENARIO_UPDATE] = ['username', 'email', 'active'];

        return $scenarios;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUserName()
    {
       return $this->username;
    }

    public function getAuthKey()
    {
        return $this->auth_key;
    }

    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
       return static::findOne(['access_token' => $token]);
    }

    public static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    public function validateAuthKey($authKey)
    {
        return $this->auth_key === $authKey;
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

}
