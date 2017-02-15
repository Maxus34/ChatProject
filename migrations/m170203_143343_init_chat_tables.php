<?php

use yii\db\Migration;

class m170203_143343_init_chat_tables extends Migration
{
    public function up()
    {

        $this->createTable('dialog',[
            'id'         => $this->primaryKey(11)->unsigned(),
            'title'      => $this->string(20),
            'created_at' => $this->integer(11)->unsigned()->notNull(),
            'created_by' => $this->integer(11)->unsigned()->notNull(),
        ]);
        $this->createTable('dialog_ref', [
            'id'         => $this->primaryKey(11)->unsigned(),
            'user_id'    => $this->integer(11)->unsigned()->notNull(),
            'dialog_id'  => $this->integer(11)->unsigned()->notNull(),
            'created_at' => $this->integer(11)->unsigned()->notNull(),
			'created_by' => $this->integer(11)->unsigned()->notNull(),
            'is_typing'  => $this->boolean()
        ]);

        $this->createIndex(
            'idx-dialog_ref-user_id',
            'dialog_ref',
            'user_id'
        ); //dialog_ref . user_id
        $this->createIndex(
            'idx-dialog_ref-dialog_id',
            'dialog_ref',
            'dialog_id'
        ); //dialog_ref . dialog_id


        $this->createTable('message',[
            'id'         => $this->primaryKey(11)->unsigned(),
            'dialog_id'  => $this->integer(11)->unsigned()->notNull(),
            'content'    => $this->text()->notNull(),
            'created_at' => $this->integer(11)->unsigned()->notNull(),
            'created_by' => $this->integer(11)->unsigned()->notNull()
        ]);
        $this->createTable('message_ref', [
            'id'         => $this->primaryKey(11)->unsigned(),
            'dialog_id'  => $this->integer(11)->unsigned()->notNull(),
            'user_id'    => $this->integer(11)->unsigned()->notNull(),
            'message_id' => $this->integer(11)->unsigned()->notNull(),
            'created_at' => $this->integer(11)->unsigned()->notNull(),
            'created_by' => $this->integer(11)->unsigned()->notNull(),
            'is_new'     => $this->boolean()
        ]);

        $this->createIndex(
            'idx-message_ref-user_id',
            'message_ref',
            'user_id'
        ); //message_ref . user_id
        $this->createIndex(
            'idx-message_ref-dialog_id',
            'message_ref',
            'dialog_id'
        ); //message_ref . dialog_id
        $this->createIndex(
            'idx-message_ref-message_id',
            'message_ref',
            'message_id'
        ); //message_ref . message_id


        $this->addForeignKey(
            'fk-dialog_ref-dialog-id',
            'dialog_ref',
            'dialog_id',
            'dialog',
            'id',
            'CASCADE', 'CASCADE'
        ); // `dialog_ref`.`dialog_id`   ==> `dialog`.`id` CASCADE
        $this->addForeignKey(
            'fk-message-dialog-id',
            'message',
            'dialog_id',
            'dialog',
            'id',
            'CASCADE', 'CASCADE'
        ); // `message`.`dialog_id`      ==> `dialog`.`id` CASCADE
        $this->addForeignKey(
            'fk-message_ref-message-id',
            'message_ref',
            'message_id',
            'message',
            'id',
            'CASCADE', 'CASCADE'
        ); // `message_ref`.`message_id` ==> `message`.`id` CASCADE
        $this->addForeignKey(
            'fk-dialog_ref-user_id',
            'dialog_ref',
            'user_id',
            'user',
            'id',
            'CASCADE', 'CASCADE'
        ); // `dialog_ref`.`user_id`     ==> `user`.`id` CASCADE
        $this->addForeignKey(
            'fk-message_ref-user-id',
            'message_ref',
            'user_id',
            'user',
            'id',
            'CASCADE', 'CASCADE'
        ); // `message_ref`.`user_id`    ==> `user`.`id` CASCADE

    }

    public function down()
    {
        $this->dropTable('dialog');
        $this->dropTable('dialog_ref');
        $this->dropTable('message');
        $this->dropTable('message_ref');
    }
}
