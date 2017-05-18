<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 12.05.2017
 * Time: 11:07
 */

namespace app\modules\chat\services;

use app\modules\chat\models\{
    Dialog, DialogBase, DialogN, DialogProperties, Message
};
use app\modules\chat\records\ { DialogRecord, DialogReferenceRecord, MessageRecord, MessageReferenceRecord };
use yii\base\ {Component, Exception };


class ChatService extends Component{

    public $userId;


    public function __construct() {
        parent:: __construct();

        $this->userId = \Yii::$app->user->getId();
    }

    /**
     * @param int $id
     * @return DialogN
     * @throws Exception
     */
    public function getDialogInstance ($id){
        $dialogRecord = DialogRecord::findOne($id);

        if (empty($dialogRecord))
            throw new Exception("Dialog does not exists");


        $dialogReferences = $this->findDialogReferences($dialogRecord);
        if ( empty( $dialogReferences[ $this->userId ] ) ){
            throw new Exception('You don`t belong to this chat');
        }

        return new DialogN($dialogRecord, $dialogReferences);
    }

    /**
     * @param int $offset
     * @param null $limit
     * @param null $condition
     * @return array
     */
    public function getDialogInstances ($offset=null, $limit=null, $condition=null){
        $query = DialogRecord::find()
            ->innerJoin('dialog_ref', '`dialog`.`id` = `dialog_ref`.`dialogId`')
            ->where("`dialog_ref`.`userId` = {$this->userId}");

        if (!empty($offset) && ($offset < 0))
            $offset += $query->count();

        if (!empty($offset))
            $query = $query->offset($offset);
        if (!empty($limit))
            $query = $query->limit($limit);
        if (!empty($condition))
            $query = $query->andWhere($condition);

        $dialogRecords = $query -> all();

        $dialogs = [];
        foreach ($dialogRecords as $dRecord){
            $dReferences = $this->findDialogReferences($dRecord);

            $dialogs[] = new DialogN($dRecord, $dReferences);
        }

        return $dialogs;
    }


    public function createNewDialog (DialogProperties $properties){
        $dRecord = new DialogRecord($properties->title);
        $dRecord -> save();

        $dReference = new DialogReferenceRecord($dRecord -> id, $this -> userId);

        $dialog = new DialogN($dRecord, [$this->userId => $dReference,]);

        $this->updateDialogReferences($dialog, $properties -> users);

        return $dialog;
    }


    public function applyProperties (DialogN $dialog, DialogProperties $properties){
        $dialog->setTitle($properties->title);
        $this->updateDialogReferences($dialog, $properties->users);

        $this->saveDialog ($dialog);
    }


    public function getProperties (DialogN $dialog) {
        $model = new DialogProperties();
        $model -> title = $dialog -> getTitle();
        $model -> users = $dialog -> getUsers(true);

        return $model;
    }


    public function getTypingUsers (DialogN $dialog) {
        $references = $dialog->getReferences(true);

        $users_array = [];

        foreach ($references as $reference){
            if ($reference->isTyping){
                if ( time() - $reference->updatedAt > Dialog::MAX_TYPING_TIMEOUT){
                    $reference -> isTyping = 0;
                    $reference -> save();

                } else {
                    $users_array[] = $reference->user;
                }
            }
        }

        return $users_array;
    }




    public function saveDialog (DialogN $dialog) {
        $dialog->dialogRecord->save();
        foreach ($dialog->dialogReferences as $reference){
            $reference->save();
        }
    }

    public function deleteDialog (DialogN $dialog) {
        $references = $dialog->getReferences();

        // Delete only for current user
        if (count($references) > 1){
            $dialog -> dialogReferences[$this->userId] -> delete();

            $message_references = MessageReferenceRecord::findAll(['dialogId' => $dialog->getId(), 'userId' => $this->getUserId()]);
            foreach ($message_references as $reference) {
                $reference->delete();
            }

        // Delete all dialog
        } else {
            $messages = MessageRecord::findAll(['dialogId' => $dialog->getId()]);

            $dialog -> dialogReferences[$this->userId]->delete();
            $dialog -> dialogRecord->delete();

            foreach ($messages as $message) {
                $message->delete();
            }
        }
    }



    protected function findDialogReferences (DialogRecord $dRecord) {
        $references = DialogReferenceRecord::findAll(['dialogId' => $dRecord->id]);

        $refArr = [];

        foreach ($references as $ref){
            $refArr[$ref->userId] = $ref;
        }

        return $refArr;
    }


    protected function updateDialogReferences (DialogN $dialog, array $usersPersist) {
        $delete = $dialog->getReferences(true);

        if (count($usersPersist) > 0 && !empty($delete)) {
            foreach ($delete as  $dkey => $value) {
                foreach ($usersPersist as  $akey => $add_item) {
                    if ($dkey == $add_item) {
                        unset($delete[$dkey]);
                        unset($usersPersist[$akey]);
                    }
                }
            }
        }


        if (!empty($delete)) {
            $this->deactivateReferences($delete);
        }


        $add_users = [];
        foreach ($usersPersist as $id) {
            $add_users[] = \Yii::$app->user->identity->findIdentity($id);
        }

        $this->createDialogReferences($dialog, $add_users);
    }


    protected function createDialogReferences (DialogN $dialog, array $users) {
        foreach ($users as $user) {

            $reference = DialogReferenceRecord::find()->where([
                'dialogId' => $dialog->getId(),
                'userId' => $user->id,
                'isActive' => 0
            ])->one();


            if (!empty($reference)) {
                $reference->isActive = 1;
                $reference->save();

            } else {
                $reference = new DialogReferenceRecord(
                    $dialog->getId(),
                    $user->id
                );


                $reference->save();
            }

            $dialog->dialogReferences[$reference->userId] = $reference;
        }
    }


    protected function deactivateReferences (DialogN $dialog, array $references){
        foreach ($references as $ref) {

            if ( $ref->createdBy == $this->getUserId() || $dialog->isCreator() ) {
                $ref->isActive = 0;
                $ref->save();
            }
        }
    }
}