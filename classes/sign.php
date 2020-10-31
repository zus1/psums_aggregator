<?php


class Sign
{
    public function getModel() {
        return Factory::getModel(Factory::MODEL_SIGN);
    }

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