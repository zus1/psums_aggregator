<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class InitStreamsRules extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table("stream_rules");
        $rows = [
            ['first_stream' => "2a3b5c7d", 'second_stream' => "d34tz671", 'rule_id' => 2],
            ['first_stream' => "d34tz671", 'second_stream' => "2a3b5c7d", 'rule_id' => 3],
            ['first_stream' => "1a2b3c4d", 'second_stream' => "1db56725", 'rule_id' => 4],
            ['first_stream' => "1a2b3c4d", 'second_stream' => "2a3b5c7d", 'rule_id' => 5],
        ];
        $table->insert($rows)->save();
    }
}
