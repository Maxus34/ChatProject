<?php

use yii\db\Migration;

class m170321_122640_add_column_has_files_to_message_rec extends Migration
{
    public function up()
    {
        $this->addColumn('message', 'has_files', $this->boolean());
    }

    public function down()
    {
        return false;
    }
}
