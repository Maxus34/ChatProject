<?php

use yii\db\Migration;

class m170214_174003_add_column_to_dialog_ref extends Migration
{
    public function up()
    {
        $this->addColumn('dialog_ref', 'updated_at', $this->integer(11));
    }

    public function down()
    {
        $this->dropColumn('dialog_ref', 'updated_at');
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
