<?php
use yii\helpers\{Html, Url};
use yii\bootstrap\{Nav, NavBar};
use yii\widgets\Breadcrumbs;
use app\assets\{AppAsset, ie9AppAsset};

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
<div class="navbar navbar-inverse" >
    <div class="container">
        <div class="navbar-header">
            <!-- Button for smallest screens -->
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse"><span class="icon-bar"></span> <span class="icon-bar"></span> <span class="icon-bar"></span> </button>
            <a class="navbar-brand" href="<?=Url::home()?>"><?=Html::img('/images/logo.png', ['alt' => 'Progressus HTML5 template'])?></a>
        </div>
        <div class="navbar-collapse collapse">
            <ul class="nav navbar-nav pull-right">
                <li class="active"><a href="<?=Url::home()?>">Home</a></li>
                <li><a href="<?=Url::to(['/admin'])?>">Admin Panel</a></li>
                <li><a href="<?=Url::to(['/site/about'])?>">About</a></li>
                <li><a href="<?=Url::to(['/site/contact'])?>">Contact</a></li>
                <?php if (\Yii::$app->user->isGuest): ?>
                    <li><a class="btn" href="<?=Url::to(['/user/login']) ?>">LOGIN</a></li>
                <?php else: ?>
                    <li><a class="btn" href="<?=Url::to(['/user/logout'])?>">Logout<b>(<?=Yii::$app->user->identity->username ?>)</b></a></li>
                <?php endif; ?>
            </ul>
        </div><!--/.nav-collapse -->
    </div>
</div><!-- /.navbar -->

<div class="container">
    <br>
    <?php if(Yii::$app->session->hasFlash('success')): ?>
        <div class="alert alert-success alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <?php echo Yii::$app->session->getFlash('success'); ?>
        </div>
    <?php endif; ?>
    <?php if(Yii::$app->session->hasFlash('error')): ?>
        <div class="alert alert-danger alert-dismissible" role="alert">
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <?php echo Yii::$app->session->getFlash('error'); ?>
        </div>
    <?php endif; ?>

    <?= $content ?>
</div>

<?php
\yii\bootstrap\Modal::begin([
    'id' => 'chat_modal',
    'size' => 'modal_lg',
]);

\yii\bootstrap\Modal::end();
?><!--Modal Window -->

<?php $this->endBody() ?>
</body>


</html>
<?php $this->endPage() ?>
