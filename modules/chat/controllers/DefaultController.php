<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 15.01.2017
 * Time: 14:29
 */

namespace app\modules\chat\controllers;


use app\modules\chat\models\{ Dialog, DialogProperties};
use app\modules\chat\services\{
    DialogRepository, DialogFactory, DialogHandler, DialogMessagesHandler,
    MessageRepository, MessageFactory} ;
use yii\base\Exception;
use yii\filters\{ AccessControl, VerbFilter };

class DefaultController extends \yii\web\Controller
{
    const  DIALOGS_PER_PAGE   = 10;
    const  MESSAGES_PER_PAGE  = 12;

    /** @var DialogRepository */
    protected $dialogRepository;

    /** @var DialogFactory */
    protected $dialogFactory;

    public function init() {
        parent::init();

        $this->dialogRepository = DialogRepository::getInstance();
        $this->dialogFactory    = DialogFactory::getInstance();
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

        $dialogs = $this->dialogRepository -> findDialogsByConditions(null, static::DIALOGS_PER_PAGE);

        return $this->render('index', compact('dialogs'));
    }


    public function  actionView ($id){
        try{
            $dialog = $this->dialogRepository->findDialogById($id);

        } catch (Exception $e){
            \Yii::$app->session->setFlash('error', $e->getMessage());
            return $this->redirect('default');
        }

        if (!$dialog->isActive()){
            \Yii::$app->session->setFlash('warning', "You were been excluded from current dialog");
        }

        $this->view->title = "Chat | " . $dialog->getTitle();


        $messages = $dialog->messageRepository -> findMessagesByConditions(-static::MESSAGES_PER_PAGE);


        return $this->render('view', compact('dialog', 'messages'));
    }


    public function  actionDeleteDialog($id){
        try{
            $dialog = $this->dialogRepository->findDialogById($id);
        } catch (Exception $e){
            \Yii::$app->session->setFlash('error', $e->getMessage());
            return $this->redirect('index');
        }

        $this->dialogRepository->deleteDialog($dialog);
        \Yii::$app->session->setFlash('success', "Dialog " . $dialog->getTitle() . " has been deleted.");
        return $this->redirect('index');
    }


    public function actionSetDialogProperties(){
        $dProperties = new DialogProperties();

        if ($dProperties->load(\Yii::$app->request->post())
            && $dProperties->validate() ){

            $dialog = $this->dialogRepository->findDialogById($dProperties->id);
            $dialog->dialogHandler->applyDialogProperties($dProperties);
            $this->dialogRepository->saveDialog($dialog);
        }

        return $this->redirect(['view', 'id'=> $dProperties->id]);
    }


    public function actionCreateDialog () {
        $model = new DialogProperties();
        $post = \Yii::$app->request->post();

        if ($model -> load($post)){
            if ($model -> validate()){

                $dialog = $this -> dialogFactory -> createNewDialog();
                $dialog -> dialogHandler    -> applyDialogProperties($model);
                $this->dialogRepository->saveDialog($dialog);

                return $this->redirect(['default/view', 'id' => $dialog->getId()]);
            } else {
                \Yii::$app->session->setFlash('error', "Errors: " . $model->getErrors());
            }
        }

        return $this->redirect(['default/index']);
    }


    public function actionTest(){
        $dialog = $this->dialogRepository->findDialogById(4);
        $message = $dialog->messageRepository->findById(41);

        debug($message->getFiles());
    }

}









