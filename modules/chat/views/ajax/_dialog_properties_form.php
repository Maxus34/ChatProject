<?php
/* @var $dialog app\modules\chat\models\Dialog; */
/* @var $this yii\web\View */

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use app\modules\chat\components\UserSelectWidget;

echo Html:: csrfMetaTags();

?>


<p class='text-success' style='font-size:25px;'>Dialog properties</p>

<form method="POST" action="<?= \yii\helpers\Url::to(['ajax/form']) ?>" id="dialog-properties">
    <?php echo Html:: hiddenInput(\Yii:: $app->getRequest()->csrfParam, \Yii:: $app->getRequest()->getCsrfToken(), []); ?>
    <?php echo Html:: hiddenInput("DialogProp[id] ", $dialog->getId(), []); ?>

    <div class="form-group">
        <label for="title-input">Tile</label>
        <input class="form-control" id="title-input" name="DialogProp[title]" value="<?= $dialog->getTitle() ?>">
    </div>

    <?=UserSelectWidget::widget(['references' => $dialog->getReferences(true)]) ?>

    <?php echo Html::submitButton('Submit', ['class' => 'btn btn-success']) ?>
</form>
