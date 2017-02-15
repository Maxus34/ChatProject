<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 15.12.2016
 * Time: 10:38
 */

namespace app\commands;
use Ratchet\Wamp\Exception;
use Yii;
use yii\console\Controller;

class RbacController extends Controller
{
    public function actionInit(){
        $authManager = Yii::$app->authManager;

        $guest  = $authManager->createRole('guest');
        $user   = $authManager->createRole('user');
        $moder  = $authManager->createRole('moder');
        $admin  = $authManager->createRole('admin');

        $login   = $authManager->createPermission('login');
        $logout  = $authManager->createPermission('logout');
        $sign_up = $authManager->createPermission('sign-up');

        try{
            $authManager->add($admin);
            $authManager->add($moder);
            $authManager->add($user);
            $authManager->add($guest);

            $authManager->add($login);
            $authManager->add($logout);
            $authManager->add($sign_up);

            $authManager->addChild($admin, $moder);
            $authManager->addChild($moder, $user);
            $authManager->addChild($user, $guest);

        } catch (Exception $e){
            echo $e->getMessage();
            return;
        }

        echo "Success\n";
    }
}