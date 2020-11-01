<?php

namespace PsumsAggregator\Classes;

use Exception;

/**
 * Class Rules
 * @package PsumsAggregator\Classes
 *
 * Class that will handle applying rules to input streams
 * Can be extended by adding new rules logic and new rules to db available_rules table
 *
 */
class Rules
{
    private $results;

    private $rulesWithPatterns = array("compare_vowels", "pattern", "match_making");

    public function __construct(RulesResults $results) {
        $this->results = $results;
    }

    public function getRulesModel() {
        return Factory::getModel(Factory::MODEL_RULES);
    }

    public function getStreamRulesModel() {
        return Factory::getModel(Factory::MODEL_STREAM_RULES);
    }

    /**
     *
     * Returns rule apply method, depending ony rule name
     *
     * @return array
     */
    private function getRuleToMethodMapping() {
        return array(
            'compare_vowels' => "applyCompareVowels",
            'arrr_bacon' => "applyArrrBacon",
            "bacon_arrr" => "applyBaconArrr",
            "pattern" => "applyPattern",
            "match_making" => "applyMatchMaking"
        );
    }

    /**
     *
     * Applies all applicable rules to word chunks (chunked stream)
     * Will look for corresponding word in second stream (aka 1 => 1, 2 => 2 ...)
     * Sends result to PsumsAggregator\Classes\RulesResults
     *
     * @param array $packedStreams
     * @throws Exception
     */
    public function applyRules(array $packedStreams) {
        foreach($packedStreams as $streamId => $streamChunks) {
            $rules = Factory::getObject(Factory::TYPE_DATABASE, true)->select(
                "SELECT t1.second_stream, t1.rule_id, t2.rule_name, t2.pattern FROM stream_rules as t1 INNER JOIN rules_available as t2 ON t1.rule_id = t2.id WHERE t1.first_stream = ?",
                array("string"),
                array($streamId)
            );

            $currentChunk = 0;
            foreach ($streamChunks["chunked"] as $stream) {
                foreach($rules as $rule) {
                    if(empty($packedStreams[$rule["second_stream"]])) {
                        continue;
                    }
                    $secondStream = $packedStreams[$rule["second_stream"]]["chunked"][$currentChunk];
                    if(!array_key_exists($rule["rule_name"], $this->getRuleToMethodMapping())) {
                        throw new Exception(__CLASS__ . "Unsupported rule: " . $rule["rule_name"]);
                    }
                    if(in_array($rule["rule_name"], $this->rulesWithPatterns)) {
                        $pattern = json_decode($rule["pattern"]);
                        $result = call_user_func_array([$this, $this->getRuleToMethodMapping()[$rule["rule_name"]]],
                            array($stream, $secondStream, $pattern));
                    } else {
                        $result = call_user_func_array([$this, $this->getRuleToMethodMapping()[$rule["rule_name"]]],
                            array($stream, $secondStream));
                    }
                    $this->results->addResults($streamId, $rule["second_stream"], $rule["rule_name"], $rule["rule_id"], $result);
                }
                $currentChunk++;
            }
        }

        $this->results->applyResults();
    }

    /**
     *
     * Looks for number of vowels in both streams and returns counts
     *
     * @param array $streamOne
     * @param array $streamTwo
     * @param array $vowels
     * @return array
     */
    private function applyCompareVowels(array $streamOne, array $streamTwo, array $vowels) {
        $wordsInStream = count($streamOne);
        $streamOneCount = 0;
        $streamTwoCount = 0;
        for($i = 0; $i < $wordsInStream; $i++) {
            $wa1 = str_split($streamOne[$i]);
            $wa2 = str_split($streamTwo[$i]);

            $v1 = array_values(array_intersect($vowels, $wa1));
            $v2 = array_values(array_intersect($vowels, $wa2));
            $streamOneCount += count($v1);
            $streamTwoCount += count($v2);
        }

        return array("total_first_stream" => $streamOneCount, "total_second_stream" => $streamTwoCount);
    }

    /**
     *
     * Looks for specified word in both streams and returns counts
     * Looks for first word in first stream and second word in second stream
     *
     * @param array $streamOne
     * @param array $streamTwo
     * @return array
     */
    private function applyArrrBacon(array $streamOne, array $streamTwo) {
        $phrase1 = "pook";
        $phrase2 = "beef";

        $wordsInStream = count($streamOne);
        $streamOneCount = 0;
        $streamTwoCount = 0;
        for($i = 0; $i < $wordsInStream; $i++) {
            if(stripos($streamOne[$i], $phrase1) || substr($streamOne[$i], 0, strlen($phrase1)) === $phrase1) {
                $streamOneCount++;
            }
            if(stripos($streamTwo[$i], $phrase2) || substr($streamTwo[$i], 0, strlen($phrase2)) === $phrase2) {
                $streamTwoCount++;
            }
        }

        return array("total_first_stream" => $streamOneCount, "total_second_stream" => $streamTwoCount);
    }

    /**
     *
     * Same as ArrBacon but reversed
     *
     * @param array $streamOne
     * @param array $streamTwo
     * @return array
     */
    private function applyBaconArrr(array $streamOne, array $streamTwo) {
        return $this->applyArrrBacon($streamTwo, $streamOne); //this streams are reverse
    }

    /**
     *
     * Looks for matches for both streams, and supplied pattern
     * Patter is stored in database
     *
     * @param array $streamOne
     * @param array $streamTwo
     * @param array $pattern
     * @return array
     */
    private function applyPattern(array $streamOne, array $streamTwo, array $pattern) {
        $r1 = array_values(array_intersect($streamOne, $pattern));
        $r2 = array_values(array_intersect($streamTwo, $pattern));

        return array("total_first_stream" => count($r1), "total_second_stream" => count($r2));
    }

    /**
     *
     * Takes provided pattern of word pairs (word1-word2)
     * Looks for word1 in first stream and word2 in second
     * Counts total matches
     *
     * @param array $streamOne
     * @param array $streamTwo
     * @param array $duplets
     * @return array
     */
    private function applyMatchMaking(array $streamOne, array $streamTwo, array $duplets) {
        $total = 0;
        foreach($duplets as $duplet) {
            $dupletParts = explode("-", $duplet);
            if(count($dupletParts) !== 2) {
                continue;
            }
            if(in_array($dupletParts[0], $streamOne) && in_array($dupletParts[1], $streamTwo)) {
                $total++;
            }
        }

        return array("total_matches" => $total);
    }
}