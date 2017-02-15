<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 15.01.2017
 * Time: 14:46
 */

namespace app\modules\chat\models\records;

use app\models\User;
use yii\behaviors\{ TimestampBehavior, BlameableBehavior };
use yii\db\ActiveRecord;
use yii\filters\AccessControl;

class DialogReferenceRecord extends ActiveRecord
{
    static function tableName()
    {
        return 'dialog_ref';
    }

    public function behaviors()
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE  => ['updated_at'],
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

    public function __construct(int $dialog_id = null, int $user_id = null)
    {
        parent::__construct();

        $this->dialog_id  =  $dialog_id;
        $this->user_id    =  $user_id;
        $this->is_typing  =  0;
    }
}