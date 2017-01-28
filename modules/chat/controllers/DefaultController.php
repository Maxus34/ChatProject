<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 15.01.2017
 * Time: 14:29
 */

namespace app\modules\chat\controllers;


use app\modules\chat\models\Dialog;
use yii\base\Exception;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\helpers\Json;

class DefaultController extends \yii\web\Controller
{
    const DIALOGS_PER_PAGE = 10;
    const MESSAGES_PER_PAGE = 10;

    public function  behaviors ()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['user'],
                    ],
                ]
            ],
        ];
    }

    public function  actionIndex (){
        $dialogs = Dialog::getDialogInstances(null, static::DIALOGS_PER_PAGE);
        $dataProvider = $this->wrapIntoDataProvider($dialogs);
        return $this->render('index', compact('dataProvider'));
    }

    public function  actionView ($id){
        try{
            $dialog = Dialog::getDialogInstance($id);
        } catch (Exception $e){
            \Yii::$app->session->setFlash('error', "You doesn't belong to this dialog or dialog does not exists");
            return $this->redirect('index');
        }

        $messages = $dialog->getMessages(-10, static::MESSAGES_PER_PAGE);
        return $this->render('dialog', compact('dialog', 'messages'));
    }

    public function  actionAjax (){
        $this->layout = false;

        $json_string = \Yii::$app->request->post('json_string');
        $j_object = Json::decode($json_string);
        $response_arr = [];

        if (isset($j_object['load_new_messages'])){
            $response_arr['new_messages'] = $this->loadNewMessagesAjax($j_object);
        }
        if (isset($j_object['load_old_messages'])){
            $response_arr['old_messages'] = $this->loadOldMessagesAjax($j_object);
        }
        if (isset($j_object['send_message'])){
            $response_arr['message'] = $this->sendMessage($j_object);
        }
        if (isset($j_object['check_is_typing'])){
            $response_arr['typing'] = $this->getTypingUsers($j_object);
        }
        if (isset($j_object['set_typing'])){
            $this->setTyping($j_object);
        }


        return Json::encode($response_arr);
    }


    private function  loadOldMessagesAjax ($j_object){
        $dialog_id        =  $j_object['load_old_messages']['dialog-id'];
        $last_message_id  =  $j_object['load_old_messages']['first_message-id'];

        try {
            $dialog = Dialog::getDialogInstance($dialog_id);

        } catch (Exception $e) {
            return $e.getMessage();
        }

        $messages = $dialog->getMessages(-static::MESSAGES_PER_PAGE, null, [["<", "message_id", $last_message_id]]);

        $messages_html = "";
        foreach ($messages as $message) {
            $messages_html .= $this->render('_message', ['message' => $message]);
        }

        return $messages_html;
    }

    private function  sendMessage         ($j_object){
        $dialog_id  = $j_object['send_message']['dialog-id'];
        $content    = $j_object['send_message']['content'];

        try{
            $dialog = Dialog::getDialogInstance($dialog_id);

        } catch (Exception $e) {
            return debug($e);
        }

        $message = $dialog -> addMessage($content);

        return $this->render('_message', ['message' => $message]);
    }

    private function  loadNewMessagesAjax ($j_object){
        $dialog_id       = $j_object['load_new_messages']['dialog-id'];
        $last_message_id = $j_object['load_new_messages']['last_message_id'];
        try{
            $dialog = Dialog::getDialogInstance($dialog_id);

        } catch (Exception $e) {
            return debug($e);
        }

        $messages = $dialog->getMessages(null, null, [[">", "message_id", $last_message_id], ["=", "is_author", 0]]);

        $messages_arr = [];
        foreach ($messages as $message){
            $messages_arr[] = $this->render("_message", ['message' => $message]);
        }

        return $messages_arr;
    }

    private function  getTypingUsers      ($j_object){
        // TODO Create method
        $dialog_id = $j_object['check_is_typing']['dialog-id'];
        try{
            $dialog = Dialog::getDialogInstance($dialog_id);

        } catch (Exception $e) {
            return debug($e);
        }

        return $dialog->getTypingUsers();
    }

    private function  setTyping           ($j_object){
        $dialog_id = $j_object['set_typing']['dialog-id'];
        try{
            $dialog = Dialog::getDialogInstance($dialog_id);

        } catch (Exception $e) {
            return debug($e);
        }

        $dialog->setIsTyping($j_object['set_typing']['is_typing']);
    }

    private function  wrapIntoDataProvider ($data){
        return new ArrayDataProvider([
            'allModels' => $data,
            'pagination' => false,
        ]);
    }
}