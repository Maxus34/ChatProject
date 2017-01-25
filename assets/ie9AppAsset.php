<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 11.12.2016
 * Time: 14:39
 */

namespace app\assets;
use yii\web\AssetBundle;

class ie9AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';

    public $js = [
        'js/html5shiv.js',
        'js/respond.min.js',
    ];
    public $jsOptions = [
        'condition' => 'lt IE 9',
        'position' => \yii\web\View::POS_HEAD,
    ];
}
