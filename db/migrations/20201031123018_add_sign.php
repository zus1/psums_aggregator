<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddSign extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table("sign");
        $rows = [
            ['stream_id' => "1a2b3c4d", "sign_key" => "14rg7-7un89-da234-ng50p"],
            ['stream_id' => "2a3b5c7d", "sign_key" => "3rfgb-743e1-wd53q-123rf"],
            ['stream_id' => "d34tz671", "sign_key" => "1v563-t6zre-86q23-vf234"],
            ['stream_id' => "1db56725", "sign_key" => "6z752-dae45-zte32-dsq23"],
        ];
        $table->insert($rows)->save();
    }
}
