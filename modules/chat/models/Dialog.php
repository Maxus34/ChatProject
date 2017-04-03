<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 15.01.2017
 * Time: 14:48
 */

namespace app\modules\chat\models;

use app\modules\chat\models\records\MessageReferenceRecord;
use yii\base\Exception;
use yii\web\HttpException;

use app\modules\chat\models\records\DialogReferenceRecord;


class Dialog extends DialogBase
{
    public function getTypingUsers(){

        if (count($this->_dialog_references) <= 1)
            $references = $this->findDialogReferences();
        else
            $references = $this->_dialog_references;

        unset($references[$this->_user_id]);
        $users_array = [];

        foreach ($references as $reference){
            if ($reference->is_typing){
                if (time() - $reference->updated_at > static::MAX_TYPING_TIMEOUT){
                    $reference -> is_typing = 0;
                    $reference -> save();

                } else {
                    $users_array[] = $reference->user->username;
                }
            }
        }

        return $users_array;
    }

    public function getMessages(int $offset = null, int $limit = null, array $conditions = null){

        return Message::getMessagesInstances($this->_user_id, $this->getId(), $offset, $limit, $conditions);

    }

    public function getMessagesCount($new = false){
        $query = MessageReferenceRecord::find() -> where(['dialog_id' => $this->getId(), 'user_id' => $this->_user_id]);

        if ($new)
            $query = $query -> andWhere(["is_new"  => 1]) -> andWhere(['!=', 'created_by', $this->_user_id]);

        return $query->count();
    }

    public function getIsSeenMessages(array $messages){
        if (empty($messages))
            throw new Exception("setSeenMessages => Empty messages array");

        return Message::getIsSeenMessages($this->_user_id, $this->getId(), $messages);
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

    public function deleteMessages(array $messages_id){

        $messages = $this->getMessages(null, null, [['message_id' => $messages_id]]);
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
         if ( !isset($this->dialog_references[$this->_user_id]) )
             $reference = DialogReferenceRecord::findOne(['user_id' => $this->_user_id, 'dialog_id' => $this->getId()]);
         else
             $reference = $this -> dialog_references[$this->_user_id];

         $reference -> is_typing = $is_typing ? 1 : 0;
         $reference -> save();
    }

}


