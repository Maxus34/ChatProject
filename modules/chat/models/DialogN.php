<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 12.05.2017
 * Time: 14:30
 */

namespace app\modules\chat\models;

use app\modules\chat\records\ { DialogRecord,  DialogReferenceRecord };
use app\modules\chat\services\{
    DialogHandler, DialogMessagesHandler, MessageRepository
};
use yii\base\Model;


/**
 * Class DialogN
 * @package app\modules\chat\models
 *
 * @property  DialogMessagesHandler $messageHandler
 * @property  DialogHandler         $dialogHandler
 * @property  MessageRepository     $messageRepository
 *
 */
class DialogN extends Model {

    const MAX_TYPING_TIMEOUT = 4;

    /**
     * @var DialogRecord
     */
    public $dialogRecord;

    /**
     * @var array
     */
    public $dialogReferences;

    /** @var int */
    protected $userId;

    /**
     * @var DialogHandler
     */
    protected $_dialogHandler = null;

    /**
     * @var DialogMessagesHandler
     */
    protected $_messagesHandler = null;

    /**
     * @var MessageRepository
     */
    protected $_messageRepository = null;


    public function __construct($dRecord, $dReferences) {
        parent::__construct();

        $this->userId           = \Yii::$app->user->getId();
        $this->dialogRecord     = $dRecord;
        $this->dialogReferences = $dReferences;
    }


    public function getDialogHandler ()     :DialogHandler {
        if ( empty($this->_dialogHandler) ){
            $this->_dialogHandler = new DialogHandler($this);
        }

        return $this->_dialogHandler;
    }


    public function getMessageHandler ()   :DialogMessagesHandler {
        if ( empty($this->_messagesHandler) ){
            $this->_messagesHandler = new DialogMessagesHandler($this);
        }

        return $this->_messagesHandler;
    }


    public function getMessageRepository () :MessageRepository {
        if ( empty($this->_messageRepository) ){
            $this->_messageRepository = new MessageRepository($this);
        }

        return $this->_messageRepository;
    }


    public function  getId() {
        return $this->dialogRecord->id;
    }


    public function  getUserId() {
        return $this->userId;
    }


    public function  setTitle($title) {
        $this->dialogRecord->title = $title;
    }


    public function  getTitle() {
        return $this->dialogRecord->title;
    }


    public function  getReferences (bool $excludeMe = false) {
        $references = $this->dialogReferences;
        if ($excludeMe) {
            unset($references[$this->userId]);
        }
        return $references;
    }


    public function  getUsers (bool $excludeMe = false) {
        $users = [];
        foreach ($this->dialogReferences as $reference) {
            $users[$reference->user->id] = $reference->user;
        }

        if ($excludeMe) {
            unset($users[$this->userId]);
        }

        return $users;
    }


    public function  isActive () :bool{
        return $this->dialogReferences[$this->userId]->isActive;
    }


    public function  isCreator (int $userId = null) {
        if ( empty($userId) ) {
            return $this->dialogRecord->createdBy == $this->userId;
        }

        return $this->dialogRecord->createdBy == $userId;
    }


    public function  setIsTyping ($isTyping) {
        if ( !isset($this->dialogReferences[$this->userId]) )
            $reference = DialogReferenceRecord::findOne(['userId' => $this->userId, 'dialogId' => $this->getId()]);
        else
            $reference = $this -> dialogReferences[$this->userId];

        $reference -> isTyping = $isTyping ? 1 : 0;
        $reference -> save();
    }
}