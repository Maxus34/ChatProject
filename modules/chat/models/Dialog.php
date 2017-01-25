<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 15.01.2017
 * Time: 14:48
 */

namespace app\modules\chat\models;

use app\models\User;
use app\modules\chat\models\records\MessageReferenceRecord;
use yii\base\Model;
use app\modules\chat\models\records\DialogRecord;
use app\modules\chat\models\records\DialogReferenceRecord;

class Dialog extends Model
{
    private  $user_id            = null;
    private  $dialog_record      = null;
    private  $dialog_references  =   [];

    static public function getDialogInstance(int $dialog_id = null){
        if (empty($dialog_id)){
            return (new static())->initDialog(null, \Yii::$app->user->getId());
        }
        $dialog_record = DialogRecord::findOne($dialog_id);
        if (empty($dialog_record))
            return null;

        return (new static())->initDialog($dialog_record, \Yii::$app->user->getId());
    }

    static public function getDialogInstances(int $offset = null, int $limit = null){

        $query = DialogReferenceRecord::find()->where(['user_id' => \Yii::$app->user->getId()]);

        if ($offset < 0){
            $count = $query->count();
            $offset += $count;
        }

        if ( !empty( $offset) )
            $query =  $query -> offset($offset);
        if ( !empty( $limit) )
            $query =  $query -> limit($limit);

        $dialog_reference_records = $query -> all();
        $dialogs = [];

        foreach ($dialog_reference_records as $record){
            $dialogs[] = static::getDialogInstance($record->dialog_id);
        }

        return $dialogs;
    }


    public function getId(){
        return $this->dialog_record->id;
    }

    public function getTitle(){
        return $this->dialog_record->title;
    }

    public function getUsers(){
        if (empty($this->dialog_references)){
            $this->findDialogReferences();
        }
        $users = [];
        foreach ($this->dialog_references as $reference) {
            $users[] = $reference->user;
        }
        return $users;
    }

    public function getMessages(int $offset = null, int $limit = null){

        return Message::getMessagesInstances($this->user_id, $this->getId(), $offset, $limit);
    }

    public function getOldMessages(int $last_message_id){

    }

    public function getMessagesCount($new = false){
        $query = MessageReferenceRecord::find();
        if (empty($new)){
            $query = $query -> where(['dialog_id' => $this->getId(), 'user_id' => $this->user_id]);
        } else {
            $query = $query -> where(['dialog_id' => $this->getId(), 'user_id' => $this->user_id, 'is_new' => true]);
        }

        return $query->count();
    }

    public function addMessage($content){
        try{
            $message = Message::createNewMessage($this->getId(), $content, $this->user_id, $this->getUsers());
            $message -> save();

        } catch (Exception $e){
            debug ($e->getMessage());
            die();
        }

        return $message;
    }

    public function save(){
        //TODO create method Message::save();
    }

    public function delete(){
        //TODO create method Message::delete();
    }


    private function initDialog(DialogRecord $dialog_rec = null, int $user_id) :Dialog{
        if (empty($dialog_rec)){
            $this-> dialog_record = new DialogRecord();
            $this-> dialog_record-> title = 'Dialog_' . \Yii::$app->formatter->asDate(new \DateTime());
            $this-> dialog_record-> save();
            $this-> createDialogReference($user_id);

        } else {
            $this->dialog_record = $dialog_rec;
        }

        $this->user_id = $user_id;
        return $this;
    }

    private function findDialogReferences(){
        $dialog_ref = DialogReferenceRecord::find()->where(['dialog_id' => $this->getId()])->all();
        foreach($dialog_ref as $key => $value){
            $this->dialog_references[$key] = $value;
        }
    }

    private function createDialogReference($user_id){
        $dr = new DialogReferenceRecord();
        $dr -> user_id = $user_id;
        $dr -> dialog_id = $this->getId();
        if ($this->user_id == $user_id){
            $dr -> is_creator = true;
        }
        $dr->save();
    }

}


