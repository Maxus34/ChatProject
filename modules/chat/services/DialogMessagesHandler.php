<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 17.05.2017
 * Time: 16:49
 */

namespace app\modules\chat\services;

use app\modules\chat\models\  { DialogN, MessageN };
use app\modules\chat\records\ { MessageRecord, MessageReferenceRecord };

class DialogMessagesHandler {

    /** @var DialogN  */
    protected $dialog;

    public function __construct(DialogN $dialog) {
        $this -> dialog = $dialog;
    }


    public function getMessagesCount(bool $new = false) {
        $query = MessageRecord :: find()
            -> innerJoin('message_ref', '`message`.`id` = `message_ref`.`messageId`')
            //-> groupBy('`message`.`id`')
            -> where("`message_ref`.`dialogId` = {$this->dialog->getId()} AND `message_ref`.`userId` = {$this->dialog->getUserId()}");

        if ($new){
            $query = $query -> andWhere("`message_ref`.`isNew`=1 AND `message_ref`.`userId`!={$this->dialog->getUserId()}");
        }

        return $query->count();
    }


    public function getIsSeenMessages (array $messageIds) :array {
        $messageReferences = MessageReferenceRecord::find()
            -> where([
                'dialogId'  => $this->dialog->getId(),
                'userId'    => $this->dialog->getUserId(),
                'messageId' => $messageIds
            ]) -> all();

        $seenMessageIds = [];

        foreach ($messageReferences as $reference){
            if ( !$reference -> isNew ){
                $seenMessageIds[] = $reference->messageId;
            }
        }

        return $seenMessageIds;
    }


    public function setMessagesThatHasBeenSeen(array $messageIds) :array{
        $messageReferences = MessageReferenceRecord::find()
            -> where([
                'dialogId'  => $this->dialog->getId(),
                'messageId' => $messageIds
            ])
            //->andWhere(['!=', 'userId', $this->dialog->getUserId()])
            -> all();

        $seenMessages = [];

        foreach ($messageReferences as $reference){
            $reference -> isNew = 0;
            $reference -> save();

            $seenMessages[] = $reference->messageId;
        }

        return $seenMessages;
    }


    public function addMessageToTheDialog(string $content, array $files = []) :MessageN{
        $messageFactory = new MessageFactory($this->dialog);

        $message = $messageFactory->createNewMessage($content, $files);

        $this->dialog->messageRepository->saveMessage($message);

        return $message;
    }
}