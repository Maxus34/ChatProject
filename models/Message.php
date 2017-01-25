<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 17.12.2016
 * Time: 13:35
 */

namespace app\models;


use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class Message extends ActiveRecord
{
    public static function tableName(){
        return "t_message";
    }

    public function  behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord :: EVENT_BEFORE_INSERT => ['creation_date'],
                ],
            ],
        ];

    }

    public function rules(){
        return [
            [['user_id', 'dialog_id', 'content'], 'required'],
        ];
    }

    public function getUser(){
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}