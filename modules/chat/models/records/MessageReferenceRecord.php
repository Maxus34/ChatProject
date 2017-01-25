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
}