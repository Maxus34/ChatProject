<?php
/* @var $dialog app\modules\chat\models\Dialog; */
$this->title = "Dialog";
?>
<div class="col-md-8 col-sm-10 col-md-offset-2 col-sm-offset-1">
    <div>
        <div class="col-md-1 col-sm-1">
            <button type="button" class="btn-info btn-xs" onclick="history.back()">Назад</button>
        </div>
        <div class="col-md-2 col-sm-2 col-md-offset-1 col-sm-offset-1">
            <span class="text-left">Dialog #<?= $dialog->getId() ?></span>
        </div>
        <div class="col-md-6 col-sm-6">
            <p class="text-success text-center" style="font-weight:700; text-align: center;">
             <?php
                foreach ($dialog->getUsers(true) as $user) echo " " . $user->username
             ?>
            </p>
        </div>

        <div class="btn-group col-md-1 col-sm-1 col-md-offset-1 col-sm-offset-1">
            <button data-toggle="dropdown" class="three-dots dropdown-toggle pull-right"><i class="fa fa-ellipsis-h" aria-hidden="true" style="text-size: 15px;"></i></button>
            <ul class="dropdown-menu">
                <li><a href="#">Добавить собеседника</a></li>
                <li><a href="#">Что то еще ...</a></li>
                <li><a href="#">... и еще :)</a></li>
            </ul>
        </div>
    </div>
</div> <!--Шапка диалога | Настройки -->

<div class="col-md-8 col-sm-10 col-md-offset-2 col-sm-offset-1">
    <div class="dialog" id="dialog_block">
        <ul class="dialog" id="messages_list">
            <?php if (!empty($messages)): ?>
                <?php foreach ($messages as $message)
                    echo $this->render('/templates/_message', compact('message'));
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

</div> <!--Блок с сообщениями -->
