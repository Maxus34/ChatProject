<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 17.12.2016
 * Time: 12:41
 */

namespace app\models;


use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;


class Dialog extends ActiveRecord
{
    public static function tableName(){
        return "t_dialog";
    }

    public function behaviors()
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

    public function getDialogUsers(){
        return $this->hasMany(DialogUser::className(), ['dialog_id' => 'id']);
    }

    public function getMessages(){
        return $this->hasMany(Message::className(), ['dialog_id' => 'id']);
    }

    public function getCountOfMessages(){
        return $this->getMessages()->count();
    }

    public function getLastMessages($limit){
        $messages_count = $this->getMessages()->count();
        $offset = $messages_count - $limit;
        if ($offset > 0) {
            return  $messages = $this->getMessages()->offset($offset)->limit($limit)->all();
        } else {
            return Message::find()->where(['dialog_id' => $this->id])->all();
        }
    }

    public function getMessagesUntilDate($limit, $date){
        $messages_count = $this->getMessages()->where(['<', 'creation_date', $date])->count();
        $offset = $messages_count - $limit;
        if ($offset > 0) {
            return $messages = $this->getMessages()->where(['<', 'creation_date', $date])->offset($offset)->limit($limit)->all();
        } else {
            return $messages = $this->getMessages()->where(['<', 'creation_date', $date])->limit($limit)->all();
        }
    }

    public function getMessagesAfterDate($date){
        return $messages = $this->getMessages()->where(['>', 'creation_date', $date])->all();
    }
}