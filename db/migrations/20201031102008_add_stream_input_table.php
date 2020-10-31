<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

final class AddStreamInputTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table("stream_input", ['id' => false, 'primary_key' => ["id"], 'engine' => "InnoDB", 'encoding' => "utf8", 'collation' => "utf8_general_ci", 'comment' => "", 'row_format' => "Dynamic"]);
        $table->addColumn('id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_BIG, 'precision' => 20, 'identity' => 'enable']);
        $table->addColumn('name', 'string', ['null' => true, 'limit' => 100, 'collation' => "utf8_general_ci", 'encoding' => "utf8", 'after' => 'id']);
        $table->addColumn('stream_id', 'string', ['null' => false, 'limit' => 45, 'collation' => "utf8_general_ci", 'encoding' => "utf8", 'after' => 'name']);
        $table->addColumn('input', 'text', ['null' => true, 'limit' => MysqlAdapter::TEXT_REGULAR, 'collation' => "utf8_general_ci", 'encoding' => "utf8", 'after' => 'stream_id']);
        $table->save();
        $table->addIndex(['stream_id'], ['name' => "stream_id", 'unique' => false])->save();
    }
}
