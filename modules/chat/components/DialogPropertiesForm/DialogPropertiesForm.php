<?php
namespace app\modules\chat\components\DialogPropertiesForm;

use app\modules\chat\models\DialogProperties;
use yii\base\Widget;
use app\models\User;
use Yii;

class DialogPropertiesForm extends Widget
{
    public $create_new  = false;

    /**
     * @var DialogProperties
     */
    public $model       = null;

    /** @var string */
    public $attribute   = null;

    /**
     * @var \app\modules\chat\models\DialogN
     */
    public $dialog      = null;

    public $references      = [];
    public $available_users = [];

    public function run()
    {
        $this->getAvailableUsers();
        $this->getCurrentReferences();

        return $this->render('@chat/components/DialogPropertiesForm/templates/widget_tpl.php',
            [
                'create_new'         => $this->create_new,
                'current_references' => $this->references,
                'available_users'    => $this->available_users,
                'model'              => $this->model,
                'attribute'          => $this->attribute,
                'dialog'             => $this->dialog,
            ]
        );
    }

    private function getAvailableUsers()
    {
        if ($this->create_new) {
            $this->available_users = User::find()->where(['!=' , 'id', \Yii::$app->user->getId()])->all();
            return;
        }


        $UsersIds = [];
        foreach ($this->model->users as $user) {
            $UsersIds[] = $user->id;
        }

        $this->available_users = User::find()
            ->where(['NOT IN', 'id', $UsersIds])
            ->andWhere(['!=', 'id', \Yii::$app->user->getId()])
            ->all();
    }

    private function getCurrentReferences () {
        if (!empty($this->dialog))
        $this->references = $this -> dialog -> getReferences(true);
    }
}