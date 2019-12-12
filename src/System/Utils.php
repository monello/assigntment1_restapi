<?php
namespace Src\System;


abstract class Utils
{
    public static function getJsonData($responseObj)
    {
        // Check tha the request's Content-Type header is JSON
        if($_SERVER['CONTENT_TYPE'] !== 'application/json') {
            $responseObj->errorResponse(["Content Type header not set to JSON"], 400);
        }
        // Check that the posted content is in JSON Format
        $rawPostData = file_get_contents('php://input');
        $data = json_decode($rawPostData);
        if (!$data) {
            $responseObj->errorResponse(["Request body is not valid JSON"], 400);
        }
        return $data;
    }

    public static function generateToken() {
        //  - using 24 random bytes to generate a token then encode this as base64
        //  - suffix with unix time stamp to guarantee uniqueness (stale tokens)
        return base64_encode(bin2hex(openssl_random_pseudo_bytes(24)).time());
    }
}