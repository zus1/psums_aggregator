<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

final class AddRulesTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table("rules_available", ['id' => false, 'primary_key' => ["id"], 'engine' => "InnoDB", 'encoding' => "utf8", 'collation' => "utf8_general_ci", 'comment' => "", 'row_format' => "Dynamic"]);
        $table->addColumn('id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_BIG, 'precision' => 20, 'identity' => 'enable']);
        $table->addColumn('rule_name', 'string', ['null' => true, 'limit' => 45, 'collation' => "utf8_general_ci", 'encoding' => "utf8", 'after' => 'id']);
        $table->addColumn('rule_description', 'text', ['null' => true, 'limit' => MysqlAdapter::TEXT_REGULAR, 'collation' => "utf8_general_ci", 'encoding' => "utf8", 'after' => 'rule_name']);
        $table->addColumn('pattern', 'text', ['null' => true, 'limit' => MysqlAdapter::TEXT_REGULAR, 'collation' => "utf8_general_ci", 'encoding' => "utf8", 'after' => 'rule_description']);
        $table->save();
        $table->addIndex(['rule_name'], ['name' => "rule_name", 'unique' => false])->save();
    }
}
