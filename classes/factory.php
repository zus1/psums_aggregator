<?php

class Factory
{
    const TYPE_STREAM_CONTROLLER = "stream-controller";
    const TYPE_CONTROLLER = "controller";
    const TYPE_DATABASE = "database";
    const TYPE_HTML_PARSER = "htmlparser";
    const TYPE_ROUTER = "router";
    const TYPE_HTTP_PARSER = "httpparser";
    const TYPE_REQUEST = 'request';
    const TYPE_VALIDATOR = 'validator';
    const TYPE_RESPONSE = "response";
    const TYPE_CMS = "cms";
    const TYPE_WEB = "web";
    const TYPE_JSON_PARSER = "json-parser";
    const TYPE_DATE_HANDLER = "date-handler";
    const TYPE_EXCEPTION_HANDLER = "exception-handler";
    const TYPE_SIGN = "sign";
    const TYPE_STREAM = "stream";
    const TYPE_RULES_CONTROLLER = "rules-controller";
    const TYPE_RULES = "rules";
    const TYPE_RULES_RESULT = "rules-results";

    const EXTENDER_HTML_PARSER = "extender_html_parser";
    const EXCEPTION_HANDLER_EXTENDER = "extender-exception-handler";

    const MODEL_LOGGER_WEB = "model-logger-web";
    const MODEL_LOGGER_API = "model-logger-api";
    const MODEL_LOGGER_STREAM = "model-logger-stream";
    const MODEL_LOGGER = "model-logger-default";
    const MODEL_SIGN = "model-sign";
    const MODEL_STREAM = "model-stream";
    const MODEL_RULES = "model-rules";
    const MODEL_RULES_RESULTS = "model-rule-results";
    const MODEL_STREAM_RULES = "model-stream-rules";

    const LOGGER_FILE = 'file';
    const LOGGER_DB = "db";

    const TYPE_METHOD_MAPPING = array(
        self::TYPE_CONTROLLER => "getController",
        self::TYPE_DATABASE => "getDatabase",
        self::TYPE_HTML_PARSER => "getHtmlParser",
        self::TYPE_ROUTER => "getRouter",
        self::TYPE_HTTP_PARSER => "getHttpParser",
        self::TYPE_REQUEST => 'getRequest',
        self::TYPE_VALIDATOR => 'getValidator',
        self::TYPE_RESPONSE => "getResponse",
        self::TYPE_CMS => "getCms",
        self::TYPE_WEB => "getWeb",
        self::TYPE_JSON_PARSER => "getJsonParser",
        self::TYPE_DATE_HANDLER => "getDateHandler",
        self::TYPE_EXCEPTION_HANDLER => "getExceptionHandler",
        self::TYPE_STREAM_CONTROLLER => "getStreamController",
        self::TYPE_SIGN => "getSign",
        self::TYPE_STREAM => "getStream",
        self::TYPE_RULES_CONTROLLER => "getRulesController",
        self::TYPE_RULES => "getRules",
        self::TYPE_RULES_RESULT => "getRulesResults",
    );
    const EXTENDER_METHOD_MAPPING = array(
        self::EXTENDER_HTML_PARSER => "getExtenderHtmlParser",
        self::EXCEPTION_HANDLER_EXTENDER => "getExtenderExceptionHandler",
    );
    const MODEL_TO_METHOD_MAPPING = array(
        self::MODEL_LOGGER_WEB => "getModelLoggerWeb",
        self::MODEL_LOGGER_API => "getModelLoggerApi",
        self::MODEL_LOGGER_STREAM => "getModelLoggerStream",
        self::MODEL_LOGGER => "getModelLogger",
        self::MODEL_SIGN => "getModelSign",
        self::MODEL_STREAM => "getModelStream",
        self::MODEL_RULES => "getModelRules",
        self::MODEL_RULES_RESULTS => "getModelRulesResults",
        self::MODEL_STREAM_RULES => "getModelStreamRules",
    );
    const LIBRARY_TO_TYPE_MAPPING = array();

    const LOGGER_TO_METHOD_MAPPING = array(
        self::LOGGER_DB => "getDbLogger",
        self::LOGGER_FILE => "getFileLogger",
    );
    private static $instances = array();

    /**
     * @param string|null $type
     * @return LoggerFile|LoggerDb
     */
    public static function getLogger(?string $type="") {
        if($type === "") {
            $type = Config::get(Config::LOG_DRIVER);
        }
        if(!array_key_exists($type, self::LOGGER_TO_METHOD_MAPPING)) {
            return null;
        }
        if(!array_key_exists($type, self::$instances)) {
            $logger = call_user_func([new self(), self::LOGGER_TO_METHOD_MAPPING[$type]]);
            self::$instances[$type] = $logger;
        }

        return self::$instances[$type];
    }

