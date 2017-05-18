<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 18.05.2017
 * Time: 11:43
 */

namespace app\modules\chat\services;


use app\modules\chat\models\DialogN;

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

}