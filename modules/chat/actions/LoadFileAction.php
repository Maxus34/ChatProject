<?php

namespace app\modules\chat\actions;

use Faker\Provider\File;
use yii\base\Action;
use app\records\FileRecord;
use yii\web\UploadedFile;
use yii\helpers\Json;

class LoadFileAction extends Action
{
    const FILES_PATH = '/upload/files/';

    public function run(){
        $result = [];

        $file = UploadedFile::getInstanceByName('file');

        if (empty($file)) {
            $result['file'] = null;
            $result['error'] = true;

        } else {
            $file_record = new FileRecord($file);
            $file_record -> save();

            $result['error']         = false;
            $result['file']['id']    = $file_record->id;
            $result['file']['name']  = $file_record->name;
        }


        return Json::encode($result);
    }

}