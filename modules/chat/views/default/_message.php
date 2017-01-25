<li data-id = "<?= $message->getId() ?>"
    data-creation-date = "<?= $message->getCreationDate() ?>"
    data-new="<?= $message->isNew()?>">
    <div class="message  <?php echo $message->isAuthor(\Yii::$app->user->getId()) ?  "message-to" : "message-from"; ?>">
        <p><?= $message->getContent() ?></p>
        <h6><?= \Yii::$app->formatter->asDate($message->getCreationDate() ) ?></h6>
    </div>
</li>

