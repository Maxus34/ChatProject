<?php
    use yii\helpers\Html;
    use yii\bootstrap\ActiveForm;
    $this->title='Chat | Registration';
?>

<h2>Registration</h2>

<?php $form = ActiveForm::begin([
    'id' => 'login-form',
    'layout' => 'horizontal',
    'fieldConfig' => [
        'template' => "{label}\n<div class=\"col-lg-3\">{input}</div>\n<div class=\"col-lg-8\">{error}</div>",
        'labelOptions' => ['class' => 'col-lg-1 control-label'],
    ],
]); ?>


<?= $form->field($model, 'username')->textInput(['autofocus' => true]); ?>

<?= $form->field($model, 'email')->textInput() ?>

<?= $form->field($model, 'password')->passwordInput() ?>

<?= $form->field($model, 'repeatPassword')->passwordInput() ?>


<div class="form-group">
    <div class="col-lg-offset-1 col-lg-11">
        <?= Html::submitButton('Регистрация', ['class' => 'btn btn-primary', 'name' => 'registration-button']) ?>
        <?= Html::resetButton('Сброс', ['class' => 'btn btn-danger']) ?>
    </div>
</div>

<?php ActiveForm::end() ?>

