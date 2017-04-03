<?php
    use app\models\User;
    use app\modules\chat\components\MessageFilesWidget;
?>
<li data-id = "<?= $message->getId() ?>"
    data-new="<?= $message->isNew()?>"
    class="message
        <?php echo $message->isAuthor(\Yii::$app->user->getId()) ?  "message-outgoing" : "message-incoming"; ?>
        <?php echo $message->isNew() ?  "message-new" : ""; ?>
    ">
        <h5><?= User::findIdentity($message->getAuthorId())->username ?></h5>
        <p><?= $message->getContent() ?></p>
        <h6><i><?= \Yii::$app->formatter->asDate($message->getCreationDate(), "php:d F, G : i") ?></i></h6>
        <img class="message-image" src="<?=$user_image?>">

    <?php
        echo MessageFilesWidget::widget([
            'model' => $message
        ]);
    ?>
</li>

