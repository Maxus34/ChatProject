<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 15.01.2017
 * Time: 14:48
 */

namespace app\modules\chat\models;

use app\modules\chat\records\ { MessageReferenceRecord, DialogReferenceRecord };
use yii\base\Exception;
use yii\web\HttpException;


class Dialog extends DialogBase
{
    public function getTypingUsers(){

        if (count($this->dialogReferences) <= 1)
            $references = $this->findDialogReferences();
        else
            $references = $this->dialogReferences;

        unset($references[$this->_userId]);
        $users_array = [];

        foreach ($references as $reference){
            if ($reference->isTyping){
                if ( time() - $reference->updatedAt > static::MAX_TYPING_TIMEOUT){
                    $reference -> isTyping = 0;
                    $reference -> save();

                } else {
                    $users_array[] = $reference->user->username;
                }
            }
        }

        return $users_array;
    }

    public function getMessages(int $offset = null, int $limit = null, array $conditions = null){

        return Message::getMessagesInstances($this->_userId, $this->getId(), $offset, $limit, $conditions);

    }

    public function getMessagesCount($new = false){
        $query = MessageReferenceRecord::find() -> where(['dialogId' => $this->getId(), 'userId' => $this->_userId]);

        if ($new)
            $query = $query -> andWhere(["isNew"  => 1]) -> andWhere(['!=', 'createdBy', $this->_userId]);

        return $query->count();
    }

    public function getIsSeenMessages(array $messages){
        return Message::getIsSeenMessages($this->_userId, $this->getId(), $messages);
    }

    public function addMessage($content, $files = []){
        if ($this->isActive()){
            try{

                $message = new Message(null, $this, $content, $files);
                $message -> save();

            } catch (Exception $e){
                debug ($e->getMessage());
                die();
            }

            return $message;
        } else {
            throw new HttpException(403, 'Inactive user ' . $this->getUserId() . ' is trying to send message');
        }

    }

    public function deleteMessages(array $messages_ids){

        $messages = $this->getMessages(null, null, [['messageId' => $messages_ids]]);
        $success = [];

        foreach ($messages as $message){
            $success[] = $message->delete();
        }

        return $success;
    }

    public function setSeenMessages (array $messages = null){
        if (empty($messages))
            throw new Exception("setSeenMessages => Empty messages array");

        return Message::setSeenMessages($this->getId(), $messages);
    }

    public function setIsTyping($is_typing){
         if ( !isset($this->dialogReferences[$this->_userId]) )
             $reference = DialogReferenceRecord::findOne(['userId' => $this->_userId, 'dialogId' => $this->getId()]);
         else
             $reference = $this -> dialogReferences[$this->_userId];

         $reference -> isTyping = $is_typing ? 1 : 0;
         $reference -> save();
    }

}


