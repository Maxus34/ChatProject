<?php
/**
 * @var \app\models\User $model
 *
 */

    use yii\bootstrap\ActiveForm;
?>

<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

<?= $form->field($model, 'username')->textInput() ?>
<?= $form->field($model, 'email')->textInput() ?>
<?= $form->field($model, 'isActive')->checkbox(['0', '1',]) ?>
<?= \app\modules\admin\components\UserSelectPicture::widget([
    'model' => $model,
    'attribute' => 'main-image',
]); ?>
<?= \yii\helpers\Html::submitButton('Submit', ['class' => 'btn btn-success']) ?> &nbsp;
<?= \yii\helpers\Html::submitButton('Reset' , ['class' => 'btn btn-danger']) ?>
<?php ActiveForm::end(); ?>

