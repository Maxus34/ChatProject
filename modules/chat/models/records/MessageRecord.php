<?php

namespace app\modules\chat\models\records;


use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

class MessageRecord extends ActiveRecord
{
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ]
            ]
        ];
    }


    static function tableName()
    {
        return 'message';
    }


}