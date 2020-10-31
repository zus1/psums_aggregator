<?php

class Response
{
    private $request;

    private $timeout = 0;

    public function __construct(Request $request) {
        $this->request = $request;
    }

    public function timeout(int $timeout) {
        $this->timeout = $timeout;
    }

    public function returnApiException(string $message, ?int $code=0) {
        if($code === 0) {
            $code = HttpCodes::INTERNAL_SERVER_ERROR;
        }
        http_response_code($code);
        return json_encode(array("error" => 1, "message" => $message), JSON_UNESCAPED_UNICODE);
    }

    public function returnApiOk(string $message) {
        return json_encode(array("error" => 0, "message" => $message), JSON_UNESCAPED_UNICODE);
    }
}