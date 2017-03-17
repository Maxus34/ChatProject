<?php
    $this->registerJsFile("@web/js/user/user_form.js", ['position' => yii\web\View::POS_END]);
?>

<style>
    ul#selected_files{
        list-style-type: none;
    }
</style>

<div class="form-group user-main-image">
    <h3 class="text-success">Current image</h3>
    <?= \yii\bootstrap\Html::img($main_image->getUrl([200, 200]), [
        'id'     => 'usr_select_form-image_selected',
        'height' => 200,
        'width'  => 200,
    ]); ?>

    <input type="file" id="main_image-input" name="<?= $attribute?>" accept="image/*">
    <ul id="selected_files">

    </ul>
</div>


