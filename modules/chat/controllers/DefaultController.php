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
use yii\helpers\Json;

class DefaultController extends \yii\web\Controller
{
    const DIALOGS_PER_PAGE = 10;
    const MESSAGES_PER_PAGE = 7;

    public function  behaviors ()
    {
        return parent::behaviors();
    }

    public function  actionIndex (){
        $this->view->title = "Chat";
        $dialogs = Dialog::getDialogInstances(null, static::DIALOGS_PER_PAGE);
        $dataProvider = $this->wrapIntoDataProvider($dialogs);
        return $this->render('index', compact('dataProvider'));
    }

    public function  actionView ($id){
        try{
            $dialog = Dialog::getDialogInstance($id);
        } catch (Exception $e){
            \Yii::$app->session->setFlash('error', $e->getMessage());
            return $this->redirect('index');
        }

        $this->view->title = "Dialog | " . $dialog->getTitle();
        $messages = $dialog->getMessages(-static::MESSAGES_PER_PAGE, null);
        return $this->render('dialog', compact('dialog', 'messages'));
    }


    private function  wrapIntoDataProvider ($data){
        return new ArrayDataProvider([
            'allModels' => $data,
            'pagination' => false,
        ]);
    }
}