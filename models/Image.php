<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 03.04.2017
 * Time: 16:01
 */

namespace app\models;


use yii\base\Exception;
use yii\base\Model;
use Imagine\Image\Box;
use Imagine\Imagick\Imagine;


class Image extends Model
{
    const CASH_PATH = 'upload/images/cash/';

    /**
     * @var String
     */
    protected $path;

    public function __construct($path)
    {
        parent::__construct(null);

        if (file_exists($path)){
            $this->path = $path;
        } else {
            throw new Exception("No found image in {$this->path}");
        }
    }


    public function init(){
        parent::init();

        if (!file_exists(static::CASH_PATH)){
            mkdir(static::CASH_PATH);
        }
    }

    /** size = [height, width]
     * @param array $size ;
     * @return string
     */
    public  function getUrl($size = null){

        $path = $this->path;

        if (empty($size)){
            return "/" . $path;
        } else {
            return "/" . $this->getResizedImageUrl($path, $size);
        }
    }



    protected function getResizedImageUrl($path, $size){
        $resizedImageFilePath = static::CASH_PATH . "{$size[0]}x{$size[1]}_" . basename($path);

        if (!file_exists($resizedImageFilePath)){
            $Imagine  =  new Imagine();
            $image    =  $Imagine -> open($path);

            $this->checkSize($size, $image);

            $box = new Box($size[1], $size[0]);
            $image -> resize($box) -> save($resizedImageFilePath);
        }

        return $resizedImageFilePath;
    }


    protected function checkSize(&$size, $image){
        $box = $image->getSize();

        if (empty($size[0]) && empty($size[1])) {
            $size[0] = $box->getHeight();
            $size[1] = $box->getWidth();
        }

        if (empty($size[0])){
            $size[0] = $box->getHeight() / ($box->getWidth() / $size[1]);
        }

        if (empty($size[1])){
            $size[1] = $box->getWidth() / ($box->getHeight() / $size[0]);
        }
    }
}