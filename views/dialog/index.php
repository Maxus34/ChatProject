<?php
//$dialogs - app\models\DialogUser

$this->title = "Dialogs";
?>

    <h2 class="text-primary">Dialogs for: <strong><?= Yii::$app->user->identity->username ?></strong></h2>

    <a href="<?= \yii\helpers\Url::to(['create']) ?>" class="btn btn-primary">New Dialog</a>

    <div class="col-md-8 col-md-offset-2">

<?php if (!empty($dialogs)): ?>
    <ul>
        <?php foreach ($dialogs as $dialog): ?>
            <li class="dialog-list">
                <a   href="<?= \yii\helpers\Url::to(['view', 'id' => $dialog->dialog->id]) ?>">
                    <?php
                    echo "With: ";
                    foreach ($dialog->dialog->dialogUsers as $du) {
                        if ($du->user->id != Yii::$app->user->getId()){
                            echo " " . $du->user->username;
                        }
                    }
                    echo " | <strong>" . $dialog->dialog->title . "</strong>";
                    echo " | " . $dialog->dialog->getCountOfMessages() . " messages";
                    ?>
                </a>
            </li>
        <?php endforeach; ?>
    </ul>
    </div>

<?php else: ?>
    <h4 class="text-warning">You have not any dialogs</h4>
<?php endif; ?>