<?php

namespace app\modules\chat\services;

use app\modules\chat\models\  { DialogN, MessageN };
use app\modules\chat\records\ { MessageRecord, MessageReferenceRecord };

class MessageRepository {

    /** @var  DialogN|null */
    protected $dialog = null;

    /** @var MessageFactory|null  */
    protected $messageFactory = null;

    /** @var array MessageN */
    protected static $messages = [];


    public function __construct(DialogN $dialog) {
        $this->dialog         = $dialog;
        $this->messageFactory = new MessageFactory($dialog);
    }


    public function findById(int $id){

        if ( empty(static::$messages[$id]) ){
            $message = $this->messageFactory->getMessageInstanceById($id);

            if ( !empty($message) ){
                static::$messages[$id] = $message;

            } else {
                return false;
            }
        }

        return static::$messages[$id];
    }


    public function findMessagesByConditions(int $offset = null, int $limit = null, array $conditions = null){
        $messages = $this -> messageFactory
            -> getMessageInstancesByConditions($offset, $limit, $conditions);

        foreach ($messages as $message){
            static::$messages[$message->getId()] = $message;
        }

        return $messages;
    }


    public function saveMessage(MessageN $message){
        $message->messageRecord->save();
        foreach ($message->messageReferences as $reference){
            $reference -> save();
        }
    }


    public function deleteMessages(array $messageIds) :array{
        $messages = $this->findMessagesByConditions(null, null, [['messageId' => $messageIds]]);

        $deletedMessages = [];

        foreach ($messages as $message){
            $deletedMessages[] = $this->deleteMessage($message);
        }

        return $deletedMessages;
    }


    public function deleteMessage(MessageN $message){

        // Delete a messageRecord for current user
        $message->messageReferences[$this->dialog->getUserId()] -> delete();
        unset($message->messageReferences[$this->dialog->getUserId()]);

        // If no more users who can get this message delete a messageRecord
        if ( count($message->messageReferences) < 1){

            if (isset(static::$messages[$message->getId()])){
                unset(static::$messages[$message->getId()]);
            }

            $message -> messageRecord -> delete();
        }

        return $message -> messageRecord -> id;
    }


}