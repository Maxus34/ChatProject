<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 20.05.2017
 * Time: 15:20
 */

namespace app\modules\chat\services;


use app\modules\chat\models\MessageN;

class MessageFilesHandler {

    protected $message;

    public function __construct (MessageN $message) {
        $this->message = $message;
    }


    public function AttachFile (int $fileId){

    }

    public function attachFiles (array $fileIds) {

    }



    public function getFiles () {

        $messageFileRecords = $this->message->messageRecord->messageFiles;

        return ;
    }

}