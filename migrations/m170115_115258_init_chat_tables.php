<?php

use yii\db\Migration;
use yii\db\Schema;

class m170115_115258_init_chat_tables extends Migration
{
    public function up()
    {
        $this->createTable('dialog',[
            'id'         => $this->primaryKey(11)->unsigned(),
            'title'      => $this->string(20)->notNull(),
            'created_at' => $this->timestamp()
        ]);

        $this->createTable('dialog_ref', [
            'id'         => $this->primaryKey(11)->unsigned(),
            'user_id'    => $this->integer(11)->unsigned()->notNull(),
            'dialog_id'  => $this->integer(11)->unsigned()->notNull(),
            'is_typing'  => $this->boolean(),
            'is_creator' => $this->boolean()
        ]);

        $this->createIndex(
            'idx-dialog_ref-user_id',
            'dialog_ref',
            'user_id'
        );
        $this->createIndex(
            'idx-dialog_ref-dialog_id',
            'dialog_ref',
            'dialog_id'
        );


        $this->createTable('message',[
            'id'         => $this->primaryKey(11)->unsigned(),
            'dialog_id'  => $this->primaryKey(11)->unsigned(),
            'content'    => $this->text()->notNull(),
            'created_at' => $this->timestamp()
        ]);

        $this->createTable('message_ref', [
            'id'         => $this->primaryKey(11)->unsigned(),
            'dialog_id'  => $this->primaryKey(11)->unsigned(),
            'user_id'    => $this->integer(11)->unsigned()->notNull(),
            'message_id'  => $this->integer(11)->unsigned()->notNull(),
            'is_author'  => $this->boolean(),
            'is_new' => $this->boolean()
        ]);

        $this->createIndex(
            'idx-message_ref-user_id',
            'message_ref',
            'user_id'
        );
        $this->createIndex(
            'idx-message_ref-message_id',
            'message_ref',
            'message_id'
        );
        $this->createIndex(
            'idx-message_ref-dialog_id',
            'message_ref',
            'dialog_id'
        );

        $this->addForeignKey(
            'fk-dialog-dialog_user-id',
            'dialog_ref',
            'dialog_id',
            'dialog',
            'id',
            'CASCADE', 'CASCADE'
        );
        $this->addForeignKey(
            'fk-message-message_ref-id',
            'message_ref',
            'message_id',
            'message',
            'id',
            'CASCADE', 'CASCADE'
        );
    }

    public function down()
    {

    }
}