    /**
     * @param string $type
     * @param bool $singleton
     * @return BaseController|Database|HtmlParser|Router|Request|Validator|Response|Web|DateHandler|Stream|Rules|RulesController
     */
    public static function getObject(string $type, bool $singleton=false) {
        if(!array_key_exists($type, self::TYPE_METHOD_MAPPING)) {
            return null;
        }
        if($singleton === true) {
            if(array_key_exists($type, self::$instances)) {
                return self::$instances[$type];
            } else {
                $object = call_user_func([new self(), self::TYPE_METHOD_MAPPING[$type]]);
                self::$instances[$type] = $object;
                return $object;
            }
        }

        return call_user_func([new self(), self::TYPE_METHOD_MAPPING[$type]]);
    }

    /**
     * @param string $extenderType
     * @return HtmlParserExtender|ExceptionHandlerExtender
     */
    public static function getExtender(string $extenderType) {
        if(!array_key_exists($extenderType, self::EXTENDER_METHOD_MAPPING)) {
            return null;
        }
        if(!isset(self::$instances[$extenderType])) {
            $object = call_user_func([new self(), self::EXTENDER_METHOD_MAPPING[$extenderType]]);
            self::$instances[$extenderType] = $object;
        }

        return self::$instances[$extenderType];
    }

    /**
     * @param string $modelType
     * @return LoggerWebModel|LoggerApiModel
     */
    public static function getModel(string $modelType) {
        if(!array_key_exists($modelType, self::MODEL_TO_METHOD_MAPPING)) {
            return null;
        }
        if(!isset(self::$instances[$modelType])) {
            $object = call_user_func([new self(), self::MODEL_TO_METHOD_MAPPING[$modelType]]);
            self::$instances[$modelType] = $object;
        }

        return self::$instances[$modelType];
    }

    /**
     * @param string $libraryType
     * @return object
     */
    public static function getLibrary(string $libraryType) {
        if(!array_key_exists($libraryType, self::LIBRARY_TO_TYPE_MAPPING)) {
            return null;
        }

        return call_user_func([new self(), self::LIBRARY_TO_TYPE_MAPPING[$libraryType]]);
    }

    private function getRules() {
        return new Rules($this->getRulesResults());
    }

    private function getRulesResults() {
        return new RulesResults();
    }

    private function getModelStreamRules() {
        return new StreamRulesModel($this->getValidator());
    }

    private function getModelRulesResults() {
        return new RulesResultsModel($this->getValidator());
    }

    private function getModelRules() {
        return new RulesModel($this->getValidator());
    }

    private function getRulesController() {
        return new RulesController($this->getValidator(), $this->getStream(), $this->getRules());
    }

    private function getModelLoggerStream() {
        return new LoggerStreamModel($this->getValidator());
    }

    private function getStream() {
        return new Stream($this->getValidator(), self::getLogger());
    }

    private function getModelStream() {
        return new StreamModel($this->getValidator());
    }

    private function getSign() {
        return new Sign();
    }

    private function getModelSign() {
        return new SignModel($this->getValidator());
    }

    private function getStreamController() {
        return new StreamController($this->getValidator(), $this->getRequest(), $this->getResponse(), $this->getSign(), $this->getStream());
    }

    private function getExceptionHandler() {
        return new ExceptionHandler($this->getExtenderExceptionHandler(), self::getLogger());
    }

    private function getExtenderExceptionHandler() {
        return new ExceptionHandlerExtender(self::getLogger(), $this->getResponse());
    }

    private function getDbLogger() {
        return new LoggerDb();
    }

    private function getFileLogger() {
        return new LoggerFile();
    }

    private function getModelLoggerWeb() {
        return new LoggerWebModel($this->getValidator());
    }

    private function getModelLoggerApi() {
        return new LoggerApiModel($this->getValidator());
    }

    private function getModelLogger() {
        return new LoggerModel($this->getValidator());
    }

    private function getController() {
        return new BaseController($this->getRequest(), $this->getHtmlParser(), $this->getValidator(), $this->getResponse(), $this->getCms(), $this->getWeb());
    }

    private function getDatabase() {
        return new Database();
    }

    private function getHtmlParser() {
        return new HtmlParser($this->getRequest(), $this->getExtenderHtmlParser());
    }

    private function getRouter() {
        return new Router($this->getExceptionHandler());
    }

    private function getHttpParser() {
        return new HttpParser();
    }

    private function getRequest() {
        return new Request();
    }

    private function getValidator() {
        return new Validator($this->getRequest(), $this->getHtmlParser());
    }

    private function getResponse() {
        return new Response($this->getHtmlParser(), $this->getRequest());
    }

    private function getCms() {
        return new Cms($this->getValidator());
    }

    private function getWeb() {
        return new Web($this->getCms());
    }

    private function getExtenderHtmlParser() {
        return new HtmlParserExtender();
    }

    private function getJsonParser() {
        return new JsonParser();
    }

    public function getDateHandler() {
        return new DateHandler();
    }
}