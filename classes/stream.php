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
        $inputStream = preg_replace("/[!?_,;:.\-]/", " ", $inputStream);
        //now we get array from input string
        $array = preg_split("/(\r\n|\n|\r| )/", $inputStream);
        //now lets validate and format input array
        $formatted = array();
        array_walk($array, function ($value) use(&$formatted) {
           if($value !== "") {
               if($this->validator->validate("stream", array(Validator::FILTER_ALPHA_NUM_PLUS), $value)->isFailed()) {
                   throw new Exception("Invalid word in input stream: " . $value, HttpCodes::HTTP_BAD_REQUEST);
               }
               $formatted[] = strtolower(trim($value));
           }
        });

        return $formatted;
    }

    public function getStreamsForCycle() {
        $chunkSize = (int)Factory::getObject(Factory::TYPE_DATABASE, true)->getSetting("aggregator_chunk_size", 5);
        $streams = $this->getModel()->select(array("stream_id", "stream"), array());
        if(!$streams) {
            throw new Exception("No streams available");
        }
        $chunkedStreams = array();
        $lowestCount = null;
        array_walk($streams, function ($value) use(&$chunkedStreams, $chunkSize, &$lowestCount) {
            if(empty($value["stream"])) {
                $stream = array();
            } else {
                $stream = json_decode($value["stream"], true);
            }

            $chunked = array_values(array_filter(array_chunk($stream, $chunkSize), function($value) use($chunkSize) {
                return count($value) === $chunkSize;
            }));

            if(count($chunked) < $lowestCount || $lowestCount === null) {
                $lowestCount = count($chunked);
            }

            $chunkedStreams[$value["stream_id"]] = array("original" => $stream, "chunked" => $chunked);
        });

        $chunkedStreams = $this->trimChunkedStreams($chunkedStreams, $lowestCount);
        return $chunkedStreams;
    }

    private function trimChunkedStreams(array $streams, int $lowestCount) {
        return array_map(function($stream) use($lowestCount) {
            return array(
                "original" => $stream["original"],
                "chunked" => array_slice($stream["chunked"], 0, $lowestCount, true)
            );
        }, $streams);
    }

    public function removeUsedInCycle(array $streams) {
        array_walk($streams, function ($orgChunk, $streamId) {
            if(count($orgChunk["chunked"]) === 0) {
                return;
            }
            $usedStr = "";
            array_walk($orgChunk["chunked"], function($value) use(&$usedStr) {
                $usedStr .= implode(",", $value) . ",";
            });
            $usedStr = substr($usedStr, 0, strlen($usedStr) - 1);
            $used = explode(",", $usedStr);
            $left = array_slice($orgChunk["original"], count($used));

            $this->getModel()->update(array("stream" => json_encode($left, JSON_UNESCAPED_UNICODE)), array("stream_id" => $streamId));
        });
    }
}