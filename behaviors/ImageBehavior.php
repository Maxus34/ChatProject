<?php
namespace app\behaviors;

use yii\base\Behavior;
use yii\base\Exception;
use yii\web\UploadedFile;
use app\models\records\ImageRecord;
use app\models\records\FileRecord;
use app\models\ImagePlaceholder;

class ImageBehavior extends Behavior
{
    public $placeholder_path;

    public $images_path;

    public $cash_path = 'cash';

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
        if (!file_exists($this->images_path)){
            mkdir($_SERVER['DOCUMENT_ROOT'] . '/web/' . $this->images_path);
        }
        if (!file_exists($this->cash_path)){
            mkdir($_SERVER['DOCUMENT_ROOT'] . '/web/'  . $this->cash_path);
        }
    }

    public function attachImage(UploadedFile $file, $is_main=false, $name = false){
        $savePath = $this->images_path . $file->baseName . "." . $file->extension;

        $file_id = $this->createFileRecord($file, $savePath);
        $this->createImageRecord($file_id, $is_main);
        $this->saveFile($file, $savePath);
    }

    public function getMainImage(){

        if ($this->_main_image){
            return $this->_main_image;
        }

        $query = ImageRecord::find();

        $query = $query->where(['key' => $this->key, 'item_id' => $this->owner->id, 'is_main' => 1])
                       ->orderBy(['id' => SORT_DESC]);

        $image = $query->one();

        if (empty($image)) {
            $image = new ImagePlaceholder($this->placeholder_path);
        }

        $image->cash_path = $this->cash_path;
        $this->_main_image = $image;

        return $image;
    }


    /* @param  UploadedFile $file*/
    /* @param  string       $savePath*/
    /* @return integer      $file_id*/
    protected function createFileRecord(UploadedFile $file, $savePath){
        $file_record = new FileRecord();

        $file_record->name      = $file->baseName;
        $file_record->extension = $file->extension;
        $file_record->type      = $file->type;
        $file_record->size      = $file->size;
        $file_record->type      = $file->type;
        $file_record->path      = $savePath;

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


    protected function saveFile(UploadedFile $file, string $path){
        $file->saveAs($path);
    }
}