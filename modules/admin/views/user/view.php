<?php
use yii\bootstrap\Html;
use yii\widgets\DetailView;

$this->title = 'Admin | Create user';
?>

<div>
    <h2><?="#" . $user->id . " | &nbsp;&nbsp;" . $user->username?></h2>

    <?= DetailView::widget([

        'model' => $user,
        'attributes' => [
            'id',
            'username',
            'email',
            [
                'attribute' => 'reg_date',
                'value' => Yii::$app->formatter->asDate($user->reg_date),
            ],
            [
                'attribute' => 'active',
                'format' => 'raw',
                'value' => ($user->active == 1) ? "<span class='text-success'><b>Yes</b></span>" : "<span class='text-success'><b>No</b></span>",
            ]
        ]

    ]);
    ?>
</div>
