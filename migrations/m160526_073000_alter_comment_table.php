<?php

use yii\db\Migration;

class m160526_073000_alter_comment_table extends Migration
{

    public $tablename = 'comment';

    public function up()
    {
        $this->dropIndex('comment_status', $this->tablename);
        $this->dropColumn($this->tablename, 'status');

        $this->addColumn($this->tablename, 'approval_status', $this->integer());
        $this->createIndex('comment_status', $this->tablename, 'approval_status');
    }

    public function down()
    {
        $this->dropIndex('comment_status', $this->tablename);
        $this->dropColumn($this->tablename, 'approval_status');

        $this->addColumn($this->tablename, 'status', $this->integer());
        $this->createIndex('comment_status', $this->tablename, 'status');
    }
}
