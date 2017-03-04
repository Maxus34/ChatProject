<?php


class DialogTest extends \Codeception\Test\Unit
{
    /**
     * @var \UnitTester
     */
    protected $tester;

    protected function _before()
    {
        parent::_before();
        $user = app\models\User::findByUsername('admin');
        \Yii::$app->user->login($user);
    }

    protected function _after()
    {
        parent::_after();
        \Yii::$app->user->logout();
    }


    public function testMe()
    {
        $dialog_properties = $this->createDialogProperties();
        $dialog = new \app\modules\chat\models\Dialog(null, $dialog_properties);

    }


    protected function createDialogProperties() {
        $dp = new \app\modules\chat\models\DialogProperties();
        $dp -> title = "Dialog" . time();

        $user_1 = app\models\User::findByUsername('user_1');

        $dp -> users[] = $user_1;

        return $dp;
    }
}