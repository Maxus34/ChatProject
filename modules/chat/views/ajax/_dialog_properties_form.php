<?php
/* @var $dialog app\modules\chat\models\Dialog; */
/* @var $this yii\web\View */

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;

echo Html:: csrfMetaTags();
$this->registerJsFile('@web/js/dialog-modal.js', ['position' => yii\web\view::POS_END]);
?>


<p class='text-success' style='font-size:25px;'>Dialog properties</p>

<form method="POST" action="<?= \yii\helpers\Url::to(['ajax/form']) ?>" id="dialog-properties">
    <?php echo Html:: hiddenInput(\Yii:: $app->getRequest()->csrfParam, \Yii:: $app->getRequest()->getCsrfToken(), []); ?>
    <?php echo Html:: hiddenInput("DialogProp[id] ", $dialog->getId(), []); ?>

    <div class="form-group">
        <label for="title-input">Tile</label>
        <input class="form-control" id="title-input" name="DialogProp[title]" value="<?= $dialog->getTitle() ?>">
    </div>

    <hr>

    <label>Users:</label>
    <?php foreach ($dialog->getUsers(true) as $user): ?>
        <div class="form-group" id="users-selected">
            <label for="checkbox-<?= $user->id ?>"><?= $user->username ?></label>
            <input type="checkbox" name="DialogProp[users][]" id="checkbox-<?= $user->id?>" value="<?= $user->id ?>" checked>
        </div>
    <?php endforeach; ?>

    <hr>

    <div class="form-group">
        <select id="users-select">
            <?php
                foreach (\app\models\User::find()->orderBy(['username' => 'ASC'])->all() as $user) {
                    echo "<option value='" . $user->id . "'>" . $user->username . "</option>";
                }
            ?>
        </select>
    </div>

    <?php echo Html::submitButton('Submit', ['class' => 'btn btn-success']) ?>
</form>
