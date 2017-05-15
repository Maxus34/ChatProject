<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 15.01.2017
 * Time: 14:29
 */

namespace app\modules\chat\controllers;


use app\modules\chat\models\{ Dialog, DialogProperties};
use app\modules\chat\services\ChatService;
use yii\base\Exception;
use yii\filters\{ AccessControl, VerbFilter };

class DefaultController extends \yii\web\Controller
{
    const DIALOGS_PER_PAGE = 10;
    const MESSAGES_PER_PAGE = 12;

    /**
     * @var ChatService
     */
    protected $chatService;

    public function init() {
        parent::init();

        $this->chatService = \Yii::$app->chatService;
    }


    public function  behaviors ()
    {
        return [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'create-dialog' => ['post'],
                    'set-dialog-properties' => ['post'],
                ],
            ],
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['user']
                    ]
                ]
            ]
        ];
    }


    public function  actionIndex (){
        $this->view->title = "Chat";

        $dialogs = $this->chatService->getDialogInstances(null, static::DIALOGS_PER_PAGE);

        return $this->render('index', compact('dialogs'));
    }


    public function  actionView ($id){
        try{
            $dialog = $this->chatService->getDialogInstance($id);

        } catch (Exception $e){
            \Yii::$app->session->setFlash('error', $e->getMessage());
            return $this->redirect('index');
        }

        if (!$dialog->isActive()){
            \Yii::$app->session->setFlash('warning', "You were been excluded from current dialog");
        }

        $this->view->title = "Dialog | " . $dialog->getTitle();
        $messages = $dialog->getMessages(-static::MESSAGES_PER_PAGE);
        return $this->render('view', compact('dialog', 'messages'));
    }


    public function  actionDeleteDialog($id){
        try{
            $dialog = $this->chatService->getDialogInstance($id);
        } catch (Exception $e){
            \Yii::$app->session->setFlash('error', $e->getMessage());
            return $this->redirect('index');
        }

        $this->chatService->deleteDialog($dialog);
        \Yii::$app->session->setFlash('success', "Dialog " . $dialog->getTitle() . " has been deleted.");
        return $this->redirect('index');
    }


    public function actionSetDialogProperties(){
        $dProperties = new DialogProperties();

        if ($dProperties->load(\Yii::$app->request->post())
            && $dProperties->validate() ){

            $dialog = $this->chatService->getDialogInstance($dProperties->id);
            $this->chatService->applyProperties($dialog, $dProperties);
            $this->chatService->saveDialog($dialog);
        }

        return $this->redirect(['view', 'id'=> $dProperties->id]);
    }


    public function actionCreateDialog () {
        $model = new DialogProperties();
        $post = \Yii::$app->request->post();

        if ($model -> load($post)){
            if ($model -> validate()){
                $dialog = $this->chatService->createNewDialog($model);
                $this->chatService->saveDialog($dialog);

                return $this->redirect(['default/view', 'id' => $dialog->getId()]);
            } else {
                \Yii::$app->session->setFlash('error', "Errors: " . $model->getErrors());
            }
        }

        return $this->redirect(['default/index']);
    }


    public function actionTest () {
        $dialogs = $this->chatService->getDialogInstances();

        debug($dialogs);
        die();
    }

}