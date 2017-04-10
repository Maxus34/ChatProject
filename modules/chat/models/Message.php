<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 22.01.2017
 * Time: 20:08
 */

namespace app\modules\chat\models;

use app\modules\chat\models\records\{ MessageRecord, MessageReferenceRecord};
use yii\base\{ Exception, Model };

class Message extends Model
{
    private  $user_id            = null;
    private  $message_record     = null;
    private  $message_references =   [];

    public function behaviors()
    {
        return [

        ];
    }

    // Not used
    static function  getMessageInstance(int $message_id = null){
        $message_record = MessageRecord::findOne($message_id);
        if (empty($message_record))
            throw new Exception("Empty message record id = $message_id");

        return new static($message_record);
    }

    static function  getMessagesInstances(int $user_id, int $dialog_id, int $offset = null, int $limit = null, array $conditions = null){

        $query = MessageReferenceRecord::find()->where(['user_id' => $user_id, 'dialog_id' => $dialog_id])
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

    static function  getIsSeenMessages(int $user_id, int $dialog_id, array $messages){
        $references = MessageReferenceRecord::find()
            ->where(['dialog_id' => $dialog_id, 'message_id' => $messages, 'user_id' => $user_id])
            ->all();

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
        $seen = [];
        foreach ($references as $reference){
            $reference -> is_new = 0;
            if ($reference -> save()) {
                $seen[] = $reference -> message_id;
            }
        }

        return $seen;
    }

    public function __construct(MessageReferenceRecord $message_ref_rec = null, Dialog $dialog = null, string $content = null, array $files = []){
        parent::__construct();

        // Creating a new Message
        if (empty($message_ref_rec)){
            $this->user_id = \Yii::$app->user->getId();

            $this->message_record  = new MessageRecord($dialog->getId(), $content);
            $this->message_record -> save();

            $this->createReferences($dialog->getUsers());

            if (count($files) > 0){
                $this->message_record->attachFiles($files);
            }

        // Getting an old Message
        } else {

            $this->message_references[$message_ref_rec->user_id] = $message_ref_rec;
            $this->message_record   = $message_ref_rec->message;
            $this->user_id          = \Yii::$app->user->getId();
        }
    }


    public function  isAuthor(int $user_id)
    {
        return  ($this->message_references[$this->user_id]->created_by === $this->user_id);
    }

    /*
     *  @return integer Message author ID
     */
    public function getAuthorId()
    {
         return $this->message_record->created_by;
    }

    public function  getId()
    {
        return $this -> message_record -> id;
    }

    public function  getCreationDate()
    {
        return $this -> message_record -> created_at;
    }

    public function  getContent()
    {
        return $this -> message_record -> content;
    }

    public function  isNew()
    {
        return $this->message_references[$this->user_id]->is_new;
    }

    public function getFiles(){
        return $this->message_record->getFiles();
    }


    public function  save(){
        $this->message_record->save();
        foreach ($this->message_references as $ref){
            $ref->save();
        }
    }

    public function  delete(){

        $this->findReferences();

        $this->message_references[$this->user_id]->delete();
        if (count($this->message_references) > 1){

        } else {
            $this->message_record->delete();
        }

        return $this->message_record->id;
    }


    private function createReferences(array $users){
        foreach ($users as $user){
            if ( empty($this->message_references[$user->id]) ) {
                $ref = new MessageReferenceRecord(
                    $this->message_record->dialog_id,
                    $this->message_record->id,
                    $user->id
                );

                try{
                    $ref->save();
                } catch (Exception $e){
                   // \Yii::warning("Error: {$e->getMessage()}", "message_reference");
                }

                $this->message_references[$ref->user_id] = $ref;
            }
        }
    }

    private function findReferences(){

        $message_references = MessageReferenceRecord::find()->where(['message_id' => $this->message_record->id])->all();

        foreach($message_references as $reference){
            $this->message_references[$reference->user_id] = $reference;
        }
    }
}