<?php


class Validator
{
    const FILTER_ALPHA_NUM = "alpha_num";
    const FILTER_CUSTOM = "custom";
    const FILTER_ALPHA_NUM_UNDERSCORE = "alpha_num_underscore";
    const FILTER_ALPHA_NUM_PLUS = "alpha_num_plus";

    private $messages = array();

    private $request;

    public function __construct(Request $request) {
        $this->request = $request;
    }

    protected function getValidFilters() {
        return array(
            self::FILTER_ALPHA_NUM, self::FILTER_ALPHA_NUM_UNDERSCORE, self::FILTER_ALPHA_NUM_PLUS, self::FILTER_CUSTOM
        );
    }

    protected function getFilterToMethodMapping() {
        return array(
            self::FILTER_ALPHA_NUM => "filterAlphaNumeric",
            self::FILTER_ALPHA_NUM_UNDERSCORE => 'filterAlphaNumUnderscore',
            self::FILTER_ALPHA_NUM_PLUS => "filterAlphaNumPlus",
        );
    }

    protected function getErrorMessagesDefinition() {
        return array(
            self::FILTER_ALPHA_NUM => "Field {field} can contain only letters and numbers",
            self::FILTER_ALPHA_NUM_PLUS => "Field {field} can contain only letters and letters plus symbols: & '",
            self::FILTER_CUSTOM => "Field {field} contains invalid characters",
            self::FILTER_ALPHA_NUM_UNDERSCORE => "Field {field} can contain only letters, numbers and underscore",
        );
    }

    public function getLanguageFilters() {
        return array(self::FILTER_ALPHA_NUM, self::FILTER_ALPHA_NUM_PLUS);
    }

    protected function getErrorMessage(string $field, string $filter, ?string $num=null) {
        $message = str_replace("{field}", $field, $this->getErrorMessagesDefinition()[$filter]);
        if($num !== null) {
            $message = str_replace("{num}", $num, $message);
        }

        return $message;
    }

    public function validate(string $field, array $filters, $value=null, ?string $customPattern="") {
        if(!$value) {
            $value = $this->request->input($field);
        }
        foreach($filters as $filter) {
            $filterCheck = explode(":", $filter);
            $check = null;
            $funcParams = array($value);
            if(count($filterCheck) === 2) {
                $filter = $filterCheck[0];
                $check = $filterCheck[1];
                $funcParams[] = intval($check);
            }
            if(!in_array($filter, $this->getValidFilters())) {
                throw new Exception("Validator filter invalid", HttpCodes::INTERNAL_SERVER_ERROR);
            }
            if($filter === self::FILTER_CUSTOM) {
                if($customPattern === "") {
                    throw new Exception("Validator custom pattern missing", HttpCodes::INTERNAL_SERVER_ERROR);
                }
                $filtered = $this->filter($value, $customPattern);
            } else {
                $filtered = call_user_func_array([$this, $this->getFilterToMethodMapping()[$filter]], $funcParams);
            }
            if($filtered !== $value) {
                $this->messages[] = $this->getErrorMessage($field, $filter, $check);
            } else {
                $this->messages[] = "ok";
            }
        }

        return $this;
    }

    public function getMessages() {
        return $this->messages;
    }

    public function getErrorMessages() {
        return array_filter($this->messages, function($value) {
            return $value !== "ok";
        });
    }

    public function isFailed() {
        $errorMessages = $this->getErrorMessages();

        if(!empty($errorMessages)) {
            return true;
        }

        return false;
    }

    public function resetMessages() {
        $this->messages = array();
    }

    public function filterAlphaNumeric($value) {
        return $this->filter($value, "/[^A-Za-z0-9]/");
    }

    public function filterAlphaNumPlus($value) {
        return $this->filter($value, "/[^A-Za-z0-9&\']/");
    }

    public function filterAlphaDash($value) {
        return $this->filter($value, "/[^A-Za-z_ ]/");
    }

    public function filterAlphaNumUnderscore($value) {
        return $this->filter($value, "/[^A-Za-z0-9_-]/");
    }

    public function filter($value, string $pattern) {
        return preg_replace($pattern, "", $value);
    }
}