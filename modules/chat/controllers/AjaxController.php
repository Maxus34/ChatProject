<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 29.01.2017
 * Time: 13:13
 */

namespace app\modules\chat\controllers;

use app\models\records\FileRecord;
use yii\web\Controller;
use app\models\User;
use app\modules\chat\models\{ Dialog, DialogProperties };
use app\modules\chat\components\DialogPropertiesForm\DialogPropertiesForm;
use yii\base\Exception;
use yii\filters\{ VerbFilter, AccessControl};
use yii\helpers\Json;
use yii\web\UploadedFile;

class AjaxController extends Controller
{

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

    public function actions(){
        return [
            'upload-file' => [
                'class' => \app\modules\chat\components\LoadFileAction::class
            ]
        ];
    }

    public function  actionIndex()
    {
        $this->layout = false;

        $json_string = \Yii::$app->request->post('json_string');
        $j_object = Json::decode($json_string);

        try {
            $dialog = Dialog::getInstance($j_object['dialog']['dialog-id']);
        } catch (Exception $e) {
            return $e->getMessage();
        }


        $response_arr = [];
        if (isset($j_object[static::LOAD_OLD_MESSAGES])) {
            $response_arr[static::LOAD_OLD_MESSAGES] = $this->loadOldMessages($dialog, $j_object);
        }

        if (isset($j_object[static::DELETE_MESSAGES])){
            $response_arr['deleted_messages'] = $this->deleteMessages($dialog, $j_object);
        }

        if (!$dialog->isActive())
            return Json::encode($response_arr);


        if (isset($j_object[static::LOAD_NEW_MESSAGES])) {
            $response_arr[static::LOAD_NEW_MESSAGES] = $this->loadNewMessages($dialog, $j_object);
        }
        if (isset($j_object[static::MESSAGES_FOR_SEND])){
            $response_arr[static::MESSAGES_FOR_SEND] = $this->sendMessages($dialog, $j_object);
        }

        if (isset($j_object[static::SEEN_MESSAGES])) {
            $response_arr[static::SEEN_MESSAGES] = $this->setSeenMessages($dialog, $j_object);
        }
        if (isset($j_object[static::CHECK_IS_SEEN])) {
           $response_arr[static::CHECK_IS_SEEN] = $this->checkIsSeenMessages($dialog, $j_object);
        }
        if (isset($j_object[static::CHECK_IS_TYPING])) {
            $response_arr['typing'] = $this->getTypingUsers($dialog, $j_object);
        }
        if (isset($j_object[static::SET_IS_TYPING])) {
            $this->setIsTyping($dialog, $j_object);
        }


        if (isset($j_object[static::DIALOG_PROPERTIES])){
            $response_arr['form'] = $this->getDialogPropertiesForm($dialog);
        }

        return Json::encode($response_arr);
    }

    public function  actionGetCreateDialogForm(){
        $d_p = new DialogProperties();

        return $this->renderAjax('/forms/_new_dialog_pr_form', [
            'create_new' => true,
            'model' => $d_p,
            'attribute' => 'users',
        ]);

    }


    protected function  getDialogPropertiesForm(Dialog $dialog){
        $model = $dialog->getProperties();
        return $this->renderAjax('/forms/_new_dialog_pr_form', [
            'create_new' => false,
            'model'      => $model,
            'attribute'  => 'users',
            'dialog'     => $dialog,
        ]);
    }


    protected function  loadOldMessages(Dialog $dialog, $j_object)
    {
        $first_message_id = $j_object['load_old_messages']['first_message-id'];
        $messages = $dialog->getMessages(-static::MESSAGES_PER_PAGE, null, [["<", "messageId", $first_message_id]]);

        return $this->renderMessages($messages);
    }


    protected function  loadNewMessages(Dialog $dialog, $j_object)
    {
        $last_message_id = $j_object['load_new_messages']['first_message-id'];
        $messages = $dialog->getMessages(null, null, [[">", "messageId", $last_message_id], ["!=", "createdBy", \Yii::$app->user->getId() ]]);

        return $this->renderMessages($messages);
    }


    protected function  sendMessages(Dialog $dialog, $j_object){
        $success = [];

        foreach ($j_object['messages_for_send'] as $item){
            $result = true;
            $error  = false;
            try {
                $message = $dialog->addMessage($item['text'], $item['files']);
            } catch (Exception $e) {
                $result = false;
                $error = $e -> getMessage();
            }

            $user_image = User::findIdentity($message->getAuthorId()) -> getMainImage() -> getUrl([100,100]);
            $success[] = [
                'pseudo_id' => $item['pseudo_id'],
                'message'   => $this->render('/templates/_message.php', ['message' => $message, 'user_image' => $user_image]),
                'success'   => $result,
                'error'     => $error
            ];
        }

        return $success;
    }


    protected function  getTypingUsers(Dialog $dialog, $j_object){
        return $dialog->getTypingUsers();
    }


    protected function  checkIsSeenMessages(Dialog $dialog, $j_object){
        if (empty($messages = $j_object[static::CHECK_IS_SEEN]['messages']))
            return [];

        return $dialog->getIsSeenMessages($messages);
    }



    protected function  setIsTyping(Dialog $dialog, $j_object){
        $dialog->setIsTyping($j_object['set_is_typing']['is_typing']);
    }



    protected function  setSeenMessages(Dialog $dialog, $j_object) {
        $messages = $j_object['seen_messages']['messages'];
        if (empty($messages))
            return [];

        return $dialog->setSeenMessages($messages);
    }



    protected function  deleteMessages(Dialog $dialog, $j_object){
         $messages = $j_object['delete_messages']['messages'];

         return $dialog->deleteMessages($messages);
    }


    protected function  renderMessages(array $messages) :array{
        $messages_arr = [];
        foreach ($messages as $message) {

            $user_image = User::findIdentity($message->getAuthorId()) -> getMainImage() -> getUrl([100,100]);
            $messages_arr[] = $this->render("/templates/_message.php", ['message' => $message, 'user_image' => $user_image]);
        }

        return $messages_arr;
    }
}