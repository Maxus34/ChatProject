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
    public $placeholder_path;

    public $cash_path;

    public function __construct($path)
    {
        $this->placeholder_path = $path;
    }

    public function getUrl($size = false){
        if (!file_exists($this->placeholder_path)){
            throw new Exception("File does not exists " . $this->placeholder_path);
        }

        if (!$size){
            return "/" . \Yii::getAlias("@web") . $this->placeholder_path;
        }

        else {
            return "/" . \Yii::getAlias("@web") . $this->getResizedImageUrl($this->placeholder_path, $size);
        }

    }

    protected function getResizedImageUrl($path, $size){

        $resized_file_path = $this->cash_path . "{$size[0]}x{$size[0]}_" . basename($this->placeholder_path);

        if (file_exists($resized_file_path)){
            return $resized_file_path;
        }
        else {
            $box = new Box($size[0], $size[1]);
            $Imagine = new Imagine();
            $image = $Imagine -> open($path);
            $image -> resize($box) -> save($resized_file_path);

            return $resized_file_path;
        }
    }

}