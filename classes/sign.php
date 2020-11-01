<?php

namespace PsumsAggregator\Classes;

use Exception;

/**
 * Class Sign
 * @package PsumsAggregator\Classes
 *
 * Class that will check sing key added to input streams
 *
 */
class Sign
{
    public function getModel() {
        return Factory::getModel(Factory::MODEL_SIGN);
    }

    /**
     *
     * Check if sing key is supplied with input stream
     * Checks if key ids valid
     * Denies access if any check do not pass
     *
     * @param string $streamId
     * @param string $key
     * @throws Exception
     */
    public function checkSignKey(string $streamId, string $key) {
        $singKey = $this->getModel()->select(array("sign_key"), array("stream_id" => $streamId));
        if(!$singKey) {
            throw new Exception("Stream not supported", HttpCodes::HTTP_FORBIDDEN);
        }
        if($singKey[0]["sign_key"] !== $key) {
            throw new Exception("sign in failed", HttpCodes::HTTP_BAD_REQUEST);
        }
    }
}