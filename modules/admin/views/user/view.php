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
                'attribute' => 'created_at',
                'value' => Yii::$app->formatter->asDate($user->created_at),
            ],
            [
                'attribute' => 'active',
                'format' => 'raw',
                'value' => ($user->active == 1) ? "<span class='text-success'><b>Yes</b></span>" : "<span class='text-success'><b>No</b></span>",
            ],
            [
                'attribute' => 'image',
                'label'     => 'Main Image',
                'format'    => 'html',
                'value' => (function () use($user){
                    return Html::img($user->getMainImage()->getUrl([100, 100]));
                })()
            ]
        ]

    ]);
    ?>
</div>
