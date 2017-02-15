<?php

use yii\db\Migration;

class m170203_143311_init_user_table extends Migration
{
    public function up()
    {
        $this->createTable('user', [
            'id' => $this->primaryKey(11)->unsigned(),
            'username' => $this->string(50) ->notNull(),
            'email'    => $this->string(100)->notNull(),
            'password' => $this->string(100)->notNull(),
            'auth_key' => $this->string(255),
            'active'   => $this->boolean(),
            'activation_key' => $this->string(40),
            'created_at' => $this->integer(11)->unsigned()->notNull(),
        ]);
    }

    public function down()
    {
        $this->dropTable('user');
    }
}
