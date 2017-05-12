<?php
use yii\helpers\{
    Html, Url
};
use yii\bootstrap\{
    Nav, NavBar
};
use yii\widgets\Breadcrumbs;
use app\assets\{
    AppAsset, ie9AppAsset
};

AppAsset::register($this);
ie9AppAsset::register($this);

?>
<?php $this->beginPage() ?>
<!DOCTYPE html>

<html lang="<?= Yii::$app->language ?>">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>

</head>

<body>
<?php $this->beginBody() ?>

<div class="fixed-top">
    <?php
    NavBar::begin([
        'brandLabel' => 'MyProject',
        'brandUrl'  => Yii::$app->homeUrl,
        'options'   => [
            'class' => 'navbar navbar-inverse'
        ]
    ]);

    echo Nav::widget([
        'options' => [ 'class' => 'navbar-nav navbar-right'],
        'items'  => [
            ['label' => 'Home', 'url' => ['/']],
            ['label' => 'Chat', 'url' => ['/chat/']],
            ['label' => 'Admin panel', 'url' => ['/admin'], 'linkOptions' =>
                \Yii::$app->user->can('moder') ? [] : ['style' => 'display:none;']] ,

            ['label' => 'Registration', 'url' => ['/user/register'], 'linkOptions' =>
                \Yii::$app->user->isGuest ? [] : ['style' => 'display:none;']] ,


            \Yii::$app->user->isGuest ?
                ['label' => 'Login', 'url' => '/user/login', 'class' => 'btn btn-link', 'linkOptions' => [
                    'class' => 'btn btn-link',
                    'id'    => 'login-link'
                ]] :
                ['label' => "Logout(" . \Yii::$app->user->identity->username .")", 'url' => 'user/logout', 'linkOptions' => ['class' => 'btn btn-link']],
        ]
    ]);

    NavBar::end();
    ?>
    <?php if (isset($this->blocks['fixed-top'])): ?>
        <div class="container">
            <?= $this->blocks['fixed-top'] ?>

            <div class="col-md-8 col-sm-10 col-md-offset-2 col-sm-offset-1">
                <?php if (Yii::$app->session->hasFlash('success')): ?>
                    <div class="alert alert-success alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <?php echo Yii::$app->session->getFlash('success'); ?>
                    </div>
                <?php endif; ?>

                <?php if (Yii::$app->session->hasFlash('error')): ?>
                    <div class="alert alert-danger alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <?php echo Yii::$app->session->getFlash('error'); ?>
                    </div>
                <?php endif; ?>

                <?php if (Yii::$app->session->hasFlash('warning')): ?>
                    <div class="alert alert-warning alert-dismissible" role="alert">
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                aria-hidden="true">&times;</span></button>
                        <?php echo Yii::$app->session->getFlash('warning'); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div> <!--Fixed Layer-->

<div class="fixed-bottom">
    <?php if (isset($this->blocks['fixed-bottom'])): ?>
        <div class="container">
            <?= $this->blocks['fixed-bottom'] ?>
        </div>
    <?php endif; ?>
</div><!--Fixed Layer-->


<div class="container under-fixed" id="container">
    <?= $content ?>
</div>

<?php
\yii\bootstrap\Modal::begin([
    'id' => 'chat_modal',
    'size' => 'modal_md',
]);
\yii\bootstrap\Modal::end();

\yii\bootstrap\Modal::begin([
    'id' => 'media_modal',
    'size' => 'modal_lg',
]);
\yii\bootstrap\Modal::end(); ?>

<!--Modal Windows -->

<?php $this->endBody() ?>
</body>


</html>
<?php $this->endPage() ?>
