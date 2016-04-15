<?php

use yii\db\Migration;

class m160415_073000_alter_table_for_SoftDelete extends Migration
{

    public $tablename = 'comment';

    public function up()
    {
        $this->addColumn($this->tablename, 'is_deleted', $this->boolean()->notNull()->defaultValue(0));
        $this->addColumn($this->tablename, 'deleted_at', $this->integer());
    }

    public function down()
    {
        $this->dropColumn($this->tablename, 'is_deleted');
        $this->dropColumn($this->tablename, 'deleted_at');
    }
}
