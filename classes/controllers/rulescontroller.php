<?php

namespace PsumsAggregator\Classes\Controllers;

use Exception;
use PsumsAggregator\Classes\Cache;
use PsumsAggregator\Classes\Factory;
use PsumsAggregator\Classes\Rules;
use PsumsAggregator\Classes\Stream;
use PsumsAggregator\Classes\Validator;
use PsumsAggregator\Interfaces\LoggerInterface;

/**
 * Class RulesController
 * @package PsumsAggregator\Classes\Controllers
 *
 * Used as front controller for cycling rules
 *
 */
class RulesController
{
    private $validator;
    private $stream;
    private $rules;
    private $logger;

    public function __construct(Validator $validator, Stream $stream, Rules $rules, LoggerInterface $logger) {
        $this->validator = $validator;
        $this->stream = $stream;
        $this->rules = $rules;
        $this->logger = $logger;
    }

    /**
     *
     * Cycle the rules.
     * First fetches all streams and the applies rules on each one
     * Last it removes used word form stream
     *
     * @return string
     */
    public function rulesCycle() {
        try {
            $this->checkShouldCycleRun();
            $streams = $this->stream->getStreamsForCycle();
            $this->rules->applyRules($streams);
            $this->stream->removeUsedInCycle($streams);
        } catch (Exception $e) {
            $this->logger->logException($e);
            return $e->getMessage() . PHP_EOL;
        }

        return "ok" . PHP_EOL;
    }

    /**
     *
     * Checks if aggregator is deactivated or on timeout
     *
     * @throws Exception
     */
    private function checkShouldCycleRun() {
        $db = Factory::getObject(Factory::TYPE_DATABASE, true);
        $delay = (int)$db->getSetting("aggregator_delay_min", 5) * 60;
        $aggregatorActive = (int)$db->getSetting("aggregator_active", 1);
        if($aggregatorActive === 0) {
            throw new Exception("Aggregator deactivated");
        }
        if(Cache::shouldIRun("aggregator_run_key", $delay) === false) {
            throw new Exception("Aggregator on timeout");
        }
    }
}