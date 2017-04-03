<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 21.03.2017
 * Time: 15:42
 */

namespace app\models\records;

use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class MessageFileRecord extends ActiveRecord
{
    static function tableName() {
        return 'message_files';
    }

    public function behaviors()
    {
        return [
          [
              'class' => TimestampBehavior::class,
              'attributes' => [
                  ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
              ],
          ]
        ];
    }

    public function __construct($file_id = null, $message_id = null)
    {
        parent::__construct();
        $this->file_id    = $file_id ?? null;
        $this->message_id = $message_id ?? null;
    }

    public function getFile()
    {
        return FileRecord::findOne($this->file_id);
    }

}