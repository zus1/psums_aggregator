<?php

namespace PsumsAggregator\Extenders;

use Exception;
use PsumsAggregator\Classes\Log\Logger;
use PsumsAggregator\Classes\Response;
use PsumsAggregator\Interfaces\LoggerInterface;

class ExceptionHandlerExtender
{
    private $logger;
    private $response;

    public function __construct(LoggerInterface $logger, Response $response) {
        $this->logger = $logger;
        $this->response = $response;
    }

    /**
     *
     * Extend basic type to method mapping for Classes\ExceptionHandler
     * Mapped method also needs to be defined in this class
     *
     * @return array
     */
    public function extend() {
        return array(
            "stream" => "handleStreamException",
        );
    }

    public function handleStreamException(Exception $e) {
        $this->logger->setType(Logger::LOGGER_STREAM);
        $this->logger->logException($e);
        $code = 200;
        if($e->getCode()) {
            $code = $e->getCode();
        }
        http_response_code($code);
        echo $this->response->returnApiException($e->getMessage(), $code);
        die();
    }
}