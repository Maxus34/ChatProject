<?php
    /* @var $this yii\web\View;*/


    $this->title = "Admin | Update User #" . $model -> id;
?>

<h2>Update user <?=$model->id?></h2>


<?php
    echo   $this->render('user_form', compact('model'));



