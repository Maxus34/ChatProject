<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 16.03.2017
 * Time: 16:17
 */

namespace app\models\records;


use Imagine\Image\Box;
use Imagine\Imagick\Imagine;
use yii\db\ActiveRecord;
use yii\imagine\Image;

/* @property integer $id*/
/* @property integer item_id*/
/* @property integer $file_id*/
/* @property boolean $is_main*/
/* @property string  $key*/
/* @property integer $created_at*/
class ImageRecord extends ActiveRecord
{
    public $images_path;

    public $cash_path;

    static function tableName()
    {
        return "images";
    }

    public function getUrl($size = false){
        $file_record = FileRecord::findOne(['id' => $this->file_id]);


        if (!$size){
            return "/" . $file_record->path;
        } else {
            return "/" . $this->getResizedImageUrl($file_record, $size);
        }
    }

    protected function getResizedImageUrl($file, $size){

        $resized_file_path = $this->cash_path . "{$size[0]}x{$size[1]}_" . $file->name . "." . $file->extension;

        if (file_exists($resized_file_path)){
            return $resized_file_path;
        }

        else {
            $box = new Box($size[0], $size[1]);
            $Imagine = new Imagine();
            $image = $Imagine -> open($file->path);
            $image -> resize($box) -> save($resized_file_path);
        }

        return $resized_file_path;
    }
}