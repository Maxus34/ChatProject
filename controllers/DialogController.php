<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 17.12.2016
 * Time: 12:39
 */

namespace app\controllers;

use app\models\{
    DialogUser, Dialog, Message
};
use yii\filters\{VerbFilter, AccessControl};
use yii\web\{
    Controller, HttpException, NotAcceptableHttpException, NotFoundHttpException
};
use Yii;

class DialogController extends Controller
{
    const MESSAGES_PER_PAGE = 10s;

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
                ],
            ],
            'verb' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'sendMessage' => ['post'],
                    'loadMoreMessages' => ['post'],
                    'loadNewMessages' => ['post'],
                ]
            ]
        ];
    }

    public function actionIndex(){
        $dialogs = DialogUser::find()->where(['user_id' => Yii::$app->user->getId()])->with('dialog')->all();
        return $this->render('index', compact('user', 'dialogs'));
    }

    public function actionCreate(){
        $dialog = new Dialog();
        $dialog->title = "dialog";
        $dialog->creator_id = \Yii::$app->user->identity->getId();
        $dialog->save();

        $dialog_user = new DialogUser();
        $dialog_user->dialog_id = $dialog->id;
        $dialog_user->user_id = \Yii::$app->user->identity->getId();
        $dialog_user->save();

        return $this->redirect(['index']);
    }

    public function actionView($id){
        if ( empty(DialogUser::find()->where(['dialog_id' => $id, 'user_id' => \Yii::$app->user->getId()])->one()) ) {
            \Yii::$app->session->setFlash('error', "You doesn't belong to this dialog or dialog does not exists");
            return $this->redirect(['index']);
        }

        $dialog = Dialog::findOne($id);

        return $this->render('view', [
            'dialog' => $dialog,
            'messages' => $dialog->getLastMessages(self::MESSAGES_PER_PAGE),
        ]);
    }

    public function actionSendMessage(){
        $model = new Message();
        $model->user_id   = Yii::$app->request->post('user_id');
        $model->dialog_id = Yii::$app->request->post('dialog_id');
        $model->content   = Yii::$app->request->post('content');
        $model->is_new    = 1;
        if ($model->validate()){
            $model->save();
            $this->layout = false;
            return $this->render('message_template', ['message' => $model]);
        } else {
            return "<div class='message message-error'><p>Message cannot be empty</p></div>";
        }
    }

    public function actionLoadMoreMessages(){
        $dialog_id = Yii::$app->request->post('dialog_id');
        if ( empty(DialogUser::find()->where(['dialog_id' => $dialog_id, 'user_id' => \Yii::$app->user->getId()])->one()) ) {
            return new HttpException(403, "You not belong to this dialog");
        }

        $dialog = Dialog::findOne($dialog_id);
        $this->layout = false;

        if (!empty($dialog)){
            $messages = $dialog->getMessagesUntilDate(self::MESSAGES_PER_PAGE, Yii::$app->request->post('creation_date'));
            if (!empty($messages)){
                return $this->render('messages_template', ['messages' => $messages]);
            } else {
                return "<div class='message message-error'><p>no more messages for this dialog</p></div>";
            }
        }
    }

    public function actionLoadNewMessages(){
        $dialog_id = Yii::$app->request->post('dialog_id');
        if ( empty(DialogUser::find()->where(['dialog_id' => $dialog_id, 'user_id' => \Yii::$app->user->getId()])->one()) ) {
            return new HttpException(403, "You not belong to this dialog");
        }
        $dialog = Dialog::findOne($dialog_id);
        $this->layout = false;

        if (!empty($dialog)){
            $messages = $dialog->getMessagesAfterDate(Yii::$app->request->post('creation_date'));
            if (!empty($messages)){
                return $this->render('messages_template', ['messages' => $messages]);
            } else {
                return "empty";
            }
        }
    }
}


