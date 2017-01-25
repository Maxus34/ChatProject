<?php foreach ($messages as $message) {
    echo "<div class='message " . (Yii::$app->user->getId() == $message->user_id ? "message-to '" : "message-from '") . "data-creation-date='$message->creation_date'>";
    include(__DIR__ . "/message_template.php");
    echo "</div>";
} ?>
