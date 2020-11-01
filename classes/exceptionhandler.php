<?php

namespace PsumsAggregator\Classes;

use Exception;
use PsumsAggregator\Extenders\ExceptionHandlerExtender;
use PsumsAggregator\Interfaces\LoggerInterface;

/**
 * Class ExceptionHandler
 * @package PsumsAggregator\Classes
 *
 * Class for handling project exceptions.
 * Can be extended by using PsumsAggregator\Extenders\ExceptionHandlerExtender
 *
 */
class ExceptionHandler
{
    const EXCEPTION_DEFAULT = "stream";

    private $extender;
    private $logger;

    public function __construct(ExceptionHandlerExtender $extender, LoggerInterface $logger) {
        $this->extender = $extender;
        $this->logger = $logger;
    }

    /**
     *
     * Return method to use, depending on exception type
     * Extendable with PsumsAggregator\Extenders\ExceptionHandlerExtender
     *
     * @return array
     */
    private function getTypeTOHandlerMapping() {
        $defaultMapping = array(
            self::EXCEPTION_DEFAULT => "handleException",
        );
        $extended = $this->extender->extend();

        return array_merge($defaultMapping, $extended);
    }

    /**
     *
     * Calls handling method depending on type parameter
     *
     * @param Exception $e
     * @param string|null $type
     * @param bool $return
     * @return mixed|null
     * @throws Exception
     */
    public function handle(Exception $e, ?string $type="", $return=false) {
        if($type === "") {
            $type = self::EXCEPTION_DEFAULT;
        }
        if(!array_key_exists($type, $this->getTypeTOHandlerMapping())) {
            $this->logger->logException($e);
            throw $e;
        }

        $method = $this->getTypeTOHandlerMapping()[$type];
        if(!method_exists($this, $method)) {
            $ret = call_user_func_array([$this->extender, $method], array($e));
        } else {
            $ret = call_user_func_array([$this, $method], array($e));
        }

        if($return === true) {
            return $ret;
        }
        return null;
    }

    /**
     *
     * Default fallback if no type supplied
     *
     * @param Exception $e
     */
    private function handleException(Exception $e) {
        $this->logger->logException($e);
    }
}