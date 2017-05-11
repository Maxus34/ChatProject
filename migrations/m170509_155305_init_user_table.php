<?php

use yii\db\Migration;

class m170509_155305_init_user_table extends Migration {
    public function up() {
        $this->createTable('user', [
            'id'            => $this->primaryKey(11)->unsigned(),
            'username'      => $this->string(50)->notNull(),
            'email'         => $this->string(100)->notNull(),
            'password'      => $this->string(100)->notNull(),
            'authKey'       => $this->string(255),
            'isActive'      => $this->boolean()->defaultValue(0),
            'activationKey' => $this->string(40),
            'createdAt'     => $this->integer(11)->unsigned()->notNull(),
        ]);
    }

    public function down() {

        $this->dropTable('user');
    }
}
