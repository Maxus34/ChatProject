<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 02.02.2017
 * Time: 11:20
 */

namespace app\modules\chat\components;

use yii\base\Widget;
use app\models\User;
use Yii;

class UserSelectWidget extends Widget
{
    public $model;
    public $attribute;
    public $references;
    public $current_users;
    public $available_users;

    public function run()
    {
        $this->getAvailableModels();
        $modelClassName = (new \ReflectionClass($this->model))->getShortName();

        return $this->render('@app/modules/chat/components/user_select/widget_tpl.php',
            [
                'current_references' => $this->references,
                'current_users'      => $this->current_users,
                'available_users'    => $this->available_users,
                'model'              => $modelClassName,
                'attribute'          => $this->attribute,
            ]
        );
    }

    private function getAvailableModels()
    {
        $array = [];
        foreach ($this->model->users as $user) {
            $array[] = $user->id;
        }

        $this->available_users = User::find()
                                    ->where(['NOT IN', 'id', $array])
                                    ->andWhere(['!=', 'id', \Yii::$app->user->getId()])
                                    ->all();
    }
}