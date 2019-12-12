<?php
namespace src\Controller;

use Src\Controller\Response;
use Src\Model\SessionModel;

class AuthController
{

    private $db;
    private $sessionModel;

    public function __construct($db)
    {
        $this->db = $db;
        $this->sessionModel = new SessionModel($db);
    }

    // This function verifies the access-token and ensures the user account is active.
    // If anything does not pass it will return a 401, else all if fine
    public function authenticate()
    {
        $responseObj = new Response();

        // Check if the Auth Token is Provided
        if (!array_key_exists('HTTP_AUTHORIZATION', $_SERVER)) {
            $responseObj->errorResponse(["Access Token not Provided"], 401);
        }

        // Verify the token
        $accessToken = $_SERVER['HTTP_AUTHORIZATION'];
        $returnData = $this->sessionModel->getSession($accessToken);
        if ($returnData->rows_affected === 0) {
            $responseObj->errorResponse(["Invalid Access Token"], 401);
        }

        // Extract data in to variables
        $isActive = (bool) $returnData->session->is_active;
        $loginAttempts = (int) $returnData->session->login_attempts;
        $accessTokenExpiry = $returnData->session->access_token_expiry;

        // Check if the user is still active
        if (!$isActive) {
            $responseObj->errorResponse(["User account is not active"], 401);
        }
        // Check if the user's account is locked
        if ($loginAttempts >= 3) {
            $responseObj->errorResponse(["User account is locked"], 401);
        }

        // Check if access token has expired
        if(strtotime($accessTokenExpiry) < time()) {
            $responseObj->errorResponse(["Access token has expired"], 401);
        }
    }
}