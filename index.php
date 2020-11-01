<?php

use PsumsAggregator\Classes\Factory;

include_once("include.php");
Factory::getObject(Factory::TYPE_ROUTER)->routeStream();