<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 17.05.2017
 * Time: 15:04
 */

namespace app\modules\chat\services;

use app\modules\chat\models\DialogProperties;
use app\modules\chat\records\ { DialogRecord, DialogReferenceRecord };
use app\modules\chat\models\    DialogN;
use yii\base\Exception;

class DialogFactory {

    protected static $_instance = null;

    protected $userId = null;


    private function __construct() {
        $this->userId = \Yii::$app->user->getId();
    }


    public static function getInstance ():DialogFactory {

        if (empty (static::$_instance)){
            static::$_instance = new static();
        }

        return static::$_instance;
    }


    public function getDialogInstanceById(int $id) {

        $dialogRecord = DialogRecord::find()->where(['id' => $id,])->with('references')->one();

        if (empty($dialogRecord))
            return false;


        $dialogReferences = $this->processDialogReferences($dialogRecord->references);
        if ( empty( $dialogReferences[ $this->userId ] ) ){
            throw new Exception('You don`t belong to this dialog');
        }

        return new DialogN($dialogRecord, $dialogReferences);
    }


    public function getDialogInstances(int $offset = null, int $limit = null) {
        $query = DialogRecord::find()
            ->innerJoin('dialog_ref', '`dialog`.`id` = `dialog_ref`.`dialogId`')
            ->where("`dialog_ref`.`userId` = {$this->userId}")
            ->with('references');

        if (!empty($offset) && ($offset < 0))
            $offset += $query->count();

        if (!empty($offset))
            $query = $query->offset($offset);
        if (!empty($limit))
            $query = $query->limit($limit);

        $dialogRecords = $query -> all();

        $dialogs = [];

        foreach ($dialogRecords as $record){

            $dialogs[] = new DialogN( $record, $this->processDialogReferences($record->references) );

        }

        return $dialogs;
    }


    public function getDialogInstancesByCondition(int $offset = null, int $limit = null, array $condition) {
        $query = DialogRecord::find()
            ->innerJoin('dialog_ref', '`dialog`.`id` = `dialog_ref`.`dialogId`')
            ->where("`dialog_ref`.`userId` = {$this->userId}")
            ->with('references');

        if (!empty($offset) && ($offset < 0))
            $offset += $query->count();

        if (!empty($offset))
            $query = $query->offset($offset);
        if (!empty($limit))
            $query = $query->limit($limit);
        if (!empty($condition))
            $query = $query->andWhere($condition);

        $dialogRecords = $query -> all();

        $dialogs = [];

        foreach ($dialogRecords as $record){

            $dialogs[] = new DialogN( $record, $this->processDialogReferences($record->references) );

        }

        return $dialogs;
    }


    public function createNewDialog() :DialogN{
        $dialogRecord = new DialogRecord();
        $dialogRecord -> save();

        $dialogReference = new DialogReferenceRecord($dialogRecord->id, $this->userId);
        $dialogReference -> save();

        $dialog = new DialogN($dialogRecord, $this->processDialogReferences([$dialogReference]));

        return $dialog;
    }


    protected function processDialogReferences (array $references) :array {
        $referencesProcessed = [];

        foreach ($references as $reference){

            if ($reference -> isActive === 1 || $reference->userId === $this->userId){
                $referencesProcessed[$reference->userId] = $reference;
            }
        }

        return $referencesProcessed;
    }

}