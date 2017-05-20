<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 21.03.2017
 * Time: 15:28
 */

namespace app\behaviors;

use app\records\FileRecord;
use app\modules\chat\records\MessageFileRecord;
use yii\base\Behavior;
use yii\db\ActiveRecord;

class AttachedFileBehavior extends Behavior
{
    const TYPE_IMAGE = "image";
    const TYPE_AUDIO = "audio";
    const TYPE_FILE  = "file";

    /**
     * @var FileRecord
     */
    protected $files = [];


    /** @var array|MessageFileRecord */
    protected $fileReferences = [];


    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_DELETE => 'deleteFiles',
        ];
    }


    public function attachFile($fileId){
        $message_file = new MessageFileRecord($fileId, $this->owner->id);
        $message_file -> save();
    }


    public function attachFiles(array $files){
        foreach ($files as $file){
            $this->attachFile($file);
        }
    }


    public function getFiles(){
        $this->findFiles();

        $files_arr = array();

        foreach ($this->files as $file) {
            switch(preg_split('/\//', $file->type)[0]){
                case static::TYPE_IMAGE :
                    $files_arr[static::TYPE_IMAGE][] = $file;
                    break;

                case static::TYPE_AUDIO :
                    $files_arr[static::TYPE_AUDIO][] = $file;
                    break;

                default:
                    $files_arr[static::TYPE_FILE][] = $file;
            }
        }

        return $files_arr;
    }


    public function deleteFiles(){
        $this->findFiles();

        foreach ($this->files as $file){
            $file->delete();
        }
    }


    protected function findFiles(){

        $this->fileReferences = $this->owner->fileReferences;

        foreach ($this->fileReferences as $reference){
            $this->files[] = $reference->file;
        }
    }

}