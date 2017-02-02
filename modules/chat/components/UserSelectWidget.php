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
    public $current_models;
    public $available_models;
    public $html;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        $this->getAvailableModels();

        return $this->render('@app/modules/chat/components/user_select/widget_tpl.php',
            [
                'current_models' => $this->current_models,
                'available_models' => $this->available_models,
            ]
        );
    }

    private function getAvailableModels()
    {
        $array = [];
        foreach ($this->current_models as $model) {
            $array[] = $model->id;
        }
        $this->available_models = User::find()
                                    ->where(['NOT IN', 'id', $array])
                                    ->andWhere(['!=', 'id', \Yii::$app->user->getId()])
                                    ->all();
    }
}