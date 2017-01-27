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

    public function behaviors()
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

    public function actionSendMessage(){
        $dialog_id = \Yii::$app->request->post('dialog_id');
        $content = \Yii::$app->request->post('content');

        $dialog = Dialog::getDialogInstance($dialog_id);
        $message = $dialog -> addMessage($content);

        $this->layout = false;
        return $this->render('_message', ['message' => $message]);
    }

    public function actionLoadOldMessages()
    {
        $dialog_id = \Yii::$app->request->post('dialog_id');
        $last_message_id = \Yii::$app->request->post('last_message_id');

        try {
            $dialog = Dialog::getDialogInstance($dialog_id);
        } catch (Exception $e) {
            return "error";
        }

        $messages = $dialog->getMessages(-static::MESSAGES_PER_PAGE, null, [["<", "message_id", $last_message_id]]);

        if(empty($messages)){
            return "no_more";
        }

        $messages_html = "";
        foreach ($messages as $message) {
            ob_start();
            include(__DIR__ . '/../views/default/' . '_message.php');
            $messages_html .= ob_get_clean();
        }

        return $messages_html;
    }

    public function actionLoadNewMessages(){
        $dialog_id = \Yii::$app->request->post('dialog_id');
        $last_message_id = \Yii::$app->request->post('last_message_id');

        try {
            $dialog = Dialog::getDialogInstance($dialog_id);
        } catch (Exception $e) {
            return "error";
        }

        $messages = $dialog->getMessages(null, null, [[">", "message_id", $last_message_id], ["=", "is_author", 0]]);
        //$messages = $dialog->getMessages(null, null, [[">", "message_id", $last_message_id]]);

        if(empty($messages)){
            return "empty";
        }

        $messages_html = "";
        foreach ($messages as $message) {
            ob_start();
            include(__DIR__ . '/../views/default/' . '_message.php');
            $messages_html .= ob_get_clean();
        }

        return $messages_html;
    }

    public function actionAjax(){
        $json_string = \Yii::$app->request->post('json_string');
        $j_object = Json::decode($json_string);
        $request_arr = [];

        if (isset($j_object['load_old_messages'])){
            $request_arr['old_messages'] = $this->loadOldMessagesAjax($j_object);
        }

        return Json::encode($request_arr);
    }

    private function loadOldMessagesAjax($j_object){
        $dialog_id =  $j_object['load_old_messages']['dialog-id'];
        $last_message_id =  $j_object['load_old_messages']['first_message-id'];

        try {
            $dialog = Dialog::getDialogInstance($dialog_id);
        } catch (Exception $e) {
            return "error";
        }

        $messages = $dialog->getMessages(-static::MESSAGES_PER_PAGE, null, [["<", "message_id", $last_message_id]]);

        $messages_html = "";
        foreach ($messages as $message) {
            ob_start();
            include(__DIR__ . '/../views/default/' . '_message.php');
            $messages_html .= ob_get_clean();
        }

        return $messages_html;
    }

    private function  wrapIntoDataProvider ($data){
        return new ArrayDataProvider([
            'allModels' => $data,
            'pagination' => false,
        ]);
    }
}