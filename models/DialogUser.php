<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 17.12.2016
 * Time: 12:44
 */

namespace app\models;


use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class DialogUser extends ActiveRecord
{
    public static function tableName(){
        return "t_dialog_user";
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord :: EVENT_BEFORE_INSERT => ['invite_date'],
                ],
            ],
        ];
    }

    public function getDialog(){
        return $this->hasOne(Dialog::className(), ['id' => 'dialog_id']);
    }

    public function getUser(){
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }

    public function getMessages(){
        return $this->hasMany(Message::className(), ['dialog_id' => 'dialog_id', 'user_id' => 'user_id']);
    }

}