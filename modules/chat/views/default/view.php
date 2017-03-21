<?php
/* @var $dialog app\modules\chat\models\Dialog; */
/* @var $this yii\web\View */
$this->registerJsFile("@web/js/chat/dialog_.js");


echo "<script>"
    . "var activeUser = {$dialog -> isActive()};"
    . "</script>";
?>

<?php $this->beginBlock('fixed-top') ?>
<div id="dialog_header_1" class="prop-block col-md-8 col-sm-10 col-md-offset-2 col-sm-offset-1">
    <div>
        <div class="col-md-1 col-sm-1">
            <a class="btn-sm btn-primary" href="<?= \yii\helpers\Url::to(['/chat/default']) ?>">Back</a>
        </div>
        <div class="col-md-3 col-sm-3 col-sm-offset-1 ">
            <span class="text-left">#<?= $dialog->getId() . " | " . $dialog->getTitle() ?></span>
        </div>
        <div class="col-md-6 col-sm-6">
            <p class="text-success text-center" style="font-weight:700; text-align: center;">
                <?php
                foreach ($dialog->getUsers(true) as $user) echo " " . $user->username
                ?>
            </p>
        </div>

        <div class="col-md-1 col-sm-1" data-toggle="tooltip" title="Options" data-placement="bottom">
            <a id="dialog_properties" style="cursor:pointer;"><i class="fa fa-ellipsis-h" aria-hidden="true"
                                                                 style="font-size: 20px;"></i></a>
        </div>
    </div>
</div>

<div id="dialog_header_2" class="prop-block col-md-8 col-sm-10 col-md-offset-2 col-sm-offset-1" style="display: none;">
    <div>
        <div class="col-md-1 col-sm-1">
            <a class="btn-sm btn-primary" href="<?= \yii\helpers\Url::to(['/chat/default']) ?>">Back</a>
        </div>
        <div class="col-md-7     col-sm-7 col-sm-offset-3 ">
            <a id="delete_messages" class="btn-sm btn-primary">Delete</a>
            <a id="reset_delete_messages" class="btn-sm btn-primary"><i class="fa fa-times" aria="hidden"></i>Reset</a>
        </div>
        <div class="col-md-1 col-sm-1" data-toggle="tooltip" title="Options" data-placement="bottom">
            <a id="dialog_properties" style="cursor:pointer;"><i class="fa fa-ellipsis-h" aria-hidden="true"
                                                                 style="font-size: 20px;"></i></a>
        </div>
    </div>
</div><!--Шапка диалога | Настройки -->
<?php $this->endBlock() ?>

<?php $this->beginBlock('fixed-bottom') ?>
<div class="col-md-8 col-sm-10 col-md-offset-2 col-sm-offset-1">

    <ul id="files-list" class="files-list">

    </ul>

    <div>
        <div class="typing" id="typing">

        </div>

        <div class="message-input">
            <textarea class="dialog" id="textarea"></textarea>

            <a id="send_message" class="btn btn-primary" data-dialog_id="<?= $dialog->getId() ?>"
               data-user_id="<?php echo \Yii::$app->user->getId() ?>"><i class="fa fa-paper-plane-o fa-2x"
                                                                         aria-hidden="true"></i>
            </a>

            <div class="btn-group dropup">
                <a class="btn dropdown-toggle" data-toggle="dropdown" href="#">
                    <i class="fa fa-plus" aria-hidden="true"></i>
                </a>
                <ul class="dropdown-menu">
                    <li>
                        <a href="#" id="upload-file"><i class="fa fa-file-o" aria-hidden="true"></i> Upload File</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php $this->endBlock() ?>

<div class="col-md-8 col-sm-10 col-md-offset-2 col-sm-offset-1">
    <div class="dialog" id="dialog_block">
        <ul class="dialog" id="messages_list">
            <?php if (!empty($messages)): ?>
                <?php
                $users = $dialog->getUsers();
                ?>

                <?php
                foreach ($messages as $i => $message) {
                    $user_image = $users[$messages[$i]->getAuthorId()]->getMainImage()->getUrl([100, 100]);
                    echo $this->render('/templates/_message', [
                        'message' => $messages[$i],
                        'user_image' => $user_image,
                    ]);
                }
                ?>

            <?php endif; ?>
        </ul>

    </div>
</div><!--Блок с сообщениями -->

