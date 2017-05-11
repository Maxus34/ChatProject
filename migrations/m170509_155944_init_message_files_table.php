<?php

use yii\db\Migration;

class m170509_155944_init_message_files_table extends Migration
{
    public function up()
    {
        $this->createTable('message_files',
            [
                'id'         => $this->primaryKey(),
                'messageId' => $this->integer(11)->unsigned(),
                'fileId'    => $this->integer(11)->unsigned(),
                'createdAt' => $this->integer(11)->unsigned(),
            ]
        );

        $this->addForeignKey(
            'fk-message_files-message',
            'message_files',
            'messageId',
            'message',
            'id',
            'CASCADE', 'CASCADE'
        ); // `message_files`.`message_id` => `message`.`id`

        $this->addForeignKey(
            'fk-message_files-files',
            'message_files',
            'fileId',
            'files',
            'id',
            'CASCADE', 'CASCADE'
        ); // `message_files`.`file_id` => `files`.`id`
    }

    public function down()
    {
        $this->dropForeignKey('fk-message_files-message', 'message_files');
        $this->dropForeignKey('fk-message_files-files', 'message_files');

        $this->dropTable('message_files');
    }
}
