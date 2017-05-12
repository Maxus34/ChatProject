<?php
namespace app\behaviors;

use app\models\Image;
use yii\base\{ Behavior, Exception };
use yii\web\UploadedFile;
use app\records\ { ImageRecord, FileRecord };


class ImageBehavior extends Behavior {
    public $placeholderPath;

    public $key = 'default';

    /**
     * @var FileRecord
     */
    protected $_mainImage;

    protected $_galleryImages = [];


    public function init() {
        parent::init();

        if (!file_exists($this->placeholderPath)) {
            throw new Exception("Placeholder image has been not found in: {$this->placeholderPath}");
        }
    }

    public function attachImage(UploadedFile $file, $is_main = false, $name = false) {
        $file_id = $this->createFileRecord($file);
        $this->createImageRecord($file_id, $is_main);
    }

    public function getMainImage() {

        if ($this->_mainImage) {
            return (new Image($this-> _mainImage -> path));
        }

        $image = ImageRecord::find()->where([
            'key' => $this->key,
            'itemId' => $this->owner->id,
            'isMain' => 1
        ])->orderBy(['id' => SORT_DESC])->one();

        if (empty($image)) {
            $image_path = $this->placeholderPath;
        } else {
            $image_path = $image->path;
        }

        $this->_mainImage = $image;

        return (new Image($image_path));
    }


    /**
     * @param  UploadedFile $file
     * @return  Integer $file_id
     */
    protected function createFileRecord(UploadedFile $file) {
        $file_record = new FileRecord($file);

        $file_record->save();

        return $file_record->id;
    }


    protected function createImageRecord($file_id, $is_main) {
        $image_record = new ImageRecord();
        $image_record->itemId = $this->owner->id;
        $image_record->fileId = $file_id;
        $image_record->isMain = $is_main;
        $image_record->key = $this->key;

        $image_record->save();
    }

}