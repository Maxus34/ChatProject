<?php

namespace app\models;

use app\behaviors\ImageBehavior;
use developeruz\db_rbac\interfaces\UserRbacInterface;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\behaviors\TimestampBehavior;
use Yii;

/**
 * Class User
 * @package app\models
 *
 * @property Integer $id
 * @property Integer $isActive
 * @property Integer $createdAt
 * @property String  $username
 * @property String  $email
 * @property String  $password
 * @property String  $authKey
 * @property String  $activationKey
 */

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
        return false; //static::findOne(['access_token' => $token]);
    }

    static function findByUsername($username)
    {
        return static::findOne(['username' => $username]);
    }

    public function  behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord :: EVENT_BEFORE_INSERT => ['createdAt'],
                ],
            ],
            [
                'class'             => ImageBehavior::class,
                'placeholderPath'  => 'images/placeholder/user_placeholder.png',
                'key'               => 'user_images',
            ]
        ];

    }

    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_UPDATE] = ['username', 'email', 'isActive', 'image'];

        return $scenarios;
    }

    public function beforeSave($insert){
        if (parent::beforeSave($insert)){
            if ($this->isNewRecord){
                $this->authKey = \Yii::$app->security->generateRandomString();
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
        return $this->authKey;
    }


    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }

    public function validatePassword($password)
    {
        return Yii::$app->security->validatePassword($password, $this->password);
    }

}
