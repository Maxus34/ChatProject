<?php
/* @var $dialog app\modules\chat\models\Dialog; */
/* @var $this yii\web\View */

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use app\modules\chat\components\UserSelectWidget;

echo Html:: csrfMetaTags();

?>


<p class='text-success' style='font-size:25px;'>Dialog properties</p>

<?php $form = ActiveForm::begin(['id' => "dialog-properties", 'action' => 'default/set-properties']) ?>

    <?= Html:: hiddenInput("DialogProp[id] ", $dialog->getId()); ?>
    <?= $form -> field($model, 'title')->input('string', ['id' => "title-input"]); ?>

    <?= UserSelectWidget::widget([
        'references' => $dialog->getReferences(true),
        'model'      => $model,
        'attribute'  => 'users'
    ]); ?>

    <?= Html::submitButton('Submit', ['class' => 'btn btn-success']) ?>

<?php ActiveForm::end() ?>
