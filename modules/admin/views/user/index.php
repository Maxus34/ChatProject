
<?php
    use yii\bootstrap\Html;
    use yii\grid\GridView;

    $this->title = 'Admin | Users'
?>

<div>
    <h2>Users</h2>

    <p>
        <?= Html::a('Create User', ['create'], ['class' => 'btn btn-primary']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'columns' => [
            'id',
            'username',
            'email',
            [
                'attribute' => 'createdAt',
                'label'     => 'Registration date',
                'value' => function ($data) {
                    return Yii::$app->formatter->asDate($data->createdAt, "d/m/Y");
                }
            ],
            [
                'attribute' => 'isActive',
                'format' => 'raw',
                'value' => function ($data) {
                    return $data->isActive ? "<span class='text-success'><b>Active</b></span>" : "<span class='text-warning'><b>Not</b></span>";
                }
            ],
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
</div>
