<?php

$this->title = "Dialog";

?>

<h4 class="text-center">Dialog #<?= $dialog->id ?> | <b>Users</b>: <?php foreach ($dialog->dialogUsers as $du) echo " " . $du->user->username?></h4><br>

<div class="col-md-8 col-md-offset-2">
    <div class="dialog" id="dialog">
        <?php if (!empty($messages)): ?>
            <?php foreach ($messages as $message): ?>
                <div class="message
                <?php
                   if(\Yii::$app->user->getId() == $message->user_id) echo "message-to"; else echo "message-from";
                 ?>" data-creation-date="<?=$message->creation_date ?>">
                    <p><?= $message->content ?></p>
                    <h6><?= Yii::$app->formatter->asDate($message->creation_date) ?></h6>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <h4 class="text-warning">You have not any messages in this dialog</h4>
        <?php endif; ?>
    </div>
    <div class="message-input">
        <textarea class="dialog" id="textarea"></textarea>
        <button id="send_message" class="btn" data-dialog_id="<?=$dialog->id?>" data-user_id="<?=\Yii::$app->user->getId()?>">Send</button>
    </div>
</div>