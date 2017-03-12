<?php
/* @var $dialog app\modules\chat\models\Dialog; */
/* @var $this yii\web\view */
$this->registerJsFile("@web/js/chat/dialog_.js");


echo "<script>"
    . "var activeUser = {$dialog -> isActive()};"
    . "var users = {";
        foreach ($dialog->getUsers() as $user){
            echo $user->id . " : '" . $user->username . "', ";
        }
echo "}"
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
        <div id="delete_messages" class="col-md-9 col-sm-9 col-sm-offset-1 ">
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
    <div class="typing" id="typing">

    </div>

    <div class="message-input">
        <textarea class="dialog" id="textarea"></textarea>
        <button id="send_message" class="send-message" data-dialog_id="<?= $dialog->getId() ?>"
                data-user_id="<?= \Yii::$app->user->getId() ?>">
            <i class="fa fa-chevron-right" aria-hidden="true"></i>
        </button>
    </div>

</div>

<?php $this->endBlock() ?>


<div class="col-md-8 col-sm-10 col-md-offset-2 col-sm-offset-1">
    <div class="dialog" id="dialog_block">
        <ul class="dialog" id="messages_list">
            <?php
            if (!empty($messages)){
                $prev_message = false;
                foreach ($messages as $message){
                    echo $this->render('/templates/_message', compact('message', 'prev_message'));
                    $prev_message = $message;
                }
            }
            ?>
        </ul>

    </div>
</div><!--Блок с сообщениями -->



