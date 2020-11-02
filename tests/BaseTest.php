<?php
$_SERVER["DOCUMENT_ROOT"] = dirname(__DIR__);
include_once($_SERVER["DOCUMENT_ROOT"] . "/include.php");

class SingTest extends \PHPUnit\Framework\TestCase
{
    public function testResponse_ResponseException() {
        $responseObj = \PsumsAggregator\Classes\Factory::getObject(\PsumsAggregator\Classes\Factory::TYPE_RESPONSE);

        $message = "Test message";
        $code = 123;
        $expectedResponse = json_encode(array("error" => 1, "message" => $message));
        $response = $responseObj->returnApiException($message, $code);
        $this->assertEquals($expectedResponse, $response);
        $this->assertEquals($code, http_response_code());

        $code = 0;
        $responseObj->returnApiException($message, $code);
        $this->assertEquals(\PsumsAggregator\Classes\HttpCodes::INTERNAL_SERVER_ERROR, http_response_code());
    }

    public function testResponse_ResponseOk() {
        $responseObj = \PsumsAggregator\Classes\Factory::getObject(\PsumsAggregator\Classes\Factory::TYPE_RESPONSE);
        $message = "test message";
        $expectedResponse = json_encode(array("error" => 0, "message" => $message));
        $response = $responseObj->returnApiOk($message);
        $this->assertEquals($expectedResponse, $response);
    }

    public function testFactory() {
        $obj1 = \PsumsAggregator\Classes\Factory::getObject(\PsumsAggregator\Classes\Factory::TYPE_REQUEST);
        $this->assertInstanceOf(\PsumsAggregator\Classes\Request::class, $obj1);
        $obj1 = \PsumsAggregator\Classes\Factory::getObject(\PsumsAggregator\Classes\Factory::TYPE_RESPONSE);
        $this->assertInstanceOf(\PsumsAggregator\Classes\Response::class, $obj1);
        $obj1 = \PsumsAggregator\Classes\Factory::getObject(\PsumsAggregator\Classes\Factory::TYPE_SIGN);
        $this->assertInstanceOf(\PsumsAggregator\Classes\Sign::class, $obj1);
        $obj1 = \PsumsAggregator\Classes\Factory::getObject(\PsumsAggregator\Classes\Factory::TYPE_RULES_RESULT);
        $this->assertInstanceOf(\PsumsAggregator\Classes\RulesResults::class, $obj1);
        \PsumsAggregator\Classes\Database::$_inTest = true;
        $obj1 = \PsumsAggregator\Classes\Factory::getObject(\PsumsAggregator\Classes\Factory::TYPE_DATABASE);
        $this->assertInstanceOf(\PsumsAggregator\Classes\Database::class, $obj1);
        $obj1 = \PsumsAggregator\Classes\Factory::getObject(\PsumsAggregator\Classes\Factory::TYPE_STREAM);
        $this->assertInstanceOf(\PsumsAggregator\Classes\Stream::class, $obj1);
        $obj1 = \PsumsAggregator\Classes\Factory::getObject(\PsumsAggregator\Classes\Factory::TYPE_BASE_CONTROLLER);
        $this->assertInstanceOf(\PsumsAggregator\Classes\Controllers\BaseController::class, $obj1);
        $obj1 = \PsumsAggregator\Classes\Factory::getObject(\PsumsAggregator\Classes\Factory::TYPE_ROUTER);
        $this->assertInstanceOf(\PsumsAggregator\Classes\Router::class, $obj1);
        $obj1 = \PsumsAggregator\Classes\Factory::getObject(\PsumsAggregator\Classes\Factory::TYPE_RULES_CONTROLLER);
        $this->assertInstanceOf(\PsumsAggregator\Classes\Controllers\RulesController::class, $obj1);
        $obj1 = \PsumsAggregator\Classes\Factory::getObject(\PsumsAggregator\Classes\Factory::TYPE_STREAM_CONTROLLER);
        $this->assertInstanceOf(\PsumsAggregator\Classes\Controllers\StreamController::class, $obj1);
        $obj1 = \PsumsAggregator\Classes\Factory::getObject(\PsumsAggregator\Classes\Factory::TYPE_EXCEPTION_HANDLER);
        $this->assertInstanceOf(\PsumsAggregator\Classes\ExceptionHandler::class, $obj1);
        $obj1 = \PsumsAggregator\Classes\Factory::getObject(\PsumsAggregator\Classes\Factory::TYPE_VALIDATOR);
        $this->assertInstanceOf(\PsumsAggregator\Classes\Validator::class, $obj1);
        $obj1 = \PsumsAggregator\Classes\Factory::getObject(\PsumsAggregator\Classes\Factory::TYPE_HTTP_PARSER);
        $this->assertInstanceOf(\PsumsAggregator\Classes\HttpParser::class, $obj1);

        $model1 = \PsumsAggregator\Classes\Factory::getModel(\PsumsAggregator\Classes\Factory::MODEL_SIGN);
        $this->assertInstanceOf(\PsumsAggregator\Models\SignModel::class, $model1);
        $model1 = \PsumsAggregator\Classes\Factory::getModel(\PsumsAggregator\Classes\Factory::MODEL_STREAM);
        $this->assertInstanceOf(\PsumsAggregator\Models\StreamModel::class, $model1);
        $model1 = \PsumsAggregator\Classes\Factory::getModel(\PsumsAggregator\Classes\Factory::MODEL_RULES);
        $this->assertInstanceOf(\PsumsAggregator\Models\RulesModel::class, $model1);
        $model1 = \PsumsAggregator\Classes\Factory::getModel(\PsumsAggregator\Classes\Factory::MODEL_STREAM_RULES);
        $this->assertInstanceOf(\PsumsAggregator\Models\StreamRulesModel::class, $model1);
        $model1 = \PsumsAggregator\Classes\Factory::getModel(\PsumsAggregator\Classes\Factory::MODEL_RULES_RESULTS);
        $this->assertInstanceOf(\PsumsAggregator\Models\RulesResultsModel::class, $model1);
        $model1 = \PsumsAggregator\Classes\Factory::getModel(\PsumsAggregator\Classes\Factory::MODEL_LOGGER_STREAM);
        $this->assertInstanceOf(\PsumsAggregator\Models\LoggerStreamModel::class, $model1);
        $model1 = \PsumsAggregator\Classes\Factory::getModel(\PsumsAggregator\Classes\Factory::MODEL_LOGGER);
        $this->assertInstanceOf(\PsumsAggregator\Models\LoggerModel::class, $model1);


        $logger1 = \PsumsAggregator\Classes\Factory::getLogger(\PsumsAggregator\Classes\Factory::LOGGER_FILE);
        $this->assertInstanceOf(\PsumsAggregator\Classes\Log\LoggerFile::class, $logger1);
        $logger1 = \PsumsAggregator\Classes\Factory::getLogger(\PsumsAggregator\Classes\Factory::LOGGER_DB);
        $this->assertInstanceOf(\PsumsAggregator\Classes\Log\LoggerDb::class, $logger1);
    }

