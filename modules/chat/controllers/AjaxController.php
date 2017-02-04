<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 29.01.2017
 * Time: 13:13
 */

namespace app\modules\chat\controllers;

use yii\web\Controller;
use app\modules\chat\models\Dialog;
use yii\base\Exception;
use yii\filters\{ VerbFilter, AccessControl};
use yii\helpers\Json;

class AjaxController extends Controller
{

    const MESSAGES_PER_PAGE = 10;

    public function  actionIndex()
    {
        $this->layout = false;

        $json_string = \Yii::$app->request->post('json_string');
        $j_object = Json::decode($json_string);
        unset($json_string);

        try {
            $dialog = Dialog::getDialogInstance($j_object['dialog']['dialog-id']);
        } catch (Exception $e) {
            return $e->getMessage();
        }

        $response_arr = [];
        if (isset($j_object['load_new_messages'])) {
            $response_arr['new_messages'] = $this->loadNewMessages($dialog, $j_object);
        }
        if (isset($j_object['load_old_messages'])) {
            $response_arr['old_messages'] = $this->loadOldMessages($dialog, $j_object);
        }
        if (isset($j_object['send_message'])) {
            $response_arr['message'] = $this->sendMessage($dialog, $j_object);
        }
        if (isset($j_object['seen_messages'])) {
            $response_arr['seen_messages'] = $this->setSeenMessages($dialog, $j_object);
        }

        if (isset($j_object['check_is_seen'])) {
           $response_arr['check_is_seen'] = $this->getIsSeenMessages($dialog, $j_object);
        }

        if (isset($j_object['check_is_typing'])) {
            $response_arr['typing'] = $this->getTypingUsers($dialog, $j_object);
        }
        if (isset($j_object['set_is_typing'])) {
            $this->setIsTyping($dialog, $j_object);
        }

        if (isset($j_object['dialog_properties'])){
            $response_arr['form'] = $this->getDialogPropertiesForm($dialog, $j_object);
        }

        return Json::encode($response_arr);
    }

    public function  actionForm(){
        $post = \Yii::$app->request->post();

        $dialog = Dialog::getDialogInstance($post['DialogProp']['id']);
        try{
            $dialog -> setDialogAttributes($post['DialogProp']);
        } catch (Exception $e){
            return $e->getMessage();
        }

        \Yii::$app->session->setFlash('success', "A Dialog properties were been changed");
        return $this->redirect(['default/view', 'id' => $dialog->id]);
    }



    private function  loadOldMessages(Dialog $dialog, $j_object)
    {
        $last_message_id = $j_object['load_old_messages']['first_message-id'];
        $messages = $dialog->getMessages(-static::MESSAGES_PER_PAGE, null, [["<", "message_id", $last_message_id]]);

        return $this->renderMessages($messages);
    }

    private function  loadNewMessages(Dialog $dialog, $j_object)
    {
        $last_message_id = $j_object['load_new_messages']['last_message_id'];
        $messages = $dialog->getMessages(null, null, [[">", "message_id", $last_message_id], ["!=", "created_by", \Yii::$app->user->getId() ]]);

        return $this->renderMessages($messages);
    }

    private function  sendMessage(Dialog $dialog, $j_object)
    {
        $content = $j_object ['send_message']['content'];
        $message = $dialog->addMessage($content);

        return $this->render('/templates/_message.php', ['message' => $message]);
    }

    private function  getTypingUsers(Dialog $dialog, $j_object){
        return $dialog->getTypingUsers();
    }

    private function  getIsSeenMessages(Dialog $dialog, $j_object){
        if (empty($messages = $j_object['check_is_seen']['check_is_seen']))
            return;

        return $dialog->getIsSeenMessages($messages);
    }

    private function  setIsTyping(Dialog $dialog, $j_object){
        $dialog->setIsTyping($j_object['set_is_typing']['is_typing']);
    }

    private function  setSeenMessages(Dialog $dialog, $j_object) {
        $messages = $j_object['seen_messages']['messages'];
        if (empty($messages))
            return;

        return $dialog->setSeenMessages($messages);
    }

    private function  getDialogPropertiesForm(Dialog $dialog, $j_object){
        return $this->renderAjax('_dialog_properties_form', compact('dialog'));
    }

    private function  renderMessages(array $messages) :array{
        $messages_arr = [];
        foreach ($messages as $message) {
            $messages_arr[] = $this->render("/templates/_message.php", ['message' => $message]);
        }

        return $messages_arr;
    }
}