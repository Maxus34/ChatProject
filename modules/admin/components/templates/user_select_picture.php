<?php

?>

<style>
    ul#selected_files{
        list-style-type: none;
    }
</style>

<div class="form-group user-main-image">
    <h3 class="text-success">Current image</h3>
    <?= \yii\bootstrap\Html::img($main_image, ['height' => 100, 'width' => 100]); ?>

    <input type="file" id="main_image-input" name="<?= $attribute?>" accept="image/*">
    <ul id="selected_files">

    </ul>
</div>


