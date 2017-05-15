<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 12.05.2017
 * Time: 14:30
 */

namespace app\modules\chat\models;

use app\modules\chat\records\{ DialogRecord, DialogReferenceRecord, MessageRecord, MessageReferenceRecord };
use app\modules\chat\models\DialogProperties;
use yii\base\Model;

class DialogN extends Model {

    protected $userId;

    /**
     * @var DialogRecord
     */
    public $dialogRecord;

    /**
     * @var array
     */
    public $dialogReferences;


    public function __construct($dRecord, $dReferences) {
        parent::__construct();


        $this->userId = \Yii::$app->user->getId();
        $this->dialogRecord = $dRecord;
        $this->dialogReferences = $dReferences;
    }


    public function getId() {
        return $this->dialogRecord->id;
    }


    public function getUserId() {
        return $this->userId;
    }


    public function setTitle($title) {
        $this->dialogRecord->title = $title;
    }


    public function getTitle() {
        return $this->dialogRecord->title;
    }


    public function getReferences(bool $exclude_me = false) {
        $references = $this->dialogReferences;
        if ($exclude_me) {
            unset($references[$this->userId]);
        }
        return $references;
    }


    public function getUsers(bool $exclude_me = false) {
        $users = [];
        foreach ($this->dialogReferences as $reference) {
            $users[$reference->user->id] = $reference->user;
        }

        if ($exclude_me) {
            unset($users[$this->userId]);
        }

        return $users;
    }


    public function isActive() {
        return $this->dialogReferences[$this->userId]->isActive;
    }


    public function isCreator($user_id = false) {
        if (!$user_id) {
            return $this->dialogRecord->createdBy == $this->userId;
        }

        return $this->dialogRecord->createdBy == $user_id;
    }


    public function getMessages(int $offset = null, int $limit = null, array $conditions = null){

        return Message::getMessagesInstances($this->userId, $this->getId(), $offset, $limit, $conditions);

    }

    public function getMessagesCount($new = false){
        $query = MessageReferenceRecord::find() -> where(['dialogId' => $this->getId(), 'userId' => $this->userId]);

        if ($new)
            $query = $query -> andWhere(["isNew"  => 1]) -> andWhere(['!=', 'createdBy', $this->userId]);

        return $query->count();
    }

    public function getIsSeenMessages(array $messages){
        return Message::getIsSeenMessages($this->userId, $this->getId(), $messages);
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