<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 10.02.2017
 * Time: 22:11
 */

namespace app\modules\chat\models;

use app\modules\chat\records\{
    DialogRecord, DialogReferenceRecord, MessageRecord, MessageReferenceRecord
};
use app\modules\chat\models\DialogProperties;
use yii\base\{
    Component, Exception
};

/**
 * Class DialogBase
 * @package app\modules\chat\models
 *
 */
class DialogBase extends Component
{
    const    MAX_TYPING_TIMEOUT = 4;

    /**
     * @var Integer
     */
    protected $_userId;

    /**
     * @var DialogRecord
     */
    protected $dialogRecord;

    /**
     * @var array
     */
    protected $dialogReferences = [];



    public static function getInstance(int $id)
    {
        $dialog_record = DialogRecord::findOne($id);
        if (empty($dialog_record))
            throw new Exception("Dialog does not exists");

        return new static($dialog_record);
    }


    public static function getInstances(int $offset = null, int $limit = null, $condition = null)
    {
        $query = DialogReferenceRecord::find()
            ->where(['userId' => \Yii::$app->user->getId()])
            ->with('dialog');

        if (!empty($offset) && ($offset < 0))
            $offset += $query->count();

        if (!empty($offset))
            $query = $query->offset($offset);
        if (!empty($limit))
            $query = $query->limit($limit);
        if (!empty($condition))
            $query = $query->andWhere($condition);

        $dialog_references = $query->all();

        $dialogs = [];
        foreach ($dialog_references as $reference) {
            try {
                $dialogs[] = new static($reference->dialog);
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        }

        return $dialogs;
    }


    public static function getNewInstance(DialogProperties $properties){
        return new static(null, $properties);
    }




    public function __construct(DialogRecord $dialog_rec = null, DialogProperties $properties = null)
    {
        parent::__construct();

        $this->_userId = \Yii::$app->user->getId();

        // Creating new Dialog from new DialogProperties
        if (!empty($properties) && empty($dialog_rec)) {
            $this->applyProperties($properties);

        } else

            // Getting existing Dialog
            if (!empty($dialog_rec)) {
                $this->dialogRecord = $dialog_rec;

                $this->findDialogReferences();
            }
    }


    public function getId()
    {
        return $this->dialogRecord->id;
    }


    public function getUserId()
    {
        return $this->_userId;
    }


    public function getTitle()
    {
        return $this->dialogRecord->title;
    }


    public function getReferences(bool $exclude_me = false)
    {
        $references = $this->dialogReferences;
        if ($exclude_me) {
            unset($references[$this->_userId]);
        }
        return $references;
    }


    public function getUsers(bool $exclude_me = false)
    {
        $users = [];
        foreach ($this->dialogReferences as $reference) {
            $users[$reference->user->id] = $reference->user;
        }

        if ($exclude_me) {
            unset($users[$this->_userId]);
        }

        return $users;
    }


    public function isActive(){
        return $this->dialogReferences[$this->getUserId()] -> isActive;
    }


    public function isCreator ($user_id = false){
        if (!$user_id){
            return $this->dialogRecord->createdBy == $this->_userId;
        }

        return $this->dialogRecord->createdBy == $user_id;
    }


    public function applyProperties(DialogProperties $model)
    {
        if (empty($this->dialogRecord)) {
            $this->dialogRecord = new DialogRecord($model->title);
            $this->dialogRecord->save();

            // Creating a reference for current user
            $reference  = new DialogReferenceRecord($this->getId(), \Yii::$app->user->getId());
            $reference -> isActive = 1;
            $reference -> save();
            $this -> dialogReferences[\Yii::$app->user->getId()] = $reference;

        } else {
            $this->dialogRecord->title = $model->title;
            $this->dialogRecord->save();
        }


        $this->updateDialogReferences($model->users);

        $this->save();
    }

    public function getProperties()
    {
        $model = new DialogProperties();
        $model->title = $this->getTitle();
        $model->users = $this->getUsers(true);

        return $model;
    }


    public function save()
    {
        $this->dialogRecord->save();
        foreach ($this->dialogReferences as $reference) {
            $reference->save();
        }
    }

    public function delete()
    {
        $this->getReferences();

        if (count($this->dialogReferences) > 1) {
            $this->dialogReferences[$this->_userId]->delete();
            $message_references = MessageReferenceRecord::findAll(['dialogId' => $this->getId(), 'userId' => $this->getUserId()]);
            foreach ($message_references as $reference) {
                $reference->delete();
            }

        } else {
            $this->dialogReferences[$this->_userId]->delete();
            $this->dialogRecord->delete();
            $messages = MessageRecord::findAll(['dialogId' => $this->getId()]);
            foreach ($messages as $message) {
                $message->delete();
            }
        }
    }


    protected function findDialogReferences()
    {
        $dialog_references = DialogReferenceRecord::findAll(['dialogId' => $this->getId(), 'isActive' => 1]);
        foreach ($dialog_references as $reference) {
            $this->dialogReferences[$reference->userId] = $reference;
        }
        return $this->dialogReferences;
    }

    protected function createDialogReferences(array $users)
    {
        foreach ($users as $user) {

            $reference = DialogReferenceRecord::find()->where([
                'dialogId' => $this->getId(),
                'userId' => $user->id,
                'isActive' => 0
            ])->one();


            if (!empty($reference)) {
                $reference->isActive = 1;
                $reference->save();

            } else {
                $reference = new DialogReferenceRecord(
                    $this->getId(),
                    $user->id
                );


                $reference->save();
            }

            $this->dialogReferences[$reference->userId] = $reference;
        }
    }

    protected function updateDialogReferences(array $users_persist)
    {
        $delete = $this->findDialogReferences();
        unset($delete[$this->getUserId()]);

        if (count($users_persist) > 0 && !empty($delete)) {
            foreach ($delete as  $dkey => $value) {
                foreach ($users_persist as  $akey => $add_item) {
                    if ($dkey == $add_item) {
                        unset($delete[$dkey]);
                        unset($users_persist[$akey]);
                    }
                }
            }
        }


        if (!empty($delete)) {
            $this->deactivateReferences($delete);
        }


        $add_users = [];
        foreach ($users_persist as $id) {
            $add_users[] = \Yii::$app->user->identity->findIdentity($id);
        }

        $this->createDialogReferences($add_users);
    }

    protected function deactivateReferences(array $references)
    {
        foreach ($references as $ref) {

            if ($ref->createdBy == $this->getUserId()
                || $this->isCreator($this->getUserId()))
            {
                $ref->isActive = 0;
                $ref->save();
                unset($this->dialogReferences[$ref->userId]);
            }
        }
    }
}