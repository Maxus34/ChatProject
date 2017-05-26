<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 18.05.2017
 * Time: 14:56
 */

namespace app\modules\chat\actions;

use app\models\User;
use app\modules\chat\models\DialogN;
use app\modules\chat\services\DialogRepository;
use yii\base\{ Action, Exception };
use yii\helpers\Json;
use yii\web\HttpException;

class HandleJsonRequestAction extends Action {

    const MESSAGES_PER_PAGE = 10;

    const LOAD_NEW_MESSAGES = "load_new_messages";
    const LOAD_OLD_MESSAGES = "load_old_messages";
    const MESSAGES_FOR_SEND = "messages_for_send";

    const SEEN_MESSAGES     = "seen_messages";
    const DELETE_MESSAGES   = "delete_messages";
    const CHECK_IS_SEEN     = "check_is_seen";
    const CHECK_IS_TYPING   = "check_is_typing";
    const SET_IS_TYPING     = "set_is_typing";
    const DIALOG_PROPERTIES = "dialog_properties";


    /** @var DialogRepository */
    protected $dialogRepository;

    public function init() {
        parent::init();

        $this->dialogRepository = DialogRepository::getInstance();
    }


    public function run(){

        $requestArray = Json::decode(\Yii::$app->request->post('json_string'));

        try{

            $dialog = $this->dialogRepository->findDialogById($requestArray['dialog']['dialog-id']);

        } catch (Exception $e){
            return $e->getMessage();
        }

        $responseArray = $this->processRequest($dialog, $requestArray);

        return Json::encode($responseArray);
    }



    protected function  processRequest(DialogN $dialog, $requestArray){
        $responseArray = [];

        if (isset($requestArray[static::LOAD_OLD_MESSAGES])) {
            $responseArray[static::LOAD_OLD_MESSAGES] = $this->loadOldMessages($dialog, $requestArray);
        }

        if (isset($requestArray[static::DELETE_MESSAGES])){
            $responseArray['deleted_messages'] = $this->deleteMessages($dialog, $requestArray);
        }


        if (!$dialog->isActive())
            return Json::encode($responseArray);

        if (isset($requestArray[static::LOAD_NEW_MESSAGES])) {
            $responseArray[static::LOAD_NEW_MESSAGES] = $this->loadNewMessages($dialog, $requestArray);
        }
        if (isset($requestArray[static::MESSAGES_FOR_SEND])){
            $responseArray[static::MESSAGES_FOR_SEND] = $this->sendMessages($dialog, $requestArray);
        }

        if (isset($requestArray[static::SEEN_MESSAGES])) {
            $responseArray[static::SEEN_MESSAGES] = $this->setSeenMessages($dialog, $requestArray);
        }
        if (isset($requestArray[static::CHECK_IS_SEEN])) {
            $responseArray[static::CHECK_IS_SEEN] = $this->checkIsSeenMessages($dialog, $requestArray);
        }
        if (isset($requestArray[static::CHECK_IS_TYPING])) {
            $responseArray['typing'] = $this->getNamesOfTypingUsers($dialog, $requestArray);
        }
        if (isset($requestArray[static::SET_IS_TYPING])) {
            $this->setIsTyping($dialog, $requestArray);
        }

        if (isset($requestArray[static::DIALOG_PROPERTIES])){
            $responseArray['form'] = $this->getDialogPropertiesForm($dialog);
        }

        return $responseArray;
    }


    protected function  loadOldMessages(DialogN $dialog, $requestArray)
    {
        if (empty($requestArray['load_old_messages']))
            return;

        $first_message_id = $requestArray['load_old_messages']['first_message-id'];

        $messages = $dialog->messageRepository
            ->findMessagesByConditions(-static::MESSAGES_PER_PAGE, null, [
                ["<", "messageId", $first_message_id]
            ]);


        return $this->renderMessages($messages);
    }


    protected function  loadNewMessages(DialogN $dialog, $requestArray)
    {
        $lastMessageId = $requestArray['load_new_messages']['first_message-id'];

        $messages = $dialog->messageRepository
        ->findMessagesByConditions(null, null, [
            "`message_ref`.`messageId` > {$lastMessageId}",     //[">", "messageId", $lastMessageId],
            "`message`.`createdBy` != {$dialog->getUserId()}"   //["!=", "createdBy", $dialog->getUserId() ]
        ]);

        return $this->renderMessages($messages);
    }


    protected function  sendMessages(DialogN $dialog, $requestArray){
        $success = [];

        foreach ($requestArray['messages_for_send'] as $item){
            $result = true;
            $error  = false;

            $message = null;

            try {

                $message = $dialog->messageHandler
                    ->addMessageToTheDialog($item['text'], $item['files']);

            } catch (Exception $e) {
                $result = false;
                $error = $e -> getMessage();

                return $e -> getMessage();
            }

            $user_image = User::findIdentity( $message->getAuthorId() ) -> getMainImage() -> getUrl([100,100]);
            $success[] = [
                'pseudo_id' => $item['pseudo_id'],
                'message'   => \Yii::$app->view->render('@chat/views/templates/_message.php', ['message' => $message, 'user_image' => $user_image]),
                'success'   => $result,
                'error'     => $error
            ];
        }

        return $success;
    }


    protected function  getNamesOfTypingUsers(DialogN $dialog, $requestArray){
        $userNames = [];
        $users = $dialog->dialogHandler->getTypingUsers();

        foreach ($users as $user){
            $userNames[] = $user->username;
        }

        return $userNames;
    }


    protected function  checkIsSeenMessages(DialogN $dialog, $requestArray){

        $messageIds = $requestArray[static::CHECK_IS_SEEN];

        if (empty($messageIds))
            return [];

        return $dialog->messageHandler->getIsSeenMessages($messageIds);
    }


    protected function  setIsTyping(DialogN $dialog, $requestArray){
        $dialog->dialogHandler->setIsTypingForCurrentUser($requestArray['set_is_typing']['is_typing']);
    }


    protected function  setSeenMessages(DialogN $dialog, $requestArray) {
        $messageIds = $requestArray['seen_messages'];
        if (empty($messageIds))
            return [];

        return $dialog->messageHandler->setMessagesThatHasBeenSeen($messageIds);
    }


    protected function  deleteMessages(DialogN $dialog, $requestArray){
        $messageIds = $requestArray['delete_messages'];

        return $dialog->messageRepository->deleteMessages($messageIds);
    }


    protected function  renderMessages(array $messages) :array{
        $messages_arr = [];
        foreach ($messages as $message) {

            $user_image = User::findIdentity($message->getAuthorId()) -> getMainImage() -> getUrl([100,100]);
            $messages_arr[] = \Yii::$app->view->render("@chat/views/templates/_message.php", ['message' => $message, 'user_image' => $user_image]);
        }

        return $messages_arr;
    }


    protected function  getDialogPropertiesForm(DialogN $dialog){
        $model = $dialog->dialogHandler->getDialogProperties();

        return \Yii::$app->view->renderAjax('@chat/views/forms/new_dialog_pr_form', [
            'create_new' => false,
            'model'      => $model,
            'attribute'  => 'users',
            'dialog'     => $dialog,
        ]);
    }

}