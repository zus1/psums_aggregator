<?php


class RulesController
{
    private $validator;
    private $stream;
    private $rules;

    public function __construct(Validator $validator, Stream $stream, Rules $rules) {
        $this->validator = $validator;
        $this->stream = $stream;
        $this->rules = $rules;
    }

    public function rulesCycle() {
        try {
            $streams = $this->stream->getStreamsForCycle();
            $this->rules->applyRules($streams);
            $this->stream->removeUsedInCycle($streams);
        } catch (Exception $e) {
            return $e->getMessage();
        }

        return "ok" . PHP_EOL;
    }
}