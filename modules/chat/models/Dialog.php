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
use yii\base\Exception;
use yii\base\Model;
use app\modules\chat\models\records\DialogRecord;
use app\modules\chat\models\records\DialogReferenceRecord;

class Dialog extends Model
{
    private  $user_id            = null;
    private  $dialog_record      = null;
    private  $dialog_references  =   [];

    static function getDialogInstance(int $dialog_id = null){
        if (empty($dialog_id)){
            return (new static())->initDialog(null, \Yii::$app->user->getId());
        }

        if (empty($dialog_record = DialogRecord::findOne($dialog_id)))
            throw new Exception("Error: Dialog don't exists. Please try later...");

        return (new static())->initDialog($dialog_record, \Yii::$app->user->getId());
    }

    static function getDialogInstances(int $offset = null, int $limit = null){

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
        if (count($this->dialog_references) <= 1){
            $this->findDialogReferences();
        }
        $users = [];
        foreach ($this->dialog_references as $reference) {
            $users[] = $reference->user;
        }
        return $users;
    }

    public function getTypingUsers(){
        if (count($this->dialog_references) <= 1){
            $this->findDialogReferences();
        }

        $users_array = [];
        foreach ($this->dialog_references as $reference){
            if ( ($reference->is_typing) && ($reference->user_id != $this->user_id) ){
                $users_array[] = $reference->user->username;
            }
        }

        return $users_array;
    }

    public function getMessages(int $offset = null, int $limit = null, array $conditions = null){

        return Message::getMessagesInstances($this->user_id, $this->getId(), $offset, $limit, $conditions);

    }

    public function getMessagesCount($new = false){
        $query = MessageReferenceRecord::find() -> where(['dialog_id' => $this->getId(), 'user_id' => $this->user_id]);

        if (!$new)
            $query = $query -> andWhere(['is_new' => 1]);

        return $query->count();
    }

    public function getIsSeenMessages(array $messages){
        if (empty($messages))
            throw new Exception("setSeenMessages => Empty messages array");

        return Message::getIsSeenMessages($this->user_id, $this->getId(), $messages);
    }


    public function setDialogAttributes (){
        //TODO Create Model DialogAttributes and method for setting them;
    }

    public function setSeenMessages (array $messages = null){
        if (empty($messages))
            throw new Exception("setSeenMessages => Empty messages array");

        return Message::setSeenMessages($this->getId(), $messages);
    }

    public function setIsTyping($is_typing){
         if ( !isset($this->dialog_references[$this->user_id]) )
             $reference = DialogReferenceRecord::findOne(['user_id' => $this->user_id, 'dialog_id' => $this->getId()]);
         else
             $reference = $this -> dialog_references[$this->user_id];

         $reference -> is_typing = $is_typing ? 1 : 0;
         $reference -> save();
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

        $reference = DialogReferenceRecord::find()->where(['user_id' => $this->user_id, 'dialog_id' => $this->getId()])->one();
        if (empty($reference))
            throw new Exception("Error: You don't belong to this dialog");

        $this->dialog_references[$reference->user_id] = $reference;
        return $this;
    }

    private function findDialogReferences(){
        $dialog_references = DialogReferenceRecord::find()->where(['dialog_id' => $this->getId()])->all();
        foreach($dialog_references as $reference){
            $this->dialog_references[$reference->user_id] = $reference;
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


