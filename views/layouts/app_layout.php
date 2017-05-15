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

<div class="wrap">
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
               ['label' => "Logout(" . \Yii::$app->user->identity->username .")", 'url' => '/user/logout', 'linkOptions' => ['class' => 'btn btn-link']],
       ]
   ]);

   NavBar::end();
   ?>

    <div class="container">
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
</div>

<footer id="footer" class="top-space">

    <div class="footer1">
        <div class="container">
            <div class="row">

                <div class="col-md-3 widget">
                    <h3 class="widget-title">Contact</h3>
                    <div class="widget-body">
                        <p>+234 23 9873237<br>
                            <a href="mailto:#">some.email@somewhere.com</a><br>
                            <br>
                            234 Hidden Pond Road, Ashland City, TN 37015
                        </p>
                    </div>
                </div>

                <div class="col-md-3 widget">
                    <h3 class="widget-title">Follow me</h3>
                    <div class="widget-body">
                        <p class="follow-me-icons">
                            <a href=""><i class="fa fa-twitter fa-2"></i></a>
                            <a href=""><i class="fa fa-dribbble fa-2"></i></a>
                            <a href=""><i class="fa fa-github fa-2"></i></a>
                            <a href=""><i class="fa fa-facebook fa-2"></i></a>
                        </p>
                    </div>
                </div>

                <div class="col-md-6 widget">
                    <h3 class="widget-title">Text widget</h3>
                    <div class="widget-body">
                        <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit. Excepturi, dolores, quibusdam architecto voluptatem amet fugiat nesciunt placeat provident cumque accusamus itaque voluptate modi quidem dolore optio velit hic iusto vero praesentium repellat commodi ad id expedita cupiditate repellendus possimus unde?</p>
                        <p>Eius consequatur nihil quibusdam! Laborum, rerum, quis, inventore ipsa autem repellat provident assumenda labore soluta minima alias temporibus facere distinctio quas adipisci nam sunt explicabo officia tenetur at ea quos doloribus dolorum voluptate reprehenderit architecto sint libero illo et hic.</p>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="footer2">
        <div class="container">
            <div class="row">

                <div class="col-md-6 widget">
                    <div class="widget-body">
                        <p class="simplenav">
                            <a href="#">Home</a> |
                            <a href="about.html">About</a> |
                            <a href="sidebar-right.html">Sidebar</a> |
                            <a href="contact.html">Contact</a> |
                            <b><a href="signup.html">Sign up</a></b>
                        </p>
                    </div>
                </div>

                <div class="col-md-6 widget">
                    <div class="widget-body">
                        <p class="text-right">
                            Copyright &copy; 2014, Your name. Designed by <a href="http://gettemplate.com/" rel="designer">gettemplate</a>
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </div>

</footer>

<?php echo
    \yii\bootstrap\Modal::widget([
        'id'   => 'small-modal',
    ]);

?>

<?php $this->endBody() ?>
</body>


</html>
<?php $this->endPage() ?>
