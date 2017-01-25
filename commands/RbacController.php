<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 15.12.2016
 * Time: 10:38
 */

namespace app\commands;
use Yii;
use yii\console\Controller;

class RbacController extends Controller
{
    public function actionInit(){
        $auth = Yii::$app->authManager;

        $moderator = $auth->getRole('moderator');

        $user = $auth->getRole('user');


        $auth->addChild($moderator, $user);

        return 'success';
    }
}