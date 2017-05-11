<?php

use yii\db\Migration;

class m170509_155833_init_user_images_table extends Migration
{
    public function up()
    {
        $this->createTable("user_images", [
                'id'         => $this->primaryKey()->unsigned(),
                'userId'     => $this->integer(11)->unsigned(),
                'fileId'     => $this->integer(11)->unsigned(),
                'isMain'     => $this->boolean(),
                'createdAt'  => $this->integer(11)->unsigned(),
        ]);

        $this->addForeignKey(
            'fk-user_images-files_id',
            'user_images',
            'fileId',
            'files',
            'id',
            'CASCADE', 'CASCADE'
        ); // `user_images`.`file_id` => `files`.`id`

        $this->addForeignKey(
            'fk-user_images-user_id',
            'user_images',
            'userId',
            'user',
            'id',
            'CASCADE', 'CASCADE'
        ); // `user_images`.`user_id` => `user`.`id`
    }

    public function down()
    {
        $this->dropForeignKey('fk-user_images-files_id', 'user_images');
        $this->dropForeignKey('fk-user_images-user_id' , 'user_images');
        $this->dropTable('user_images');
    }

}
