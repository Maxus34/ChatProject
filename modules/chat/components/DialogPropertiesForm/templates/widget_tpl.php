<?php
/* @var $this yii\web\View;*/
/* @var $available_models app\models\User; */
/* @var $current_references app\modules\chat\models\records\DialogReferenceRecord; */
/* @var $create_new boolean;*/
/* @var $model      app\modules\chat\models\DialogProperties */
/* @var $attribute  string */
use app\models\User;
use yii\bootstrap\{ ActiveForm, Html };

    $this->registerJsFile('@web/js/chat/user_select-widget.js', ['position' => yii\web\view::POS_END], 1);

?>

<?php
    $modelClassName = (new \ReflectionClass($model))->getShortName();
    echo "<script> var modelName = '{$modelClassName}'; var attributeName = '{$attribute}'; </script>";
?> <!-- Variables for Js -->

<p class='text-success' style='font-size:25px;'>Dialog properties</p>



<?php
        $form = ActiveForm::begin(
            [
                'id' => "dialog-properties",
                'action' =>  ($create_new) ? '/chat/default/create-dialog' : '/chat/default/set-dialog-properties'
            ]
        );

        if (!$create_new) {
            echo Html:: hiddenInput("DialogProp[id] ", $dialog->getId());
            echo $form -> field($model, 'title')->input('string', ['id' => "title-input"]);

        } else {
            echo $form -> field($model, 'title')->input('string', ['id' => "title-input", "placeholder" => 'Please enter name for new dialog']);
        }
?>


<div class="panel panel-primary">
    <div class="panel-heading"><i>Current <b>users</b> in the dialog</i></div>

    <div class="panel-body" id="selected_users">

        <?php foreach ($current_references as $reference): ?>
            <label class="user-checkbox">
                <input type="checkbox" name="<?=$modelClassName?>[<?=$attribute?>][]" id="checkbox-<?= $reference->user_id ?>" value="<?= $reference->user_id ?>" checked>
                <span><?= $reference->user->username;?></span>
                <p> Added by <?= User::findOne($reference->created_by)->username ?> on <?= \Yii::$app->formatter->asDate($reference->created_at); ?> </p>

                <?php
                    if (   $reference->created_by == $dialog->getUserId()
                        || $dialog -> isCreator( $dialog->getUserId()) ){
                        echo "<a class=\"btn-delete\"  > <i class=\"fa fa-times\" aria-hidden=\"true\"> </i></a>";
                        echo "<a class=\"btn-restore\" > <i class=\"fa fa-undo\"  aria-hidden=\"true\"> </i></a>";
                    }
                ?>


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

<?= Html::submitButton('Submit', ['class' => 'btn btn-success']) ?>

<?php ActiveForm::end() ?>




