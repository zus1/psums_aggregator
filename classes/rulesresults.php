<?php


class RulesResults
{
    private $results = array();

    public function getModel() {
        return Factory::getModel(Factory::MODEL_RULES_RESULTS);
    }

    private function getHandlerToRuleMapping() {
        return array(
            'compare_vowels' => "handleCompareVowelsResult",
            'arrr_bacon' => "handleArrrBaconResult",
            "bacon_arrr" => "handleBaconArrrResult",
            "pattern" => "handlePatternResult",
            "match_making" => "handleMatchMakingResult"
        );
    }

    public function addResults(string $streamIdOne, string $streamIdTwo, string $ruleName, int $ruleId, array $results) {
        if(!array_key_exists($ruleName, $this->getHandlerToRuleMapping())) {
            throw new Exception(__CLASS__ . ":" . __FUNCTION__ ." Unsupported rule: " . $ruleName);
        }
        $uniqueKey = sprintf("%s_%s_%s", $streamIdOne, $streamIdTwo, $ruleId);
        if(!array_key_exists($uniqueKey, $this->results)) {
            $resultArray  = array(
                'first_stream' => $streamIdOne,
                'second_stream' => $streamIdTwo,
                'rule_name' => $ruleName,
                'rule_id' => $ruleId,
                'results' => $results
            );
            $this->results[$uniqueKey] = call_user_func_array([$this, $this->getHandlerToRuleMapping()[$ruleName]],
                array($resultArray, array("total_first_stream" => 0, "total_second_stream" => 0)));
        } else {
            $this->results[$uniqueKey] = call_user_func_array([$this, $this->getHandlerToRuleMapping()[$ruleName]], array($this->results[$uniqueKey], $results));
        }
    }

    public function applyResults() {
        if(empty($this->results)) {
            return;
        }
        foreach($this->results as $result) {
            $existing = $this->getModel()->select(array("results", "rule_name"),
                array("first_stream" => $result["first_stream"], "second_stream" => $result["second_stream"], "rule_id" => $result["rule_id"]));
            if(!$existing) {
                $result["results"] = json_encode($result["results"], JSON_UNESCAPED_UNICODE);
                $this->getModel()->insert($result);
            } else {
                $existing = $existing[0];
                if(!array_key_exists($existing["rule_name"], $this->getHandlerToRuleMapping())) {
                    throw new Exception(__CLASS__ . ":" . __FUNCTION__ ." Unsupported rule: " . $existing["rule_name"]);
                }
                $existing["results"] = json_decode($existing["results"], true);
                $nExisting = call_user_func_array([$this, $this->getHandlerToRuleMapping()[$existing["rule_name"]]], array($existing, $result["results"]));
                $nExisting["results"] = json_encode($nExisting["results"], JSON_UNESCAPED_UNICODE);
                $this->getModel()->update(array("results" => $nExisting["results"]),
                    array("first_stream" => $result["first_stream"], "second_stream" => $result["second_stream"], "rule_id" => $result["rule_id"]));
            }
        }
    }

    private function handleCompareVowelsResult(array $existing, array $toAdd) {
        return $this->handleDoubleResult($existing, $toAdd);
    }

    private function handleArrrBaconResult(array $existing, array $toAdd) {
        return $this->handleDoubleResult($existing, $toAdd);
    }

    private function handleBaconArrrResult(array $existing, array $toAdd) {
        return $this->handleDoubleResult($existing, $toAdd);
    }

    private function handlePatternResult(array $existing, array $toAdd) {
        return $this->handleDoubleResult($existing, $toAdd);
    }

    private function handleDoubleResult(array $existing, array $toAdd) {
        $existing['results']["total_first_stream"] = (int)$existing['results']["total_first_stream"] + (int)$toAdd["total_first_stream"];
        $existing['results']["total_second_stream"] = (int)$existing['results']["total_second_stream"] + (int)$toAdd["total_second_stream"];

        $lower = (int)$existing['results']["total_second_stream"];
        if($lower <= 0) {
            $lower = 1;
        }
        $perc = (int)$existing['results']["total_first_stream"]/$lower;
        $existing['results']["percentage"] = (float)$perc;

        return $existing;
    }

    private function handleMatchMakingResult(array $existing, array $toAdd) {
        $existing["results"]["total"] = (int)$existing["results"]["total"] + (int)$toAdd["total"];

        return $existing;
    }
}