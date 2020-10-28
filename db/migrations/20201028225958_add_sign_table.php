<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

final class AddSignTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table("sign", ['id' => false, 'primary_key' => ["id"], 'engine' => "InnoDB", 'encoding' => "utf8", 'collation' => "utf8_general_ci", 'comment' => "", 'row_format' => "Dynamic"]);
        $table->addColumn('id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_BIG, 'precision' => 20, 'identity' => 'enable']);
        $table->addColumn('stream_id', 'string', ['null' => false, 'limit' => 225, 'collation' => "utf8_general_ci", 'encoding' => "utf8", 'after' => 'id']);
        $table->addColumn('sign_key', 'string', ['null' => false, 'limit' => 225, 'collation' => "utf8_general_ci", 'encoding' => "utf8", 'after' => 'stream_id']);
        $table->save();
        $table->addIndex(['stream_id'], ['name' => "stream_id", 'unique' => false])->save();
    }
}
