<?php
/* @var $dialog app\modules\chat\models\Dialog; */
$this->title = "Dialog";
?>
<div class="col-md-8 col-md-offset-2">
    <div>
        <button type="button" class="btn-info btn-xs" onclick="history.back()">Назад</button>
        <span class="text-left">&nbsp;&nbsp;&nbsp;Dialog #<?= $dialog->getId() ?> &nbsp;|&nbsp;
        <b>Users</b>: <?php foreach ($dialog->getUsers() as $user) echo " " . $user->username ?></span>

        <div class="btn-group pull-right">
            <button data-toggle="dropdown" class="three-dots dropdown-toggle"><i class="fa fa-ellipsis-h" aria-hidden="true" style="text-size: 15px;"></i></button>
            <ul class="dropdown-menu">
                <li><a href="#">Добавить собеседника</a></li>
                <li><a href="#">Что то еще ...</a></li>
                <li><a href="#">... и еще :)</a></li>
            </ul>
        </div>
    </div>
</div>

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

    <div class="typing" id="typing">

    </div>

    <div class="message-input">
        <textarea class="dialog" id="textarea"></textarea>
        <button id="send_message" class="btn" data-dialog_id="<?= $dialog->getId() ?>"
                data-user_id="<?= \Yii::$app->user->getId() ?>">Send
        </button>
    </div>

</div>
