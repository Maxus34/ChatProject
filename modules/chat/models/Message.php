<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 22.01.2017
 * Time: 20:08
 */

namespace app\modules\chat\models;

use app\modules\chat\records\{ MessageRecord, MessageReferenceRecord};
use yii\base\{ Exception, Model };

class Message extends Model
{
    private  $userId            = null;
    private  $messageRecord     = null;
    private  $messageReferences =   [];

    // Not uses
    static function  getMessageInstance(int $message_id = null){
        $messageRecord = MessageRecord::findOne($message_id);
        if (empty($messageRecord))
            throw new Exception("Empty message record id = $message_id");

        return new static($messageRecord);
    }

    static function  getMessagesInstances(int $userId, int $dialog_id, int $offset = null, int $limit = null, array $conditions = null){

        $query = MessageReferenceRecord::find()->where(['userId' => $userId, 'dialogId' => $dialog_id])
            -> with('message');

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

        $message_reference_records = $query -> all();
        $messages = [];

        foreach ($message_reference_records as $record){
            $messages[] = new static($record);
        }

        return $messages;
    }

    static function  getIsSeenMessages(int $userId, int $dialog_id, array $messages){
        $references = MessageReferenceRecord::find()
            ->where(['dialogId' => $dialog_id, 'messageId' => $messages, 'userId' => $userId])
            ->all();

        $seen = [];
        foreach ($references as $reference){
            if (!$reference -> isNew) {
                $seen[] = $reference -> messageId;
            }
        }

        return $seen;
    }

    static function  setSeenMessages(int $dialog_id, array $messages){
        $references = MessageReferenceRecord::find()->where(['dialogId' => $dialog_id, 'messageId' => $messages])->all();
        $seen = [];
        foreach ($references as $reference){
            $reference -> isNew = 0;
            if ($reference -> save()) {
                $seen[] = $reference -> messageId;
            }
        }

        return $seen;
    }

    public function __construct(MessageReferenceRecord $message_ref_rec = null, Dialog $dialog = null, string $content = null, array $files = []){
        parent::__construct();

        // Creating a new Message
        if (empty($message_ref_rec)){
            $this->userId = \Yii::$app->user->getId();

            $this->messageRecord  = new MessageRecord($dialog->getId(), $content);
            $this->messageRecord -> save();

            $this->createReferences($dialog->getUsers());

            if (count($files) > 0){
                $this->messageRecord->attachFiles($files);
            }

        // Getting an old Message
        } else {

            $this->messageReferences[$message_ref_rec->userId] = $message_ref_rec;
            $this->messageRecord   = $message_ref_rec->message;
            $this->userId          = \Yii::$app->user->getId();
        }
    }


    public function  isAuthor(int $userId)
    {
        return  ($this->messageReferences[$this->userId]->createdBy === $this->userId);
    }

    /**
     *  @return integer Message author ID
     */
    public function getAuthorId()
    {
         return $this->messageRecord->createdBy;
    }

    public function  getId()
    {
        return $this -> messageRecord -> id;
    }

    public function  getCreationDate()
    {
        return $this -> messageRecord -> createdAt;
    }

    public function  getContent()
    {
        return $this -> messageRecord -> content;
    }

    public function  isNew()
    {
        return $this->messageReferences[$this->userId]->isNew;
    }

    public function getFiles(){
        return $this->messageRecord->getFiles();
    }


    public function  save(){
        $this->messageRecord->save();
        foreach ($this->messageReferences as $ref){
            $ref->save();
        }
    }

    public function  delete(){

        $this->findReferences();

        $this->messageReferences[$this->userId]->delete();
        if (count($this->messageReferences) > 1){

        } else {
            $this->messageRecord->delete();
        }

        return $this->messageRecord->id;
    }


    private function createReferences(array $users){
        foreach ($users as $user){
            if ( empty($this->messageReferences[$user->id]) ) {
                $ref = new MessageReferenceRecord(
                    $this->messageRecord->dialogId,
                    $this->messageRecord->id,
                    $user->id
                );

                try{
                    $ref->save();
                } catch (Exception $e){
                   // \Yii::warning("Error: {$e->getMessage()}", "message_reference");
                }

                $this->messageReferences[$ref->userId] = $ref;
            }
        }
    }

    private function findReferences(){

        $messageReferences = MessageReferenceRecord::find()->where(['messageId' => $this->messageRecord->id])->all();

        foreach($messageReferences as $reference){
            $this->messageReferences[$reference->userId] = $reference;
        }
    }
}