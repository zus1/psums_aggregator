<?php

namespace PsumsAggregator\Classes;

/**
 * Class Response
 * @package PsumsAggregator\Classes
 *
 * Main response class.
 * Called in controllers and passes response to router
 *
 */
class Response
{
    private $request;

    private $timeout = 0;

    public function __construct(Request $request) {
        $this->request = $request;
    }

    /**
     *
     * Returns exception response in json format
     *
     *
     * @param string $message
     * @param int|null $code
     * @return false|string
     */
    public function returnApiException(string $message, ?int $code=0) {
        if($code === 0) {
            $code = HttpCodes::INTERNAL_SERVER_ERROR;
        }
        http_response_code($code);
        return json_encode(array("error" => 1, "message" => $message), JSON_UNESCAPED_UNICODE);
    }

    /**
     *
     * Return ok response in json format
     *
     * @param string $message
     * @return false|string
     */
    public function returnApiOk(string $message) {
        return json_encode(array("error" => 0, "message" => $message), JSON_UNESCAPED_UNICODE);
    }
}