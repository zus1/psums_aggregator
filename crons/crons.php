<?php
$_SERVER["DOCUMENT_ROOT"] = dirname(__DIR__);
include_once($_SERVER["DOCUMENT_ROOT"] . "/include.php");

$report = Factory::getObject(Factory::TYPE_RULES_CONTROLLER)->rulesCycle();
echo $report;