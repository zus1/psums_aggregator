<?php

namespace PsumsAggregator\Classes\Controllers;

use PsumsAggregator\Classes\Response;

/**
 * Class BaseController
 * @package PsumsAggregator\Classes\Controllers
 *
 * Base controller used only for web root
 *
 */
class BaseController
{

    private $response;

    public function __construct(Response $response) {
        $this->response = $response;
    }

    /**
     * @return string
     */
    public function webRoot() {
        return $this->response->returnApiOk("Nothing to find here");
    }
}