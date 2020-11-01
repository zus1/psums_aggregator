<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSettings extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table("settings");
        $rows = [
            ['name' => "stream_active_1a2b3c4d", 'value' => 1],
            ['name' => "stream_active_2a3b5c7d", 'value' => 1],
            ['name' => "stream_active_d34tz671", 'value' => 1],
            ['name' => "stream_active_1db56725", 'value' => 1],
            ['name' => "stream_delay_min_1a2b3c4d", 'value' => 4],
            ['name' => "stream_delay_min_2a3b5c7d", 'value' => 4],
            ['name' => "stream_delay_min_d34tz671", 'value' => 4],
            ['name' => "stream_delay_min_1db56725", 'value' => 4],
            ['name' => "aggregator_delay_min", 'value' => 2],
            ['name' => "stream_throughput_1a2b3c4d", 'value' => 20],
            ['name' => "stream_throughput_2a3b5c7d", 'value' => 20],
            ['name' => "stream_throughput_d34tz671", 'value' => 20],
            ['name' => "stream_throughput_1db56725", 'value' => 20],
            ['name' => "aggregator_chunk_size", 'value' => 5],
            ['name' => "aggregator_active", 'value' => 1],
        ];
        $table->insert($rows)->save();
    }
}
