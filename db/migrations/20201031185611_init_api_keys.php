<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class InitApiKeys extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table("api_keys");
        $rows = [
            ['api_key' => "1df45-5zu78-qdftz-4b8lo-1vfre-124np"],
        ];
        $table->insert($rows)->save();
    }
}
