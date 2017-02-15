<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 10.02.2017
 * Time: 22:11
 */

namespace app\modules\chat\models;

use app\modules\chat\models\records\{
    DialogRecord, DialogReferenceRecord, MessageRecord, MessageReferenceRecord
};
use app\modules\chat\models\DialogProperties;
use yii\base\{
    Component, Exception
};

class DialogBase extends Component
{
    protected $_user_id;
    protected $_dialog_record;
    protected $_dialog_references;

    const    MAX_TYPING_TIMEOUT = 4;

    static function getInstance(int $id)
    {
        $dialog_record = DialogRecord::findOne($id);
        if (empty($dialog_record))
            throw new Exception("Dialog does not exists");

        return new static($dialog_record);
    }

    static function getInstances(int $offset = null, int $limit = null, $condition = null)
    {
        $query = DialogReferenceRecord::find()->where(['user_id' => \Yii::$app->user->getId()]);

        if (!empty($offset) && ($offset < 0))
            $offset += $query->count();

        if (!empty($offset))
            $query = $query->offset($offset);
        if (!empty($limit))
            $query = $query->limit($limit);
        if (!empty($condition))
            $query = $query->andWhere($condition);

        $dialog_references = $query->all();

        //debug($dialog_references); die;

        $dialogs = [];
        foreach ($dialog_references as $reference) {
            try {
                $dialogs[] = static::getInstance($reference->dialog_id);
            } catch (Exception $e) {
                throw new Exception($e->getMessage());
            }
        }

        return $dialogs;
    }


    public function __construct(DialogRecord $dialog_rec = null, DialogProperties $properties = null)
    {
        parent::__construct();

        if (\Yii::$app->user->isGuest)
            throw new Exception("Only currently logged users can use Dialog");

        $this->_user_id = \Yii::$app->user->getId();

        if (!empty($properties) && empty($dialog_rec)) {
            $this->applyProperties($properties);
            $this->save();

        } else
            if (!empty($dialog_rec) && empty($properties)) {
                $this->_dialog_record = $dialog_rec;
                $reference = DialogReferenceRecord::findOne(['user_id' => $this->_user_id, 'dialog_id' => $this->getId()]);

                if (empty($reference))
                    throw new Exception("Error: You don't belong to this dialog");

                $this->_dialog_references[$reference->user_id] = $reference;
            }
    }

    public function getId()
    {
        return $this->_dialog_record->id;
    }

    public function getUserId()
    {
        return $this->_user_id;
    }

    public function getTitle()
    {
        return $this->_dialog_record->title;
    }

    public function getReferences(bool $exclude_me = false)
    {
        if (count($this->_dialog_references) < 2) {
            $references = $this->findDialogReferences();
        } else
            $references = $this->_dialog_references;

        if ($exclude_me) {
            unset($references[$this->_user_id]);
        }
        return $references;
    }

    public function getUsers(bool $expect_me = false){
        if (count($this->_dialog_references) < 2){
            $this->findDialogReferences();
        }

        $users = [];
        foreach ($this->_dialog_references as $reference) {
            $users[$reference->user->id] = $reference->user;
        }

        if ($expect_me){
            unset($users[$this->_user_id]);
        }

        return $users;
    }


    public function applyProperties(DialogProperties $model)
    {
        $this->_dialog_record->title = $model['title'];
        $this->updateDialogReferences($model['users']);

        $this->save();
    }

    public function getProperties(){
        $model = new DialogProperties();
        $model->title = $this->getTitle();
        $model->users = $this->getUsers(true);

        return $model;
    }


    public function save()
    {
        $this->_dialog_record->save();
        foreach ($this->_dialog_references as $reference) {
            $reference->save();
        }
    }

    public function delete()
    {
        $references = $this->getReferences();
        if (count($this->_dialog_references) > 1) {
            $this->_dialog_references[$this->_user_id]->delete();
            $message_references = MessageReferenceRecord::findAll(['dialog_id' => $this->getId(), 'user_id' => $this->getUserId()]);
            foreach ($message_references as $reference) {
                $reference->delete();
            }

        } else {
            $this->_dialog_references[$this->user_id]->delete();
            $this->_dialod_record->delete();
            $messages = MessageRecord::findAll(['dialog_id' => $this->getId()]);
            foreach ($messages as $message) {
                $message->delete();
            }
        }
    }



    protected function findDialogReferences()
    {
        $dialog_references = DialogReferenceRecord::findAll(['dialog_id' => $this->getId()]);
        foreach ($dialog_references as $reference) {
            $this->_dialog_references[$reference->user_id] = $reference;
        }
        return $this->_dialog_references;
    }

    protected function createDialogReferences(array $users)
    {
        foreach ($users as $user) {
            $reference = new DialogReferenceRecord(
                $this->getId(),
                $user->id
            );

            try {
                $reference->save();
            } catch (Exception $e) {
                // \Yii::warning("Error: {$e->getMessage()}", "message_reference");
            }

            $this->_dialog_references[$reference->user_id] = $reference;
        }
    }

    protected function updateDialogReferences(array $add)
    {
        $delete = $this->findDialogReferences();
        unset($delete[$this->getUserId()]);

        if (count($add) > 0) {
            foreach ($delete as $key => $value) {
                foreach ($add as $key1 => $add_item) {
                    if ($key == $add_item) {
                        unset($delete[$key]);
                        unset($add[$key1]);
                    }
                }
            }
        }

        foreach ($delete as $del) {
            if (!$del->delete()) {
                throw new Exception(debug($del->getErrors()));
            } else {
                unset($this->_dialog_references[$del->user_id]);
            }
        }

        $add_users = [];
        foreach ($add as $item) {
            $add_users[] = \Yii::$app->user->identity->findIdentity($item);
        }


        $this->createDialogReferences($add_users);
    }


}