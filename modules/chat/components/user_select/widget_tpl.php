<?php
    use app\models\User;
/* @var $this yii\web\View;*/
/* @var $available_models app\models\User; */
/* @var $current_references app\modules\chat\models\records\DialogReferenceRecord; */

    //$this->registerCssFile('@web/css/user_select-widget.css', ['position' => yii\web\view::POS_HEAD])
    $this->registerJsFile('@web/js/user_select-widget.js', ['position' => yii\web\view::POS_END]);
?>

<div class="panel panel-primary">
    <div class="panel-heading"><i>Current <b>users</b> in the dialog</i></div>

    <div class="panel-body" id="selected_users">

        <?php foreach ($current_references as $reference): ?>
            <label class="user-checkbox">
                <input type="checkbox" name="DialogProp[users][]" id="checkbox-<?= $reference->user_id ?>" value="<?= $reference->user_id ?>" checked>
                <span><?= $reference->user->username;?></span>
                <p> Added by <?= User::findOne($reference->created_by)->username ?> on <?= \Yii::$app->formatter->asDate($reference->created_at); ?> </p>
				<a class="btn-delete"  > <i class="fa fa-times-circle" aria-hidden="true"> </i></a>
                <a class="btn-restore" > <i class="fa fa-plus-circle"  aria-hidden="true"> </i></a>
            </label>
        <?php endforeach; ?>

    </div> <!-- Users in dialog -->

    <div class="panel-footer">

        <a id="add-user" class="btn-sm btn-primary">Add user</a> &nbsp;
        <select id="users-select">
            <?php
            foreach ($available_users as $user) {
                echo "<option value='" . $user->id . "'>" . $user->username . "</option>";
            }
            ?>
        </select>

    </div> <!-- Users Select -->
</div>
