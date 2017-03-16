<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 13.03.2017
 * Time: 17:11
 */

namespace app\models\records;


use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;
use yii\base\Exception;

/* @property $user_id */
/* @property $file_id */
/* @property $is_main */
/* @property $created_at */

class UserImageRecord extends ActiveRecord
{
    const IMAGE_PATH = 'upload/images/user/';

    public $imageFile;
    public $is_main = 0;

    static function tableName()
    {
        return "user_images";
    }

    public function rules(){
        return [
            [['is_main'], 'safe'],
            [['user_id'], 'safe'],
            [['imageFile'], 'file', 'skipOnEmpty' => true, 'extensions' => 'png, jpg'],
        ];
    }

    public function behaviors(){
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],
            ]
        ];
    }

    public function getMainImage(){
        return FileRecord::findOne(['id' => $this->file_id]);
        //return $this->hasOne(FileRecord::class, ['id' => $this->file_id]);
    }

    public function getImages(){
        return $this->hasMany(FileRecord::class, ['user_id' => $this->user_id]);
    }

    public function upload() {
        if ($this->validate()){
            $savePath = static::IMAGE_PATH . $this->imageFile->baseName . "." . $this->imageFile -> extension;
            $this->createUserImageRecord($this->createFileRecord($this->imageFile, $savePath));

            $this->imageFile->saveAs($savePath);

            if ($this->imageFile->error != 0){
                throw new Exception("Error loading files");
                return;
            }



        } else {
            debug($this->getErrors()); die();
        }
    }

    protected function createFileRecord(UploadedFile $imageFile, $savePath){
        $file = new FileRecord();

        $file->name      = $imageFile->baseName;
        $file->extension = $imageFile->extension;
        $file->type      = $imageFile->type;
        $file->size      = $imageFile->size;
        $file->type      = $imageFile->type;
        $file->path      = $savePath;

        $file->save();

        return $file->id;
    }

    protected function createUserImageRecord($file_id){
        if (empty($file_id))
            return;

        $this->file_id = $file_id;
        $this->save();
    }
}