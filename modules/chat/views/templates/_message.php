<?php
/* @var $message app\modules\chat\models\Message */
/* @var $prev_message app\modules\chat\models\Message */
?>
<li data-id="<?= $message->getId() ?>"
    data-new="<?= $message->isNew() ?>"
    data-user_id="<?= $message->getAuthor()->id ?>">

    <?php
    if (isset($prev_message)) {
        if (!$prev_message
            || $prev_message->getAuthor()->id != $message->getAuthor()->id
        ) {
            echo "<h5 class='message-author";

            echo $message->isAuthor(\Yii::$app->user->getId()) ? " message-outgoing" : " message-incoming";

            echo "'>"
                . $message->getAuthor()->username
                . "</h5>";
        }
    }
    ?>


    <div class="message
        <?php echo $message->isAuthor(\Yii::$app->user->getId()) ? "message-outgoing" : "message-incoming"; ?>
        <?php echo $message->isNew() ? "message-new" : ""; ?>
    ">
        <p><?= $message->getContent() ?></p>
        <h6><i><?= \Yii::$app->formatter->asDate($message->getCreationDate(), "php:d F, G : i") ?></i></h6>
    </div>
</li>

