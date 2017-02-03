<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 15.01.2017
 * Time: 14:38
 */

namespace app\modules\chat\models\records;

use yii\db\ActiveRecord;
use yii\behaviors\{TimestampBehavior, BlameableBehavior};

class MessageReferenceRecord extends ActiveRecord
{
    static function tableName(){
        return 'message_ref';
    }

    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],
            ],
            'blame' => [
                'class' => BlameableBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_by']
                ]
            ]
        ];
    }

    public function getMessage(){
        return $this->hasOne(MessageRecord::className(), ['id' => 'message_id']);
    }

    public function __construct(int $dialog_id = null, int $message_id = null, int $user_id = null)
    {
        parent::__construct();

        $this-> dialog_id  = $dialog_id;
        $this-> message_id = $message_id;
        $this-> user_id    = $user_id;
        $this-> is_new     = 1;
    }
}