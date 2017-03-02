<?php

namespace app\modules\chat\models\records;


use yii\db\ActiveRecord;
use yii\behaviors\{TimestampBehavior, BlameableBehavior};

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

    public function __construct(int $dialog_id = null, string $content = null)
    {
        parent::__construct();

        $this->dialog_id = $dialog_id;
        $this->content   = $content;
    }

    public function getReferences($dialog_id = null){
        if (!empty($dialog_id)){
            return $this->hasMany(MessageReferenceRecord::className(), ['message_id' => $this->id, 'dialog_id' => $dialog_id]);
        } else {
            return $this->hasMany(MessageReferenceRecord::className(), ['message_id' => $this->id]);
        }

    }
}