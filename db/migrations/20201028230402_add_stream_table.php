<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

final class AddStreamTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table("log_stream", ['id' => false, 'primary_key' => ["id"], 'engine' => "InnoDB", 'encoding' => "utf8", 'collation' => "utf8_general_ci", 'comment' => "", 'row_format' => "Dynamic"]);
        $table->addColumn('id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_BIG, 'precision' => 20, 'identity' => 'enable']);
        $table->addColumn('stream_id', 'string', ['null' => false, 'limit' => 225, 'collation' => "utf8_general_ci", 'encoding' => "utf8", 'after' => 'id']);
        $table->addColumn('stream', 'text', ['null' => true, 'limit' => MysqlAdapter::TEXT_REGULAR, 'collation' => "utf8_general_ci", 'encoding' => "utf8", 'after' => 'stream_id']);
        $table->addColumn('rules', 'text', ['null' => true, 'limit' => MysqlAdapter::TEXT_REGULAR, 'collation' => "utf8_general_ci", 'encoding' => "utf8", 'after' => 'stream']);
        $table->addColumn('created_at', 'datetime', ['null' => true, 'after' => 'rules']);
        $table->addColumn('updated_at', 'datetime', ['null' => false, 'default' => "CURRENT_TIMESTAMP", 'after' => 'created_at']);
        $table->save();
        $table->addIndex(['stream_id'], ['name' => "stream_id", 'unique' => false])->save();
    }
}
