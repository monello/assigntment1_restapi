<?php


namespace Src\Controller;


class Response {
    private $success;
    private $messages = [];
    private $data;
    private $httpStatusCode;
    private $responseData = [];

    // Define some Setters
    public function setSuccess($success)
    {
        $this->success = $success;
    }
    public function setData($data)
    {
        $this->data = $data;
    }
    public function setHttpStatusCode($httpStatusCode)
    {
        $this->httpStatusCode = $httpStatusCode;
    }
    public function addMessage($message)
    {
        $this->messages[] = $message;
    }

    /**
     * All requests will call this function to return the response object to the caller in a consistent format
     */
    public function send()
    {
        // Ensure that the response header is set to JSON, utf-8
        header('Content-type:application/json;charset=utf-8');
        $isValid = $this->validateResponse();
        $this->buildResponse($isValid);
        // Encode the responseData object to JSON
        echo json_encode($this->responseData);
    }

    /**
     * This function does a few checks to ensure the response was set up correctly
     * @return bool $isValid
     */
    private function validateResponse()
    {
        $isValid = true;
        // Ensure the httpStatusCode is numeric
        if (!is_numeric($this->httpStatusCode)) {
            $isValid = false;
        }
        // Ensure that the success flag is explicitly set to either true or false
        if (!is_bool($this->success)) {
            $isValid = false;
        }
        return $isValid;
    }

    /**
     * This function prepares an error response if there was a problem with the Response object else passes along the valid Response
     * @param bool $isValid
     */
    private function buildResponse($isValid)
    {
        if (!$isValid) {
            http_response_code(500);
            $this->responseData['statusCode'] = 500;
            $this->responseData['success'] = false;
            $this->addMessage("Problem creating the response");
            $this->responseData['messages'] = $this->messages;
        } else {
            http_response_code($this->httpStatusCode);
            $this->responseData['statusCode'] = $this->httpStatusCode;
            $this->responseData['success'] = $this->success;
            $this->responseData['messages'] = $this->messages;
            $this->responseData['data'] = $this->data;
        }
    }
        public function successResponse($messages, $code=200, $returnData=[])
    {
        $this->setHttpStatusCode($code);
        $this->setSuccess(true);
        if (count($messages)) {
            foreach ($messages as $message) {
                $this->addMessage($message);
            }
        }
        $this->setData($returnData);
        $this->send();
        exit;
    }

    public function errorResponse($messages, $code)
    {
        $this->setHttpStatusCode($code);
        $this->setSuccess(false);
        if (count($messages)) {
            foreach ($messages as $message) {
                $this->addMessage($message);
            }
        }
        $this->send();
        exit;
    }
}