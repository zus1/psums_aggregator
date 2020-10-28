<?php
var_dump("hhh");
die();
include_once("include.php");
Factory::getObject(Factory::TYPE_ROUTER)->route();