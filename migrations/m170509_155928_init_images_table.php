<?php

use yii\db\Migration;

class m170509_155928_init_images_table extends Migration
{
    public function up()
    {
        $this->createTable("images", [
                'id'         => $this->primaryKey()->unsigned(),
                'itemId'     => $this->integer(11)->unsigned(),
                'fileId'     => $this->integer(11)->unsigned(),
                'isMain'     => $this->boolean(),
                'key'        => $this->string(),
                'createdAt'  => $this->integer(11)->unsigned(),
        ]);

        $this->addForeignKey(
            'fk-images-files_id',
            'images',
            'fileId',
            'files',
            'id',
            'CASCADE', 'CASCADE'
        ); // `images`.`file_id` => `files`.`id`
    }

    public function down()
    {
        $this->dropForeignKey('fk-images-files_id', "images");
        $this->dropTable("images");
    }
}
