<?php
namespace app\behaviors;

use app\models\Image;
use yii\base\Behavior;
use yii\base\Exception;
use yii\web\UploadedFile;
use app\models\records\ImageRecord;
use app\models\records\FileRecord;
use app\models\ImagePlaceholder;

class ImageBehavior extends Behavior
{
    public $placeholder_path;

    public $key = 'default';

    protected $_main_image = false;

    protected $_gallery_images = [];


    public function init()
    {
        parent::init();

        if (!file_exists($this->placeholder_path)){
            $error = "Placeholder image has been not found in: " . $this->placeholder_path;
            throw new Exception($error);
        }
    }

    public function attachImage(UploadedFile $file, $is_main=false, $name = false){
        //$savePath = $this->images_path . $file->baseName . "." . $file->extension;

        $file_id = $this->createFileRecord($file);
        $this->createImageRecord($file_id, $is_main);
    }

    public function getMainImage(){

        if ($this->_main_image){

        }

        $image = ImageRecord::find()
                    ->where(['key' => $this->key, 'item_id' => $this->owner->id, 'is_main' => 1])
                    ->orderBy(['id' => SORT_DESC])
                    ->one();

        $image_path = $image->path;

        if (empty($image)) {
            $image_path = $this->placeholder_path;
        }

        $this->_main_image = $image;

        return (new Image($image_path));
    }


    /* @param  UploadedFile $file*/
    /* @param  string       $savePath*/
    /* @return integer      $file_id*/
    protected function createFileRecord(UploadedFile $file){
        $file_record = new FileRecord($file);

        $file_record->save();

        return $file_record->id;
    }


    protected function createImageRecord($file_id, $is_main){
        $image_record = new ImageRecord();
        $image_record -> item_id = $this->owner->id;
        $image_record -> file_id = $file_id;
        $image_record -> is_main = $is_main;
        $image_record -> key     = $this->key;

        $image_record -> save();
    }

}