<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 15.01.2017
 * Time: 14:42
 */

namespace app\modules\chat\records;


use yii\db\ActiveRecord;
use yii\behaviors\{
    BlameableBehavior, TimestampBehavior
};

/**
 * @property  Integer $id
 * @property  String  $title
 * @property  Integer $createdAt
 * @property  Integer $createdBy
 */
class DialogRecord extends ActiveRecord
{
    /**
     * @property Integer $id
     */

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


    public static function tableName()
    {
        return 'dialog';
    }

    public function __construct($title = null)
    {
        parent::__construct();

        $this->title = $title;
    }

    public function getReferences()
    {
        return $this->hasMany(DialogReferenceRecord::className(), ['dialogId' => $this->id]);
    }
}