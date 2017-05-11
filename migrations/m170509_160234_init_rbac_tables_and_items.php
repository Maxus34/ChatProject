<?php

use yii\db\Migration;

include __DIR__ . '/../vendor/yiisoft/yii2/rbac/migrations/m140506_102106_rbac_init.php';

class m170509_160234_init_rbac_tables_and_items extends m140506_102106_rbac_init
{
    public function up()
    {
        parent::up();

        $this->addRbacRoles();
    }

    public function down()
    {
        parent::down();
    }

    protected function addRbacRoles(){
        $authManager = Yii::$app->authManager;

        $user   = $authManager->createRole('user');
        $moder  = $authManager->createRole('moder');
        $admin  = $authManager->createRole('admin');

        try{
            $authManager->add($admin);
            $authManager->add($moder);
            $authManager->add($user);

            $authManager->addChild($admin, $moder);
            $authManager->addChild($moder, $user);

        } catch (Exception $e){

            echo $e->getMessage();
            return;
        }

        $this->addAdminUser($admin);
    }

    protected function addAdminUser($admin_role){
        $authManager = \Yii::$app->authManager;

        $admin = new \app\models\User();

        $admin -> username   = 'admin';
        $admin -> password   = \Yii::$app->security->generatePasswordHash('admin');
        $admin -> email      = 'admin@some-mail.us';
        $admin -> isActive   =  1;
        $admin -> createdAt  =  time();

        $admin -> save();

        $authManager->assign($admin_role, $admin->getId() );
    }
}
