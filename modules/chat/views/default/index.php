<?php
/*
 *  @var $dialogs = \app\models\Dialog;
 */
?>

<?php $this->beginBlock('fixed-top') ?>
<div class="container">
    <div class="prop-block col-md-8 col-sm-10 col-md-offset-2 col-sm-offset-1">
        <div class="col-md-1 col-sm-1">
            <a class="btn-sm btn-primary" onclick="history.back()">Back</a>
        </div>
        <div style="cursor:pointer; position:absolute; top:24%; right:3%;">
           <a data-toggle="tooltip" title="Create" data-placement="bottom" ><i class="fa fa-plus" aria-hidden="true" style="font-size: 15px;"></i></a>
        </div>
    </div> <!--Шапка диалога | Настройки -->
</div>
<?php $this->endBlock() ?>


<div class="col-md-8 col-sm-10 col-md-offset-2 col-sm-offset-1">

    <?php
    foreach ($dialogs as $dialog) {
        echo $this->render('/templates/_dialog', compact('dialog'));
    }
    ?>
</div>