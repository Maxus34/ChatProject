<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 17.05.2017
 * Time: 17:23
 */

namespace app\modules\chat\services;

use app\modules\chat\models\  { MessageN, DialogN };
use app\modules\chat\records\ { MessageRecord, MessageReferenceRecord };

class MessageFactory {
    /** @var  DialogN */
    protected $dialog;

    public function __construct(DialogN $dialog) {
        $this->dialog = $dialog;
    }


    public function getMessageInstanceById(int $id){
        $messageRecord = MessageRecord::find()
            ->where(['id' => $id])
            ->with('references')
            ->one();

        return new MessageN($messageRecord, $this->processMessageReferences($messageRecord->references));
    }


    public function getMessageInstancesByConditions(int $offset = null, int $limit = null, array $conditions = null) {
        $query = MessageRecord :: find()
            -> innerJoin('message_ref', '`message`.`id` = `message_ref`.`messageId`')
            //-> groupBy('`message`.`id`')
            -> where("`message_ref`.`dialogId` = {$this->dialog->getId()} AND `message_ref`.`userId` = {$this->dialog->getUserId()}")
            -> with('references');

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

        $messageRecords = $query -> all();

        $messages = [];

        foreach ($messageRecords as $record){
            $messages[] = new MessageN($record, $this->processMessageReferences($record->references));
        }

        return $messages;
    }


    public function createNewMessage(string $content, array $files = []){
        if ($this->dialog->isActive()){

            $messageRecord = $this->createAndSaveMessageRecord($content, $files);
            $messageReferences = $this->createAndSaveMessageReferences(
                $messageRecord->id,
                $this->dialog->getReferences()
            );

            return new MessageN($messageRecord, $messageReferences);

        } else {
            return false;
        }
    }




    protected function processMessageReferences (array $references){
        $referencesProcessed = [];

        foreach ($references as $reference){
            $referencesProcessed[$reference->userId] = $reference;
        }

        return $referencesProcessed;
    }


    protected function createAndSaveMessageRecord ($content, $files = []) :MessageRecord{
        $messageRecord = new MessageRecord($this->dialog->getId(), $content);
        $messageRecord -> save();

        $messageRecord -> attachFiles($files);

        return $messageRecord;
    }


    protected function createAndSaveMessageReferences (int $messageId, array $dialogReferences) :array{

        $messageReferences = [];

        foreach ($dialogReferences as $reference){
            if ( $reference->isActive ) {
                $reference = new MessageReferenceRecord(
                    $this->dialog->getId(),
                    $messageId,
                    $reference->userId
                );

                $reference -> save();

                $messageReferences[$reference->userId] = $reference;
            }
        }

        return $messageReferences;
    }
}