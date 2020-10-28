<?php

class ExceptionHandlerExtender
{
    private $logger;
    private $response;

    public function __construct(LoggerInterface $logger, Response $response) {
        $this->logger = $logger;
        $this->response = $response;
    }

    public function extend() {
        return array(
            "web" => "handleWebException",
            "api" => "handleApiException",
            "stream" => "handleStreamException",
        );
    }

    public function handleWebException(Exception $e) {
        $this->logger->setType(Logger::LOGGER_WEB);
        $this->logger->logException($e);
        Factory::getObject(Factory::TYPE_ROUTER)->redirect(HttpParser::baseUrl() . "views/error.php?error=" . $e->getMessage() . "&code=" . $e->getCode(), $e->getCode());
    }

    public function handleApiException(Exception $e) {
        $this->logger->setType(Logger::LOGGER_API);
        $this->logger->logException($e);
        echo Factory::getObject(Factory::TYPE_API_EXCEPTION)->getApiException($e);
        die();
    }

    public function handleStreamException(Exception $e) {
        $this->logger->setType(Logger::LOGGER_STREAM);
        $this->logger->logException($e);
        echo $this->response->returnApiException($e->getMessage(), $e->getCode());
        die();
    }
}