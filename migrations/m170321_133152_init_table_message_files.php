<?php

use yii\db\Migration;

class m170321_133152_init_table_message_files extends Migration
{
    public function up()
    {
        $this->createTable('message_files',
            [
                'id'         => $this->primaryKey(),
                'message_id' => $this->integer(11)->unsigned(),
                'file_id'    => $this->integer(11)->unsigned(),
                'created_at' => $this->integer(11)->unsigned(),
            ]
        );

        $this->addForeignKey(
            'fk-message_files-message',
            'message_files',
            'message_id',
            'message',
            'id',
            'CASCADE', 'CASCADE'
        ); // `message_files`.`message_id` => `message`.`id`

        $this->addForeignKey(
            'fk-message_files-files',
            'message_files',
            'file_id',
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
        return false;
    }
}
