<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class InitRules extends AbstractMigration
{
    public function change(): void
    {
        $table = $this->table("rules_available");
        $rows = [
            ['rule_name' => 'compare_vowels', 'rule_description' => "This rule counts number of vowels in words and makes comparison between streams", 'pattern' => '["a", "e", "i", "o", "u"]'],
            ['rule_name' => 'arrr_bacon', 'rule_description' => "Fuby as it sounds, this rule counts occurrences of arrr in first stream and bacon in second one, and compares them"],
            ['rule_name' => 'bacon_arrr', 'rule_description' => "Same as arrr_bacon, only reversed. So bacon in first stream and arrr in second"],
            ['rule_name' => 'pattern', 'rule_description' => "Uses provided patter to check occurrence of symbols in both streams, and compare", 'pattern' => '["lorem", "ipsum", "tipsum", "apsum", "dapsum"]'],
            ['rule_name' => 'match_making', 'rule_description' => "Uses provided patter to check occurrence of symbols in both streams, and compare", 'pattern' => '["lorem-ipsum", "dapsum-tapsum", "epsim-mepsum", "ipsum-ipsum"]'],
        ];
        $table->insert($rows)->save();
    }
}
