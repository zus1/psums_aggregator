<?php


class BaseController
{
    private $request;
    private $response;
    private $htmlParser;
    private $validator;
    private $cms;
    private $web;

    public function __construct(Request $request, HtmlParser $htmlParser, Validator $validator, Response $response, Cms $cms, Web $web) {
        $this->request = $request;
        $this->htmlParser = $htmlParser;
        $this->validator = $validator;
        $this->response = $response;
        $this->cms = $cms;
        $this->web = $web;
    }

    public function webRoot() {
        return json_encode(array("error" => 0, "message" => "ok"));
        //Factory::getObject(Factory::TYPE_ROUTER)->redirect(HttpParser::baseUrl() . "views/documentation.php");
    }
}