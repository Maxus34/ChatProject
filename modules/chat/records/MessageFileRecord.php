<?php
namespace app\modules\chat\records;

use app\modules\chat\models\MessageN;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use app\records\FileRecord;

/**
 * Class MessageFileRecord
 * @package app\modules\chat\records
 *
 * @property Integer $id
 * @property Integer $messageId
 * @property Integer $fileId
 * @property Integer $createdAt
 */
class MessageFileRecord extends ActiveRecord {

    public static function tableName() {
        return 'message_files';
    }


    public function behaviors() {
        return [
            [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['createdAt'],
                ],
            ]
        ];
    }


    public function __construct($file_id = null, $message_id = null) {
        parent::__construct();
        $this->fileId = $file_id ?? null;
        $this->messageId = $message_id ?? null;
    }


    public function getFile() {
        return $this->hasOne(FileRecord::class, ['id' => 'fileId']);
    }


    public function getMessage() {
        return $this->hasOne(MessageN::class, ['id' => 'messageId']);
    }
}