<?php
use yii\bootstrap\Html;
use yii\widgets\DetailView;
/* @var $user app\models\User*/
$this->title = 'Admin | view user';

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
                'attribute' => 'createdAt',
                'value' => Yii::$app->formatter->asDate($user->createdAt),
            ],
            [
                'attribute' => 'active',
                'format' => 'raw',
                'value' => ($user->isActive == 1) ? "<span class='text-success'><b>Yes</b></span>" : "<span class='text-success'><b>No</b></span>",
            ],
            [
                'attribute' => 'image',
                'label'     => 'Main Image',
                'format'    => 'html',
                'value' => (function () use($user){
                    return Html::img($user->getMainImage()->getUrl([150, 150]));
                })()
            ]
        ]

    ]);
    ?>
</div>
