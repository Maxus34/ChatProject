<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 16.03.2017
 * Time: 17:12
 */

namespace app\models;


use app\models\records\ImageRecord;
use yii\web\Exception;
use yii\base\Object;
use Imagine\Image\Box;
use Imagine\Imagick\Imagine;

class ImagePlaceholder extends Object
{
    protected $_placeholder_path;

    public function __construct($path)
    {
        $this->_placeholder_path = $path;
    }

    public function getPath(){
        return $this->_placeholder_path;
    }
}