<?php
/* @var $current_models  app\models\User; */
/* @var $available_models app\models\User; */

?>

<div class="panel panel-primary">
    <div class="panel-heading"><i>Current <b>users</b> in the dialog</i></div>
    <div class="panel-body" id="selected_users">
        <?php foreach ($current_models as $user): ?>
            <label class="checkbox-inline" for="checkbox-<?= $user->id ?>">
                <input type="checkbox"
                       name="DialogProp[users][]"
                       id="checkbox-<?= $user->id ?>"
                       value="<?= $user->id ?>"
                       checked >
                <?= $user->username ?>
            </label>
        <?php endforeach; ?>
    </div>
    <div class="panel-footer">
        <a href="#" class="btn-sm btn-primary">Add user</a> &nbsp;
        <select id="users-select">
            <?php
            foreach ($available_models as $user) {
                echo "<option value='" . $user->id . "'>" . $user->username . "</option>";
            }
            ?>
        </select>
    </div>
</div>
