<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 15.01.2017
 * Time: 14:38
 */

namespace app\modules\chat\records;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\behaviors\{TimestampBehavior, BlameableBehavior};

/**
 * Class MessageReferenceRecord
 * @package app\modules\chat\models\records
 *
 * @property Integer $id
 * @property Integer $dialogId
 * @property Integer $userId
 * @property Integer $messageId
 * @property Integer $createdAt
 * @property Integer $createdBy
 * @property Integer $isNew
 *
 * @property ActiveQuery $message
 */
class MessageReferenceRecord extends ActiveRecord
{
    static function tableName(){
        return 'message_ref';
    }

    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['createdAt'],
                ],
            ],
            'blame' => [
                'class' => BlameableBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['createdBy']
                ]
            ]
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getMessage(){
        return $this->hasOne(MessageRecord::className(), ['id' => 'messageId']);
    }

    public function __construct(int $dialog_id = null, int $message_id = null, int $user_id = null)
    {
        parent::__construct();

        $this-> dialogId  = $dialog_id;
        $this-> messageId = $message_id;
        $this-> userId    = $user_id;
        $this-> isNew     = 1;
    }
}