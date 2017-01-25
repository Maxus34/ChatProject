<?php

/*
 *  @var $dataProvider = \yii\data\ArrayDataProvider
 */

echo "<h2 class='text-success'>Dialogs</h2>";

echo \yii\widgets\ListView::widget([
    'options' => [
        'class' => 'list-view',
        'id' => 'messages'
    ],
    'itemView' => '_dialog',
    'dataProvider' => $dataProvider,
]);