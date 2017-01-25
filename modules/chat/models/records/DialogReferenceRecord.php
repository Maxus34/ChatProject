<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 15.01.2017
 * Time: 14:46
 */

namespace app\modules\chat\models\records;

use app\models\User;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;

class DialogReferenceRecord extends ActiveRecord
{

    public static function tableName()
    {
        return 'dialog_ref';
    }

    public function rules(){
        return [
            [['id', 'dialog_id', 'user_id' ],'number'],
            [['is_typing', 'is_creator'], 'safe'],
        ];
    }

    public function getDialog(){
        return $this->hasOne(DialogRecord::className(), ['id' => 'dialog_id']);
    }

    public function getUser(){
        return $this->hasOne(User::className(), ['id' => 'user_id']);
    }
}