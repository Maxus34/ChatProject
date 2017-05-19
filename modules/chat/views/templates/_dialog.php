<?php
use yii\helpers\Url;

/* @var $dialog \app\modules\chat\models\DialogN */
?>


<table class="table table-hover" style="background: #fef;">
    <thead>
    <tr>
        <td style="width:55%;"><b>title</b></td>
        <td style="width:30%;"><b>users</b></td>
        <td><b>Messages(new)</b></td>
        <td></td>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td><a href="<?= Url::to(['/chat/default/view', 'id' => $dialog->getId()]) ?>"><?= $dialog->getTitle() ?></a>
        </td>
        <td>
            <?php
                foreach ($dialog->getUsers(true) as $user) {
                    echo $user->username . "&nbsp;";
                }
            ?>
        </td>
        <td> <?=$dialog->messageHandler->getMessagesCount()?> (<?= $dialog->messageHandler->getMessagesCount(true)?>)</td>
        <td><a href="<?= Url::to(['delete-dialog', 'id' => $dialog->id]) ?>" data-toggle="tooltip" title="Delete" data-placement="bottom" style="color:#e74c3c"><i class="fa fa-times"></i></a></td>
    </tr>
    </tbody>
</table>