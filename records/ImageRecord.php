<?php

namespace app\records;

use Imagine\Image\Box;
use Imagine\Imagick\Imagine;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/* @property integer $id*/
/* @property integer itemId*/
/* @property integer $fileId*/
/* @property boolean $isMain*/
/* @property string  $key*/
/* @property integer $createdAt*/
class ImageRecord extends ActiveRecord
{
    protected $_fileRecord = false;


    static function tableName()
    {
        return "images";
    }


    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_AFTER_INSERT => 'createdAt',
                ]
            ]
        ];
    }


    public function getFile(){
        return $this->hasOne(FileRecord::class, ['id' => 'fileId']);
    }

    public function getPath(){

        if (!$this->_fileRecord){
            $this->_fileRecord = FileRecord::findOne(['id' => $this->fileId]);
        }

        return $this->_fileRecord->path;
    }

}