<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;

final class AddApiKeyTable extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table("api_keys", ['id' => false, 'primary_key' => ["id"], 'engine' => "InnoDB", 'encoding' => "utf8", 'collation' => "utf8_general_ci", 'comment' => "", 'row_format' => "Dynamic"]);
        $table->addColumn('id', 'integer', ['null' => false, 'limit' => MysqlAdapter::INT_BIG, 'precision' => 20, 'identity' => 'enable']);
        $table->addColumn('api_key', 'string', ['null' => true, 'limit' => 100, 'collation' => "utf8_general_ci", 'encoding' => "utf8", 'after' => 'id']);
        $table->save();
        $table->addIndex(['api_key'], ['name' => "api_key", 'unique' => false])->save();
    }
}
