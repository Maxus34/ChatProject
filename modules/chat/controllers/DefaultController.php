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
            'verb' => [
                'class' => VerbFilter::className(),
                'actions' => [

                ]
            ]
        ];
    }

    public function  actionIndex (){
        $dialogs = Dialog::getDialogInstances(null, static::DIALOGS_PER_PAGE);
        $dataProvider = $this->wrapIntoDataProvider($dialogs);
        return $this->render('index', compact('dataProvider'));
    }

    public function  actionView ($id){
        $dialog = Dialog::getDialogInstance($id);
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

        }

        $messages = $dialog->getOldMessages($last_message_id, static::MESSAGES_PER_PAGE);

        $messages_html = "";
        foreach ($messages as $message) {
            ob_start();
            include(__DIR__ . '/../views/default/' . '_message.php');
            $messages_html .= ob_get_clean();
        }


        return $messages_html;
    }


    public function actionLoadNewMessages($last_message_id){

    }

    private function  wrapIntoDataProvider ($data){
        return new ArrayDataProvider([
            'allModels' => $data,
            'pagination' => false,
        ]);
    }
}