<?php

use yii\db\Migration;

class m170509_155318_init_chat_tables extends Migration
{
    public function up()
    {
        $this->createTable('dialog',[
            'id'         => $this->primaryKey(11)->unsigned(),
            'title'      => $this->string(20),
            'createdAt'  => $this->integer(11)->unsigned()->notNull(),
            'createdBy'  => $this->integer(11)->unsigned()->notNull()
        ]); // table `dialog`
        $this->createTable('dialog_ref', [
            'id'        => $this->primaryKey(11)->unsigned(),
            'userId'    => $this->integer(11)->unsigned()->notNull(),
            'dialogId'  => $this->integer(11)->unsigned()->notNull(),
            'createdAt' => $this->integer(11)->unsigned()->notNull(),
            'createdBy' => $this->integer(11)->unsigned()->notNull(),
            'updatedAt' => $this->integer(11)->unsigned()->notNull(),
            'updatedBy' => $this->integer(11)->unsigned()->notNull(),
            'isActive'  => $this->boolean()->defaultValue(1),
            'isTyping'  => $this->boolean()
        ]); // table `dialog_ref

        $this->createIndex(
            'idx-dialog_ref-user_id',
            'dialog_ref',
            'userId'
        ); //dialog_ref . user_id
        $this->createIndex(
            'idx-dialog_ref-dialog_id',
            'dialog_ref',
            'dialogId'
        ); //dialog_ref . dialog_id


        $this->createTable('message',[
            'id'         => $this->primaryKey(11)->unsigned(),
            'dialogId'  => $this->integer(11)->unsigned()->notNull(),
            'content'    => $this->text()->notNull(),
            'createdAt' => $this->integer(11)->unsigned()->notNull(),
            'createdBy' => $this->integer(11)->unsigned()->notNull()
        ]); // table `message`
        $this->createTable('message_ref', [
            'id'         => $this->primaryKey(11)->unsigned(),
            'dialogId'  => $this->integer(11)->unsigned()->notNull(),
            'userId'    => $this->integer(11)->unsigned()->notNull(),
            'messageId' => $this->integer(11)->unsigned()->notNull(),
            'createdAt' => $this->integer(11)->unsigned()->notNull(),
            'createdBy' => $this->integer(11)->unsigned()->notNull(),
            'isNew'     => $this->boolean()
        ]); // table `message_ref`

        $this->createIndex(
            'idx-message_ref-user_id',
            'message_ref',
            'userId'
        ); //message_ref . userId
        $this->createIndex(
            'idx-message_ref-dialog_id',
            'message_ref',
            'dialogId'
        ); //message_ref . dialogId
        $this->createIndex(
            'idx-message_ref-message_id',
            'message_ref',
            'messageId'
        ); //message_ref . messageId


        $this->addForeignKey(
            'fk-dialog_ref-dialog-id',
            'dialog_ref',
            'dialogId',
            'dialog',
            'id',
            'CASCADE', 'CASCADE'
        ); // `dialog_ref`.`dialog_id`   ==> `dialog`.`id` CASCADE
        $this->addForeignKey(
            'fk-message-dialog-id',
            'message',
            'dialogId',
            'dialog',
            'id',
            'CASCADE', 'CASCADE'
        ); // `message`.`dialog_id`      ==> `dialog`.`id` CASCADE
        $this->addForeignKey(
            'fk-message_ref-message-id',
            'message_ref',
            'messageId',
            'message',
            'id',
            'CASCADE', 'CASCADE'
        ); // `message_ref`.`message_id` ==> `message`.`id` CASCADE
        $this->addForeignKey(
            'fk-dialog_ref-user_id',
            'dialog_ref',
            'userId',
            'user',
            'id',
            'CASCADE', 'CASCADE'
        ); // `dialog_ref`.`user_id`     ==> `user`.`id` CASCADE
        $this->addForeignKey(
            'fk-message_ref-user-id',
            'message_ref',
            'userId',
            'user',
            'id',
            'CASCADE', 'CASCADE'
        ); // `message_ref`.`user_id`    ==> `user`.`id` CASCADE

        echo 'success';
    }

    public function down()
    {
        $this->dropTable('dialog');
        $this->dropTable('dialog_ref');
        $this->dropTable('message');
        $this->dropTable('message_ref');
    }
}
