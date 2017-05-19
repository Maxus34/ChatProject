<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 29.01.2017
 * Time: 13:13
 */

namespace app\modules\chat\controllers;

use app\modules\chat\services\DialogRepository;
use yii\web\Controller;
use app\models\User;
use app\modules\chat\models\{ Dialog, DialogProperties };
use yii\base\Exception;
use yii\helpers\Json;
use app\modules\chat\actions\ { LoadFileAction, HandleJsonRequestAction };


class AjaxController extends Controller
{

    const MESSAGES_PER_PAGE = 10;

    protected $dialogRepository;



    public function actions(){
        return [
            'upload-file' => [
                'class' => LoadFileAction::class
            ],
            'json'  => [
                'class' => HandleJsonRequestAction::class,
            ]
        ];
    }


    public function  actionIndex()
    {

    }


    public function  actionGetCreateDialogForm(){
        $dialogProperties = new DialogProperties();

        return $this->renderAjax('/forms/new_dialog_pr_form', [
            'create_new' => true,
            'model' => $dialogProperties,
            'attribute' => 'users',
        ]);

    }

}