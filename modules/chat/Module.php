<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 15.01.2017
 * Time: 14:27
 */

namespace app\modules\chat;
use yii\filters\AccessControl;

class Module extends \yii\base\Module
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function init()
    {
        parent::init();
    }
}