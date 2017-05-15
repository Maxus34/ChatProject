<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 15.01.2017
 * Time: 14:46
 */

namespace app\modules\chat\records;

use app\models\User;
use phpDocumentor\Reflection\Types\Integer;
use yii\behaviors\{ TimestampBehavior, BlameableBehavior };
use yii\db\ActiveRecord;
use yii\filters\AccessControl;

/**
 * Class DialogReferenceRecord
 * @package app\modules\chat\records
 *
 * @property integer $id
 * @property integer $userId
 * @property integer $dialogId
 * @property integer $createdAt
 * @property integer $createdBy
 * @property integer $updatedAt
 * @property integer $updatedBy
 * @property integer $isActive
 * @property integer $isTyping
 */

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
                'class' => TimestampBehavior::class,
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['createdAt'],
                    ActiveRecord::EVENT_BEFORE_UPDATE  => ['updatedAt'],
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


    public function getDialog(){
        return $this->hasOne(DialogRecord::class, ['id' => 'dialogId']);
    }

    public function getUser(){
        return $this->hasOne(User::class, ['id' => 'userId']);
    }

    public function __construct(int $dialog_id = null, int $user_id = null)
    {
        parent::__construct();

        $this->dialogId  =  $dialog_id;
        $this->userId    =  $user_id;
        $this->isTyping  =  0;
    }
}