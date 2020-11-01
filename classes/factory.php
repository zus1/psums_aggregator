<?php

namespace PsumsAggregator\Classes;

use PsumsAggregator\Classes\Controllers\BaseController;
use PsumsAggregator\Classes\Controllers\RulesController;
use PsumsAggregator\Classes\Controllers\StreamController;
use PsumsAggregator\Classes\Log\LoggerDb;
use PsumsAggregator\Classes\Log\LoggerFile;
use PsumsAggregator\Config\Config;
use PsumsAggregator\Extenders\ExceptionHandlerExtender;
use PsumsAggregator\Models\LoggerModel;
use PsumsAggregator\Models\LoggerStreamModel;
use PsumsAggregator\Models\RulesModel;
use PsumsAggregator\Models\RulesResultsModel;
use PsumsAggregator\Models\SignModel;
use PsumsAggregator\Models\StreamModel;
use PsumsAggregator\Models\StreamRulesModel;
use Exception;

/**
 * Class Factory
 * @package PsumsAggregator\Classes
 *
 * Main container for generating object and handling dependency injection.
 * Can return Objects, Extenders, Models and Libraries
 *
 */
class Factory
{
    const TYPE_STREAM_CONTROLLER = "stream-controller";
    const TYPE_BASE_CONTROLLER = "controller";
    const TYPE_DATABASE = "database";
    const TYPE_ROUTER = "router";
    const TYPE_HTTP_PARSER = "httpparser";
    const TYPE_REQUEST = 'request';
    const TYPE_VALIDATOR = 'validator';
    const TYPE_RESPONSE = "response";
    const TYPE_EXCEPTION_HANDLER = "exception-handler";
    const TYPE_SIGN = "sign";
    const TYPE_STREAM = "stream";
    const TYPE_RULES_CONTROLLER = "rules-controller";
    const TYPE_RULES = "rules";
    const TYPE_RULES_RESULT = "rules-results";

    const EXCEPTION_HANDLER_EXTENDER = "extender-exception-handler";

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
        self::TYPE_BASE_CONTROLLER => "getBaseController",
        self::TYPE_DATABASE => "getDatabase",
        self::TYPE_ROUTER => "getRouter",
        self::TYPE_HTTP_PARSER => "getHttpParser",
        self::TYPE_REQUEST => 'getRequest',
        self::TYPE_VALIDATOR => 'getValidator',
        self::TYPE_RESPONSE => "getResponse",
        self::TYPE_EXCEPTION_HANDLER => "getExceptionHandler",
        self::TYPE_STREAM_CONTROLLER => "getStreamController",
        self::TYPE_SIGN => "getSign",
        self::TYPE_STREAM => "getStream",
        self::TYPE_RULES_CONTROLLER => "getRulesController",
        self::TYPE_RULES => "getRules",
        self::TYPE_RULES_RESULT => "getRulesResults",
    );
    const EXTENDER_METHOD_MAPPING = array(
        self::EXCEPTION_HANDLER_EXTENDER => "getExtenderExceptionHandler",
    );
    const MODEL_TO_METHOD_MAPPING = array(
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
     * @throws Exception
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
     *
     * Pass true for singleton
     *
     * @param string $type
     * @param bool $singleton
     * @return Database|Router|Request|Validator|Response|Stream|Rules|RulesController
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
     *
     * Always singleton
     *
     * @param string $extenderType
     * @return ExceptionHandlerExtender
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
     *
     * Always singleton
     *
     * @param string $modelType
     * @return StreamRulesModel|RulesModel
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
     *
     * Used for including external libraries added to project (like Phinx or Mailer)
     *
     * @param string $libraryType
     * @return object
     */
    public static function getLibrary(string $libraryType) {
        if(!array_key_exists($libraryType, self::LIBRARY_TO_TYPE_MAPPING)) {
            return null;
        }

        return call_user_func([new self(), self::LIBRARY_TO_TYPE_MAPPING[$libraryType]]);
    }

    private function getBaseController() {
        return new BaseController($this->getResponse());
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
        return new RulesController($this->getValidator(), $this->getStream(), $this->getRules(), self::getLogger());
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

    private function getModelLogger() {
        return new LoggerModel($this->getValidator());
    }

    private function getDatabase() {
        return new Database();
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
        return new Validator($this->getRequest());
    }

    private function getResponse() {
        return new Response($this->getRequest());
    }
}