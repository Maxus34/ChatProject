<?php
    /* @var $this yii\web\View;*/

    $this->registerJsFile("@web/js/user/user_form.js", ['position' => yii\web\View::POS_END]);
    $this->title = "Admin | Update User #" . $model -> id;
?>

<h2>Update user <?=$model->id?></h2>


<?php
    echo   $this->render('user_form', compact('model'));



