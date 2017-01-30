<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 22.01.2017
 * Time: 20:08
 */

namespace app\modules\chat\models;

use app\modules\chat\models\records\{ MessageRecord, MessageReferenceRecord};
use yii\base\Exception;
use yii\base\Model;

class Message extends Model
{
    //TODO Refactor this shit

    private  $user_id            = null;
    private  $message_record     = null;
    private  $message_references =   [];

    static function  getMessageInstance(int $message_id = null){
        $message_record = MessageRecord::findOne($message_id);
        if (empty($message_record))
            throw new Exception("Empty message record id = $message_id");

        return new static($message_record);
    }

    static function  getMessagesInstances(int $user_id, int $dialog_id, int $offset = null, int $limit = null, array $conditions = null){

        $query = MessageReferenceRecord::find()->where(['user_id' => $user_id, 'dialog_id' => $dialog_id]);

        if ( !empty($conditions))
            foreach ($conditions as $condition)
                $query = $query -> andWhere($condition);

        if ( !empty( $offset) ){
            if ($offset < 0)
                $offset += $query->count();

            $query =  $query -> offset($offset);
        }
        if ( !empty( $limit) )
            $query =  $query -> limit($limit);

        $message_reference_records = $query -> with('message')-> all();
        $messages = [];

        foreach ($message_reference_records as $record){
            $messages[] = new static($record->message);
        }

        return $messages;
    }

    static function  getIsSeenMessages(int $user_id, int $dialog_id, array $messages){
        $references = MessageReferenceRecord::find()->where(['dialog_id' => $dialog_id, 'message_id' => $messages, 'user_id' => $user_id])->all();

        $seen = [];
        foreach ($references as $reference){
            if (!$reference -> is_new) {
                $seen[] = $reference -> message_id;
            }
        }

        return $seen;
    }

    static function  setSeenMessages(int $dialog_id, array $messages){
        $references = MessageReferenceRecord::find()->where(['dialog_id' => $dialog_id, 'message_id' => $messages])->all();
        $success = [];
        foreach ($references as $reference){
            $reference -> is_new = 0;
            if ($reference -> save()) {
                $success[] = $reference -> message_id;
            }
        }

        return $success;
    }


    public function __construct(MessageRecord $message_rec = null, Dialog $dialog = null, $content = null) // Only for creating new Records;
    {
        parent::init();

        if (!empty($message_rec)){
            $this->message_record = $message_rec;
            $this->user_id = \Yii::$app->user->getId();

            $reference = MessageReferenceRecord::findOne(['message_id' => $this->getId(), 'user_id' => $this->user_id]);
            if (empty($reference))
                throw new Exception("Empty reference when initialize message");

            $this->message_references[$reference->user_id] = $reference;

        } else {
            $this->user_id = $dialog->getUserId();

            $this->message_record  = new MessageRecord($dialog->getId(), $content);
            $this->message_record -> save();

            $this->createReferences($dialog->getUsers());
        }
    }

    public function  isAuthor(int $user_id){
        return MessageReferenceRecord::findOne(['message_id' => $this->getId(), 'user_id' => $user_id])->is_author;
    }

    public function  getId(){
        return $this -> message_record -> id;
    }

    public function  getCreationDate(){
        return $this -> message_record -> created_at;
    }

    public function  getContent(){
        return $this -> message_record -> content;
    }

    public function  isNew(){
        return $this->message_references[$this->user_id]->is_new;
    }


    public function  save(){
        //TODO Fix method Message::save();
        $this->message_record->save();
        foreach ($this->message_references as $ref){
            $ref->save();
        }
    }

    public function  delete(){
        //TODO Create method Message::delete();
    }


    private function createReferences(array $users){
        foreach ($users as $user){

            $ref = new MessageReferenceRecord(
                $this->message_record->dialog_id,
                $this->message_record->id,
                $user->id,
                ($user->id == $this->user_id) ? 1 : 0
            );
            try{
                $ref->save();
            } catch (Exception $e){

            }

            $this->message_references[$user->id] = $ref;
        }
    }

    private function findMessageReferences(){
        $message_references = MessageReferenceRecord::find()->where(['message_id' => $this->getId()])->all();
        foreach($message_references as $reference){
            $this->message_references[$reference->user_id] = $reference;
        }
    }
}