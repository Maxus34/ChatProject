<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 13.03.2017
 * Time: 17:06
 */

namespace app\records;

use yii\behaviors\{ TimestampBehavior, BlameableBehavior };
use yii\db\ActiveRecord;
use yii\web\UploadedFile;

class FileRecord extends ActiveRecord
{
    const  FILES_PATH    =  'upload';
    const  IMAGES_PATH   =  'images';
    const  AUDIO_PATH    =  'audios';
    const  DEFAULT_PATH  =  'files';


    const  TYPE_IMAGE    =  'image';
    const  TYPE_AUDIO    =  'audio';
    const  TYPE_FILE     =  'file';


    /*
     * @var UploadedFile uploaded file
     */
    protected $uploaded_file;

    public static function tableName()
    {
        return "files";
    }

    public function behaviors(){
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['createdAt'],
                ],
            ],
            'blame' => [
                'class' => BlameableBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['createdBy']
                ]
            ]
        ];
    }

    public function __construct(UploadedFile $file = null)
    {
        parent::__construct(null);

        if (!empty($file) && $this->isNewRecord){
            $this->uploaded_file = $file;

            $this->name      = $file->baseName;
            $this->extension = $file->extension;
            $this->type      = $file->type;
            $this->size      = $file->size;
        }
    }

    public function beforeSave($insert)
    {
        $path = $this->generateFilePath($this->uploaded_file);

        do {
            $name = $this->generateRandomFileName();
            $file = $path . '/' . $name . '.' . $this->uploaded_file->extension;

        } while (file_exists($file));

        $this->uploaded_file->saveAs($_SERVER['DOCUMENT_ROOT'] . "/web/" . $file);

        $this-> path = $file;

        return parent::beforeSave($insert);
    }


    protected function generateFilePath($file){
        $path = static::FILES_PATH;

        switch(preg_split('/\//', $file->type)[0]){
            case static::TYPE_IMAGE :
                $path .= '/' . static::IMAGES_PATH;
                break;

            case static::TYPE_AUDIO :
                $path .= '/' . static::AUDIO_PATH;
                break;

            default:
                $path .= '/' . static::DEFAULT_PATH;
        }

        if (!file_exists($path)){
            mkdir($path);
        }

        return $path;
    }

    protected function generateRandomFileName(){
        return md5(microtime() . rand(0, 9999));
    }
}