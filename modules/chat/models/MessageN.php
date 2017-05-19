<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 17.05.2017
 * Time: 17:12
 */

namespace app\modules\chat\models;

use yii\base\Object;
use app\modules\chat\records\ { MessageRecord, MessageReferenceRecord };

class MessageN extends Object{

    /** @var int */
    protected $userId;

    /** @var MessageRecord  */
    public $messageRecord;

    /** @var array|MessageReferenceRecord */
    public $messageReferences;

    public function __construct(MessageRecord $messageRecord, array $messageReferences) {
        $this->messageRecord     = $messageRecord;
        $this->messageReferences = $messageReferences;
        $this->userId            = \Yii::$app->user->getId();
    }


    public function getId(){
        return $this->messageRecord->id;
    }


    public function getContent(){
        return $this->messageRecord->content;
    }


    public function getCreationDate(){
        return $this->messageRecord->createdAt;
    }


    public function isNew(){
        return $this->messageReferences[$this->userId] -> isNew;
    }


    public function isAuthor(int $userId = null) {
        if (empty($userId)){
            return $this->messageRecord->createdBy == $this->userId;

        } else {
            return $this->messageRecord->createdBy == $userId;
        }
    }


    public function getAuthorId() {
        return $this->messageRecord->createdBy;
    }


    public function getFiles(){
        return $this->messageRecord->getFiles();
    }

}