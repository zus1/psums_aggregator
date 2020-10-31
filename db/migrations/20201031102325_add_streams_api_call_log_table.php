<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

final class AddStreamsApiCallLogTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table("streams_api_call_log", ['id' => false, 'primary_key' => ["id"], 'engine' => "InnoDB", 'encoding' => "utf8", 'collation' => "utf8_general_ci", 'comment' => "", 'row_format' => "Dynamic"]);
        $table->addColumn('id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_BIG, 'precision' => 20, 'identity' => 'enable']);
        $table->addColumn('api', 'string', ['null' => true, 'limit' => 45, 'collation' => "utf8_general_ci", 'encoding' => "utf8", 'after' => 'id']);
        $table->addColumn('raw_result', 'text', ['null' => true, 'limit' => MysqlAdapter::TEXT_REGULAR, 'collation' => "utf8_general_ci", 'encoding' => "utf8", 'after' => 'api']);
        $table->addColumn('error', 'integer', ['null' => true, 'limit' => MysqlAdapter::INT_TINY, 'precision' => 1, 'after' => 'raw_result']);
        $table->addColumn('code', 'string', ['null' => true, 'limit' => 45, 'collation' => "utf8_general_ci", 'encoding' => "utf8", 'after' => 'error']);
        $table->addColumn('last_updated', 'datetime', ['null' => false, 'default' => "CURRENT_TIMESTAMP", 'after' => 'code']);
        $table->save();
        $table->addIndex(['api'], ['name' => "api", 'unique' => false])->save();
        $table->addIndex(['code'], ['name' => "code", 'unique' => false])->save();
        $table->addIndex(['error'], ['name' => "error", 'unique' => false])->save();
    }
}
