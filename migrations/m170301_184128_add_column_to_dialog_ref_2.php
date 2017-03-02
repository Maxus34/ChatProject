<?php

use yii\db\Migration;

class m170301_184128_add_column_to_dialog_ref_2 extends Migration
{
    public function up()
    {
        $this->addColumn('dialog_ref', 'is_active', $this->boolean()->defaultValue(1));
    }

    public function down()
    {
        $this->dropColumn('dialog_ref', 'is_active');
    }
}
