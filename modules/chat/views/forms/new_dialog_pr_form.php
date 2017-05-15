<?php
/* @var $model app\modules\chat\models\DialogProperties*/
/* @var $create_new bool; */
/* @var $attribute string*/
/* @var $dialog app\modules\chat\models\Dialog */

use app\modules\chat\components\DialogPropertiesForm\DialogPropertiesForm;

echo DialogPropertiesForm::widget([
        'create_new' => $create_new,
        'model'      => $model,
        'attribute'  => $attribute,
        'dialog'     => $dialog ?? null,
    ]);
?>