<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 15.01.2017
 * Time: 14:38
 */

namespace app\modules\chat\models\records;

use yii\db\ActiveRecord;

class MessageReferenceRecord extends ActiveRecord
{
    static function tableName(){
        return 'message_ref';
    }

    public function getMessage(){
        return $this->hasOne(MessageRecord::className(), ['id' => 'message_id']);
    }

    public function __construct(int $dialog_id = null, int $message_id = null, int $user_id = null, int $is_author = null)
    {
        parent::__construct();

        $this-> dialog_id  = $dialog_id;
        $this-> message_id = $message_id;
        $this-> user_id    = $user_id;
        $this-> is_author  = $is_author;
        $this-> is_new     = 1;
    }
}