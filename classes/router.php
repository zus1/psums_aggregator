<?php

class Router
{
    const REQUEST_POST = 'post';
    const REQUEST_GET = "get";
    private $eHandler;

    private $supportedRequestMethods = array(self::REQUEST_GET, self::REQUEST_POST);

    public function __construct(ExceptionHandler $eHandler) {
        $this->eHandler = $eHandler;
    }

    public function streamRouteMapping() {
        return array(
            '/' => array('class' => Factory::TYPE_BASE_CONTROLLER, 'method' => 'webRoot', 'request' => self::REQUEST_GET), //in case somebody hits root
            '/stream/input' => array('class' => Factory::TYPE_STREAM_CONTROLLER, 'method' => 'inputStream', 'request' => self::REQUEST_POST),
        );
    }

    public function routeStream() {
        $requestUri = explode("?", strtolower($_SERVER["REQUEST_URI"]))[0];
        $requestMethod = strtolower($_SERVER["REQUEST_METHOD"]);
        $routes = $this->streamRouteMapping();

        try {
            $route = $this->validateRequest($requestUri, $routes, $requestMethod);
        } catch(Exception $e) {
            $this->eHandler->handle($e, "stream");
        }

        $classObject = Factory::getObject($route['class']);
        try {
            $this->validateClassMethod($classObject, $route['method']);
        } catch(Exception $e) {
            $this->eHandler->handle($e, "stream");
        }

        try {
            $result = call_user_func([$classObject, $route['method']]);
        } catch(Exception $e) {
            $this->eHandler->handle($e, "stream");
        }

        $this->returnResult($result);
    }

    private function validateRequest(string $requestUri, array $routes, string $requestMethod) {
        if(!array_key_exists($requestUri, $routes)) {
            throw new Exception("Page do not exists", HttpCodes::HTTP_NOT_FOUND);
        }
        $route = $routes[$requestUri];
        if(!in_array($route['request'], $this->supportedRequestMethods)) {
            throw new Exception("Method not supported", HttpCodes::METHOD_NOT_ALLOWED);
        }

        if($requestMethod !== $route['request']) {
            throw new Exception("Method invalid", HttpCodes::HTTP_FORBIDDEN);
        }

        return $route;
    }

    private function validateClassMethod(object $classObject, string $classMethod) {
        if(!method_exists($classObject, $classMethod)) {
            throw new Exception("Method not found", HttpCodes::INTERNAL_SERVER_ERROR);
        }
    }

    private function returnResult(string $result) {
        http_response_code(HttpCodes::HTTP_OK);
        echo $result;
    }

    public function redirect(string $url, ?int $code=null, ?array $data = array(), $timeout=0) {
        $code = ($code)? $code : HttpCodes::HTTP_OK;
        http_response_code($code);
        if(!empty($data)) {
            $httpQuery = http_build_query($data);
            $url = $url . "?" . $httpQuery;
        }
        if($timeout > 0) {
            header(sprintf("refresh:%d;url=%s", $timeout, $url));
        } else  {
            header("Location: " . $url);
        }
        die();
    }
}