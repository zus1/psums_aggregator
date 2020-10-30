<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

final class AddStreamRulesTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table("stream_rules", ['id' => false, 'primary_key' => ["id"], 'engine' => "InnoDB", 'encoding' => "utf8", 'collation' => "utf8_general_ci", 'comment' => "", 'row_format' => "Dynamic"]);
        $table->addColumn('id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_BIG, 'precision' => 20, 'identity' => 'enable']);
        $table->addColumn('first_stream', 'string', ['null' => false, 'limit' => 45, 'collation' => "utf8_general_ci", 'encoding' => "utf8", 'after' => 'id']);
        $table->addColumn('second_stream', 'string', ['null' => false, 'limit' => 45, 'collation' => "utf8_general_ci", 'encoding' => "utf8", 'after' => 'first_stream']);
        $table->addColumn('rule_id', 'string', ['null' => false, 'limit' => 45, 'collation' => "utf8_general_ci", 'encoding' => "utf8", 'after' => 'second_stream']);
        $table->save();
        $table->addIndex(['first_stream'], ['name' => "first_stream", 'unique' => false])->save();
        $table->addIndex(['second_stream'], ['name' => "second_stream", 'unique' => false])->save();
        $table->addIndex(['rule_id'], ['name' => "rule_id", 'unique' => false])->save();
    }
}
