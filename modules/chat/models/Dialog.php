<?php
/**
 * Created by PhpStorm.
 * User: MXS34
 * Date: 15.01.2017
 * Time: 14:48
 */

namespace app\modules\chat\models;

use app\models\User;
use app\modules\chat\models\records\MessageReferenceRecord;
use yii\base\Exception;
use yii\base\Model;
use app\modules\chat\models\records\DialogRecord;
use app\modules\chat\models\records\DialogReferenceRecord;

class Dialog extends Model
{
    private  $user_id            = null;
    private  $dialog_record      = null;
    private  $dialog_references  =   [];

    static function getDialogInstance(int $dialog_id = null){
        if (empty($dialog_record = DialogRecord::findOne($dialog_id)))
            throw new Exception("Error: Dialog don't exists.");

        return new static($dialog_record);
    }

    static function getDialogInstances(int $offset = null, int $limit = null){

        $query = DialogReferenceRecord::find()->where(['user_id' => \Yii::$app->user->getId()]);

        if ($offset < 0){
            $count = $query->count();
            $offset += $count;
        }

        if ( !empty( $offset) )
            $query =  $query -> offset($offset);
        if ( !empty( $limit) )
            $query =  $query -> limit($limit);

        $dialog_reference_records = $query -> with('dialog') -> all();
        $dialogs = [];

        foreach ($dialog_reference_records as $reference){
            $dialogs[] = new static($reference->dialog);
        }

        return $dialogs;
    }


    public function __construct(DialogRecord $dr = null, string $title = null, array $users = null)
    {
        parent::__construct();
        $this->user_id = \Yii::$app->user->getId();

        if (empty($dr))
        {
            if (empty($title))
                $title = 'Dialog_' . \Yii::$app->formatter->asDate(new \DateTime(), "php:d F");

            $this->dialog_record  = new DialogRecord($title);
            $this->dialog_record -> save();

            $this->createDialogReferences($users);

        } else {

            $this->dialog_record = $dr;

            $reference = DialogReferenceRecord::find()->where(['user_id' => $this->user_id, 'dialog_id' => $this->getId()])->one();
            if (empty($reference))
                throw new Exception("Error: You don't belong to this dialog");

            $this->dialog_references[$reference->user_id] = $reference;
        }
    }

    public function getId(){
        return $this->dialog_record->id;
    }

    public function getUserId(){
        return $this->user_id;
    }

    public function getTitle(){
        return $this->dialog_record->title;
    }

    public function getUsers(bool $expect_me = false){
        if (count($this->dialog_references) < 2){
            $this->findDialogReferences();
        }

        $users = [];
        foreach ($this->dialog_references as $reference) {
            $users[$reference->user->id] = $reference->user;
        }

        if ($expect_me){
            unset($users[$this->user_id]);
        }

        return $users;
    }

    public function getTypingUsers(){
        if (count($this->dialog_references) <= 1){
            $this->findDialogReferences();
        }

        $users_array = [];
        foreach ($this->dialog_references as $reference){
            if ( ($reference->is_typing) && ($reference->user_id != $this->user_id) ){
                $users_array[] = $reference->user->username;
            }
        }

        return $users_array;
    }

    public function getMessages(int $offset = null, int $limit = null, array $conditions = null){

        return Message::getMessagesInstances($this->user_id, $this->getId(), $offset, $limit, $conditions);

    }

    public function getMessagesCount($new = false){
        $query = MessageReferenceRecord::find() -> where(['dialog_id' => $this->getId(), 'user_id' => $this->user_id]);

        if ($new)
            $query = $query -> andWhere(["is_new"  => 1]) -> andWhere(['!=', 'created_by', $this->user_id]);

        return $query->count();
    }

    public function getIsSeenMessages(array $messages){
        if (empty($messages))
            throw new Exception("setSeenMessages => Empty messages array");

        return Message::getIsSeenMessages($this->user_id, $this->getId(), $messages);
    }

    public function addMessage($content){
        try{

            $message = new Message(null, $this, $content);
            $message -> save();

        } catch (Exception $e){
            debug ($e->getMessage());
            die();
        }

        return $message;
    }


    public function setDialogAttributes ($model){
        if(!empty($model['title']))
            $this->dialog_record->title = $model['title'];

        $this->updateDialogReferences($model['users'] ?? []);

        $this->save();
    }

    public function setSeenMessages (array $messages = null){
        if (empty($messages))
            throw new Exception("setSeenMessages => Empty messages array");

        return Message::setSeenMessages($this->getId(), $messages);
    }

    public function setIsTyping($is_typing){
         if ( !isset($this->dialog_references[$this->user_id]) )
             $reference = DialogReferenceRecord::findOne(['user_id' => $this->user_id, 'dialog_id' => $this->getId()]);
         else
             $reference = $this -> dialog_references[$this->user_id];

         $reference -> is_typing = $is_typing ? 1 : 0;
         $reference -> save();
    }


    public function save(){
        $this->dialog_record->save();
        foreach($this->dialog_references as $reference){
            $reference->save();
        }
    }

    public function delete(){
        $this->findDialogReferences();
        if (count($this->dialog_references) > 1){
            $this->dialog_references[$this->user_id]->delete();
            //TODO delete message_records for deleted dialog_user (?);
        } else {
            $this->dialog_references[$this->user_id]->delete();
            $this->dialod_record->delete();
            //TODO delete messages for this dialog (!);
        }
    }


    private function findDialogReferences() :array{
        $dialog_references = DialogReferenceRecord::find()->where(['dialog_id' => $this->getId()])->with('user')->all();

        foreach($dialog_references as $reference){
            $this->dialog_references[$reference->user_id] = $reference;
        }
        return $this->dialog_references;
    }

    private function createDialogReferences(array $users){
        foreach ($users as $user){
            $ref = new DialogReferenceRecord(
                $this->getId(),
                $user->id
            );

            try{
                $ref->save();
            } catch (Exception $e){
                // \Yii::warning("Error: {$e->getMessage()}", "message_reference");
            }

            $this->dialog_references[$ref->user_id] = $ref;
        }
    }

    private function updateDialogReferences(array $add){
        $delete = $this->findDialogReferences();
        unset($delete[$this->getUserId()]);

        if (count($add) > 0){
            foreach ($delete as $key => $value){
                for ($i = 0; $i < count($add); $i++){
                    if ($key == $add[$i]){
                        unset($delete[$key]);
                        unset($add[$i]);
                    }
                }
            }
        }

        foreach($delete as $del){
            if (!$del->delete()){
                throw new Exception(debug($del->getErrors()));
            } else {
                unset($this->dialog_references[$del->user_id]);
            }
        }

        $add_users = [];
        foreach ($add as $item){
            $add_users[] = \Yii::$app->user->identity->findIdentity($item);
        }


        $this->createDialogReferences($add_users);
    }

}


