<?php


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