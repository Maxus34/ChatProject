<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 15.01.2017
 * Time: 14:29
 */

namespace app\modules\chat\controllers;


use app\modules\chat\models\Dialog;
use app\modules\chat\models\DialogProperties;
use yii\base\Exception;
use yii\data\ArrayDataProvider;
use yii\filters\AccessControl;
use yii\helpers\Json;

class DefaultController extends \yii\web\Controller
{
    const DIALOGS_PER_PAGE = 10;
    const MESSAGES_PER_PAGE = 12;

    public function  behaviors ()
    {
        return parent::behaviors();
    }

    public function  actionIndex (){
        $this->view->title = "Chat";
        $dialogs = Dialog::getInstances(null, static::DIALOGS_PER_PAGE, null);
        return $this->render('index', compact('dialogs'));
    }

    public function  actionView ($id){
        try{
            $dialog = Dialog::getInstance($id);
        } catch (Exception $e){
            \Yii::$app->session->setFlash('error', $e->getMessage());
            return $this->redirect('index');
        }

        $this->view->title = "Dialog | " . $dialog->getTitle();
        $messages = $dialog->getMessages(-static::MESSAGES_PER_PAGE, null);
        return $this->render('view', compact('dialog', 'messages'));
    }

    public function  actionSetProperties(){
        $post = \Yii::$app->request->post();
        $model = new DialogProperties();
        $dialog = Dialog::getInstance($post['DialogProp']['id']);

        if ($model -> load($post)){
            if ($model -> validate()){

                $dialog -> applyProperties($model);

                \Yii::$app->session->setFlash('success', "A Dialog properties were been changed");
                return $this->redirect(['default/view', 'id' => $dialog->id]);

            } else {
                \Yii::$app->session->setFlash('error', "A Dialog properties were been changed");

            }
        }

        return $this->redirect(['default/view', 'id' => $dialog->id]);
    }

}