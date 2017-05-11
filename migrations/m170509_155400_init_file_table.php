<?php

use yii\db\Migration;

class m170509_155400_init_file_table extends Migration
{
    public function up()
    {
        $this->createTable("files",
            [
                'id'         => $this->primaryKey()->unsigned(),
                'name'       => $this->string(225),
                'extension'  => $this->string(50),
                'type'       => $this->string(50),
                'size'       => $this->integer(11)->unsigned(),
                'path'       => $this->string(255),
                'createdAt' => $this->integer(11)->unsigned(),
                'createdBy' => $this->integer(11)->unsigned(),
            ]
        );
    }

    public function down()
    {
        $this->dropTable('files');
    }

}
