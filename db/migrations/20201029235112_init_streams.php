<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class InitStreams extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table("stream");
        $rows = [
            ['stream_id' => "1a2b3c4d"],
            ['stream_id' => "2a3b5c7d"],
            ['stream_id' => "d34tz671"],
            ['stream_id' => "1db56725"],
        ];
        $table->insert($rows)->save();
    }
}
