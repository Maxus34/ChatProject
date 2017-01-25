<?php
    use yii\helpers\Url;
    /* @var $model = \app\modules\chat\models\Dialog */
?>

<table class="table table-hover" style="background: #fef;">
    <thead>
       <tr>
           <td style="width:55%;"><b>title</b></td>
           <td style="width:30%;"><b>users</b></td>
           <td><b>new </b></td>
       </tr>
    </thead>
    <tbody>
        <tr>
            <td><a href="<?=Url::to(['default/view', 'id' => $model->getId()])?>"><?=$model->getTitle()?></a></td>
            <td>
                <?php
                    foreach ($model->getUsers() as $user){
                        echo  $user->username . "&nbsp;&nbsp;";
                    }
                ?>
            </td>
            <td><?= $model->getMessagesCount(true) ?></td>
        </tr>
    </tbody>
</table>