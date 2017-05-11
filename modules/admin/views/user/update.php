<?php
    /**
     * @var \yii\web\View    $this
     * @var \app\models\User $model
     */


    $this->title = "Admin | Update User #" . $model -> id;
?>

<h2>Update user <?=$model->id?></h2>


<?php
    echo   $this->render('user_form', compact('model'));



