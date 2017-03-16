<?php

use yii\db\Migration;

class m170313_143508_init_user_images_table extends Migration
{
    public function up()
    {
        $this->createTable("user_images",
            [
                'id'         => $this->primaryKey()->unsigned(),
                'user_id'    => $this->integer(11)->unsigned(),
                'file_id'    => $this->integer(11)->unsigned(),
                'is_main'    => $this->boolean(),
                'created_at' => $this->integer(11)->unsigned(),
            ]
        );

        $this->addForeignKey(
            'fk-user_images-files_id',
            'user_images',
            'file_id',
            'files',
            'id',
            'CASCADE', 'CASCADE'
        ); // `user_images`.`file_id` => `files`.`id`

        $this->addForeignKey(
            'fk-user_images-user_id',
            'user_images',
            'user_id',
            'user',
            'id',
            'CASCADE', 'CASCADE'
        ); // `user_images`.`user_id` => `user`.`id`
    }

    public function down()
    {
        $this->dropTable('user_images');
        $this->dropForeignKey('fk-user_images-files_id', 'user_images');
        $this->dropForeignKey('fk-user_images-user_id' , 'user_images');
    }

}
