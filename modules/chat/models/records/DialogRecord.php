<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 15.01.2017
 * Time: 14:42
 */

namespace app\modules\chat\models\records;


use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;
class DialogRecord extends ActiveRecord
{
    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                ],
            ]
        ];
    }


    public static function tableName(){
        return 'dialog';
    }
}