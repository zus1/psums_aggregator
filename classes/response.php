<?php

class Response
{
    private $htmlParser;
    private $request;

    private $timeout = 0;

    public function __construct(HtmlParser $parser, Request $request) {
        $this->htmlParser = $parser;
        $this->request = $request;
    }

    public function timeout(int $timeout) {
        $this->timeout = $timeout;
    }

    public function returnView(string $view, ?array $data=array()) {
        return $this->htmlParser->parseView($view, $data);
    }

    public function returnRedirect(string $url, ?int $code=null, ?array $data=array()) {
        Factory::getObject(Factory::TYPE_ROUTER)->redirect($url, $code, $data, $this->timeout);
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