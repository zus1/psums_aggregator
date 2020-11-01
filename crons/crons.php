<?php

/*
 * Inits cycle for applying rules to input streams
 * */

use PsumsAggregator\Classes\Factory;

$_SERVER["DOCUMENT_ROOT"] = dirname(__DIR__);
include_once($_SERVER["DOCUMENT_ROOT"] . "/include.php");

$report = Factory::getObject(Factory::TYPE_RULES_CONTROLLER)->rulesCycle();
echo $report;