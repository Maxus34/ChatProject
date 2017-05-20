<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 18.05.2017
 * Time: 11:43
 */

namespace app\modules\chat\services;


use app\modules\chat\models\{ DialogN, DialogProperties};
use app\modules\chat\records\DialogReferenceRecord;

class DialogHandler {

    protected $dialog = null;


    public function __construct(DialogN $dialog) {
        $this->dialog = $dialog;
    }


    public function getTypingUsers() :array{
        $excludeMe = true;
        $dialogReferences = $this->dialog->getReferences($excludeMe);
        $usersTyping = [];

        foreach ($dialogReferences as $reference){
            if ($reference -> isTyping) {
                if( (time() - $reference->updatedAt) > DialogN::MAX_TYPING_TIMEOUT){
                    $reference -> isTyping = 0;
                    $reference -> save();

                } else {
                    $usersTyping[] = $reference->user;
                }
            }
        }

        return $usersTyping;
    }


    public function setIsTypingForCurrentUser(bool $isTyping){
        $this->dialog->dialogReferences[$this->dialog->getUserId()] -> isTyping = $isTyping ? 1 : 0;
        $this->dialog->dialogReferences[$this->dialog->getUserId()] -> save();
    }


    public function getDialogProperties () {
        $model = new DialogProperties();
        $model->title = $this->dialog->getTitle();
        $model->users = $this->dialog->getUsers(true);

        return $model;
    }


    public function applyDialogProperties (DialogProperties $dialogProperties){

        if (!empty($dialogProperties->title)){
            $this->dialog->dialogRecord->title = $dialogProperties->title;
        }

        if (!empty($dialogProperties->users)){
            $this->updateDialogReferences($dialogProperties->users);
        }
    }




    protected function updateDialogReferences (array $usersToAddIds) {

        $referencesToDelete = $this->dialog->getReferences(true);

        if (count($usersToAddIds) > 0 && !empty($referencesToDelete)) {

            foreach ($referencesToDelete as   $refUserId  =>  $reference)  {
                foreach ($usersToAddIds  as   $key        =>  $userToAddId)  {

                    if ($refUserId == $userToAddId){
                        unset ($referencesToDelete[$refUserId]);
                        unset ($usersToAddIds[$key]);
                    }

                }
            }
        }



        $this->deactivateReferences($referencesToDelete);
        $this->createDialogReferences($usersToAddIds);
    }


    protected function createDialogReferences (array $userIds) {

        foreach ($userIds as $userId) {

            $reference = DialogReferenceRecord::find()->where([
                'dialogId' => $this->dialog->getId(),
                'userId'   => $userId,
                'isActive' => 0
            ])->one();

            if ( !empty($reference) ) {
                $reference->isActive = 1;
                $reference->save();

            } else {
                $reference = new DialogReferenceRecord(
                    $this->dialog->getId(),
                    $userId
                );
                $reference->save();
            }

            $this->dialog->dialogReferences[$reference->userId] = $reference;
        }
    }


    protected function deactivateReferences (array $dialogReferences){
        foreach ($dialogReferences as $reference) {
            if ( ($reference -> createdBy == $this->dialog->getUserId()) || $this->dialog->isCreator() ) {
                $reference->isActive = 0;
                $reference->save();
            }
        }
    }
}