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
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

/* @property integer $id*/
/* @property integer item_id*/
/* @property integer $file_id*/
/* @property boolean $is_main*/
/* @property string  $key*/
/* @property integer $created_at*/
class ImageRecord extends ActiveRecord
{
    protected $_file_record = false;

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
                    ActiveRecord::EVENT_AFTER_INSERT => 'created_at',
                ]
            ]
        ];
    }

    public function getPath(){

        if (!$this->_file_record){
            $this->_file_record = FileRecord::findOne(['id' => $this->file_id]);
        }

        return $this->_file_record->path;
    }

}