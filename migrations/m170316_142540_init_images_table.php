<?php

use yii\db\Migration;

class m170316_142540_init_images_table extends Migration
{
    public function up()
    {
        $this->createTable("images",
            [
                'id'         => $this->primaryKey()->unsigned(),
                'item_id'    => $this->integer(11)->unsigned(),
                'file_id'    => $this->integer(11)->unsigned(),
                'is_main'    => $this->boolean(),
                'key'        => $this->string(),
                'created_at' => $this->integer(11)->unsigned(),
            ]
        );

        $this->addForeignKey(
            'fk-images-files_id',
            'user_images',
            'file_id',
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
