<?php

namespace app\models;

use app\behaviors\ImageBehavior;
use app\models\records\UserImageRecord;
use developeruz\db_rbac\interfaces\UserRbacInterface;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\web\IdentityInterface;
use yii\behaviors\TimestampBehavior;
use Yii;

/* @property $id*/
/* @property $username*/
/* @property $email*/
/* @property $password*/
/* @property $auth_key*/

class User extends ActiveRecord implements IdentityInterface, UserRbacInterface
{
    static $users = [];

    public $image;

    const SCENARIO_UPDATE = 'update';

    public static function tableName()
    {
        return 'user';
    }

    static function findIdentity($id)
    {
        if (isset(static::$users[$id])){
            return static::$users[$id];

        } else {
            static::$users[$id] = static::findOne($id);
            return  static::$users[$id];
        }
    }

    static function findIdentityByAccessToken($token, $type = null)
    {
        return static::findOne(['access_token' => $token]);
    }

    static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    public function  behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord :: EVENT_BEFORE_INSERT => ['created_at'],
                ],
            ],
            [
                'class'             => ImageBehavior::class,
                'placeholder_path'  => 'images/placeholder/user_placeholder.png',
                'key'               => 'user_images',
            ]
        ];

    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_UPDATE] = ['username', 'email', 'active', 'image'];

        return $scenarios;
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



    public function validateAuthKey($authKey)
    {
        return $this->auth_key === $authKey;
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

}
