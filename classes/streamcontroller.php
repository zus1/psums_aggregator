<?php


class StreamController
{
    private $validator;
    private $request;
    private $response;
    private $sign;
    private $stream;

    public function __construct(Validator $validator, Request $request, Response $response, Sign $sign, Stream $stream) {
        $this->validator = $validator;
        $this->request = $request;
        $this->response = $response;
        $this->sign = $sign;
        $this->stream = $stream;
    }

    public function inputStream() {
        $streamId = $this->request->inputOrThrow("id");
        $sing = $this->request->inputOrThrow("sign");
        $stream = $this->request->inputOrThrow("stream");

        if($this->validator->validate("id", array(Validator::FILTER_ALPHA_NUM))->isFailed()) {
            throw new Exception($this->validator->getMessages()[0], HttpCodes::HTTP_BAD_REQUEST);
        }
        $this->validator->resetMessages();
        if($this->validator->validate("sign", array(Validator::FILTER_ALPHA_NUM_UNDERSCORE))->isFailed()) {
            throw new Exception($this->validator->getMessages()[0], HttpCodes::HTTP_BAD_REQUEST);
        }
        $this->sign->checkSignKey($streamId, $sing);
        $this->stream->addToStream($streamId, $stream);

        return $this->response->returnApiOk("ok");
    }
}