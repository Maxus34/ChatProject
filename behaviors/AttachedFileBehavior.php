<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 21.03.2017
 * Time: 15:28
 */

namespace app\behaviors;

use app\models\records\FileRecord;
use app\models\records\MessageFileRecord;
use yii\base\Behavior;
use yii\db\ActiveRecord;

class AttachedFileBehavior extends Behavior
{
    const TYPE_IMAGE = "image";
    const TYPE_AUDIO = "audio";
    const TYPE_FILE  = "file";

    protected $files = [];

    public function events()
    {
        return [
            ActiveRecord::EVENT_AFTER_DELETE => 'deleteFiles',
        ];
    }

    /*
     *
     */
    public function attachFile($file_id){
        $message_file = new MessageFileRecord($file_id, $this->owner->id);
        $message_file -> save();

        $this->owner->setHasFiles(1);
        $this->owner->save();
    }

    /*
     *
     */
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


    protected function deleteFiles(){
        $this->findFiles();

        foreach ($this->files as $file){
            $file->delete();
        }
    }

    protected function findFiles(){
        if ($this->owner->getHasFiles() == 0){
            return [];
        }

        $m_files = MessageFileRecord::find()
            ->where(['message_id' => $this->owner->id])
            ->all();

        foreach ($m_files as $m_file){
            $this->files[] = $m_file->getFile();
        }
    }

}