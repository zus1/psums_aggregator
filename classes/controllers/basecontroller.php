<?php

class BaseController
{

    private $response;

    public function __construct(Response $response) {
        $this->response = $response;
    }

    public function webRoot() {
        return $this->response->returnApiOk("Nothing to find here");
    }
}