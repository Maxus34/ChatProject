<?php
/* @var $dialog app\modules\chat\models\Dialog; */
$this->title = "Dialog";
?>

<h4 class="text-center">Dialog #<?= $dialog->getId() ?> |
    <b>Users</b>: <?php foreach ($dialog->getUsers() as $user) echo " " . $user->username ?></h4><br>

<div class="col-md-8 col-md-offset-2">
    <div class="dialog" id="dialog_block">
        <ul class="dialog" id="messages_list">
            <?php if (!empty($messages)): ?>
                <?php foreach ($messages as $message)
                    echo $this->render('_message', compact('message'));
                ?>
            <?php else: ?>
                <h4 class="text-danger text-center">You have not any messages in this dialog</h4>
            <?php endif; ?>
        </ul>
    </div>
    <div class="message-input">
        <textarea class="dialog" id="textarea"></textarea>
        <button id="send_message" class="btn" data-dialog_id="<?= $dialog->id ?>"
                data-user_id="<?= \Yii::$app->user->getId() ?>">Send
        </button>
    </div>
</div>
