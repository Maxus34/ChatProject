<?php

namespace app\modules\chat\records;

use yii\db\ActiveRecord;
use yii\behaviors\{TimestampBehavior, BlameableBehavior};
use app\behaviors\AttachedFileBehavior;

/**
 * Class MessageRecord
 * @package app\modules\chat\models\records
 *
 * @property Integer $id
 * @property Integer $dialogId
 * @property String  $content
 * @property Integer $createdAt
 * @property Integer $createdBy
 */
class MessageRecord extends ActiveRecord
{
    static function tableName()
    {
        return 'message';
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
            ],
            'attachedFile' => [
                'class' => AttachedFileBehavior::class,
            ],
        ];
    }

    public function __construct(int $dialog_id = null, string $content = null)
    {
        parent::__construct();

        $this->dialogId = $dialog_id;
        $this->content   = $content;
    }

    public function getReferences(){
        return $this->hasMany(MessageReferenceRecord::className(), ['messageId' => $this->id]);
    }
}