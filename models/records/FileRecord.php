<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 13.03.2017
 * Time: 17:06
 */

namespace app\models\records;

use yii\behaviors\{ TimestampBehavior, BlameableBehavior };
use yii\db\ActiveRecord;

class FileRecord extends ActiveRecord
{
    static function tableName()
    {
        return "files";
    }

    public function behaviors(){
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],
            ],
            'blame' => [
                'class' => BlameableBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_by']
                ]
            ]
        ];
    }


}