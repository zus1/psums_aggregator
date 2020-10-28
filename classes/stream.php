<?php


class Stream
{
    private $validator;
    private $logger;

    public function __construct(Validator $validator, LoggerInterface $logger) {
        $this->validator = $validator;
        $this->logger = $logger;
    }

    public function getModel() {
        return Factory::getModel(Factory::MODEL_STREAM);
    }

    public function addToStream(string $streamId, string $inputStream) {
        $this->logger->setType(Logger::LOGGER_STREAM)->log(json_encode(array("stream_id" => $streamId, "stream" => $inputStream), JSON_UNESCAPED_UNICODE), "input_stream");
        $streamArray = $this->getStreamArray($inputStream);
        $existingStream = $this->getModel()->select(array("stream"), array("stream_id" => $streamId));
        if(!$existingStream) {
            throw new Exception("Stream do no exist", HttpCodes::INTERNAL_SERVER_ERROR);
        }
        $existingStream = $existingStream[0]["stream"];

        if(empty($existingStream)) {
           $newStream = $streamArray;
        } else {
            $existingStream = json_decode($existingStream);
            if(json_last_error() !== JSON_ERROR_NONE) {
                throw new Exception(json_last_error_msg(), HttpCodes::INTERNAL_SERVER_ERROR);
            }
            if(!is_array($existingStream)) {
                throw new Exception("Wrong stream format", HttpCodes::INTERNAL_SERVER_ERROR);
            }
            $newStream = array_merge($existingStream, $streamArray);
        }

        $this->getModel()->update(array(
            "stream" => json_encode($newStream, JSON_UNESCAPED_UNICODE)
        ), array(
            'stream_id' => $streamId
        ));
    }

    private function getStreamArray(string $inputStream) {
        //first lets replace every symbol that is not latter, and lets have uniform brakes for later on
        $inputStream = preg_replace("/[!?_,;:.]/", " ", $inputStream);
        //now we get array from input string
        $array = preg_split("/(\r\n|\n|\r| )/", $inputStream);
        //now lets validate and format input array
        $formatted = array();
        array_walk($array, function ($value) use(&$formatted) {
           if($value !== "") {
               if($this->validator->validate("stream", array(Validator::FILTER_ALPHA_NUM), $value)->isFailed()) {
                   throw new Exception("Invalid word in input stream: " . $value, HttpCodes::HTTP_BAD_REQUEST);
               }
               $formatted[] = strtolower(trim($value));
           }
        });

        return $formatted;
    }
}