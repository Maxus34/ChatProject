<?php

namespace app\modules\admin;
use yii\filters\AccessControl;
use yii\web\NotFoundHttpException;


class Module extends \yii\base\Module
{

    //public $controllerNamespace = 'app\modules\admin\controllers';
    public $layout = "app_layout";

    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['admin', 'moderator'],
                    ],
                ],
            ],
        ];
    }

    public function init()
    {
        parent::init();

        $this->defaultRoute = "user/";
    }
}