    public function testValidator() {
        $validator = \PsumsAggregator\Classes\Factory::getObject(\PsumsAggregator\Classes\Factory::TYPE_VALIDATOR);

        $value = "1234@";
        $validator->validate("key", array(\PsumsAggregator\Classes\Validator::FILTER_ALPHA_NUM), $value);
        $this->assertTrue($validator->isFailed());

        $this->assertNotEmpty($validator->getErrorMessages());
        $validator->resetMessages();
        $this->assertEmpty($validator->getErrorMessages());

        $value = "1234";
        $validator->validate("key", array(\PsumsAggregator\Classes\Validator::FILTER_ALPHA_NUM), $value);
        $this->assertFalse($validator->isFailed());

        $value = "1a2c34";
        $validator->validate("key", array(\PsumsAggregator\Classes\Validator::FILTER_ALPHA_NUM), $value);
        $this->assertFalse($validator->isFailed());

        $value = "1a2c34";
        $validator->validate("key", array(\PsumsAggregator\Classes\Validator::FILTER_ALPHA_NUM_PLUS), $value);
        $this->assertFalse($validator->isFailed());

        $value = "1a2c34&+'";
        $validator->validate("key", array(\PsumsAggregator\Classes\Validator::FILTER_ALPHA_NUM_PLUS), $value);
        $this->assertFalse($validator->isFailed());

        $value = "1a2c34&+'@";
        $validator->validate("key", array(\PsumsAggregator\Classes\Validator::FILTER_ALPHA_NUM_PLUS), $value);
        $this->assertTrue($validator->isFailed());

        $this->assertNotEmpty($validator->getErrorMessages());
        $validator->resetMessages();
        $this->assertEmpty($validator->getErrorMessages());

        $value = "1a2c34_-";
        $validator->validate("key", array(\PsumsAggregator\Classes\Validator::FILTER_ALPHA_NUM_UNDERSCORE), $value);
        $this->assertFalse($validator->isFailed());

        $value = "1a2c34&_-!";
        $validator->validate("key", array(\PsumsAggregator\Classes\Validator::FILTER_ALPHA_NUM_UNDERSCORE), $value);
        $this->assertTrue($validator->isFailed());

        $this->assertNotEmpty($validator->getErrorMessages());
        $validator->resetMessages();
        $this->assertEmpty($validator->getErrorMessages());
    }


}