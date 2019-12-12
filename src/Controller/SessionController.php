<?php
namespace Src\Controller;

use Src\Controller\Response;
use Src\Model\SessionModel;
use Src\System\SessionException;
use Src\System\Utils;

class SessionController
{

    private $db;
    private $requestMethod;
    private $sessionId;
    private $uri;
    private $access_seconds;
    private $refresh_seconds;

    private $sessionModel;

    public function __construct($db, $requestMethod, $uri, $sessionId)
    {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->uri = $uri;
        $this->sessionId = $sessionId;
        $this->access_seconds = 60 * 20;
        $this->refresh_seconds = 60 * 60 * 24 * 14;

        $this->sessionModel = new SessionModel($db);
    }

    public function processRequest()
    {
        switch ($this->requestMethod) {
            case 'POST':
                $this->createSession();
                break;
            case 'PATCH':
                if ($this->sessionId) {
                    $this->refreshSession();
                }
                else {
                    $responseObj = new Response();
                    $responseObj->errorResponse(["Method Forbidden"], 403);
                };
                break;
            case 'DELETE':
                if ($this->sessionId) {
                    $this->deleteSession();
                }
                else {
                    $responseObj = new Response();
                    $responseObj->errorResponse(["Method Forbidden"], 403);
                };
                break;
            default:
                $responseObj = new Response();
                $responseObj->errorResponse(["Method Not Allowed"], 405);
        }
    }

    private function createSession()
    {
        sleep(1);
        $responseObj = new Response();
        $requestData = Utils::getJsonData($responseObj);

        // Attempt to fetch the user details
        $userData = $this->sessionModel->findUser($requestData->username);
        if ((int) $userData->rows_affected === 0) {
            $responseObj->errorResponse(["Username or Password is incorrect"], 401);
        }

        // save returned details into variables
        $user_id = (int) $userData->user->id;
        $password = $requestData->password;
        $hashed_password = $userData->user->password;
        $is_active = (bool) $userData->user->is_active;
        $login_attempts = (int) $userData->user->login_attempts;

        // Check if the user is still active
        if (!$is_active) {
            $responseObj->errorResponse(["User account is not active"], 401);
        }
        // Check if the user's account is locked
        if ($login_attempts >= 3) {
            $responseObj->errorResponse(["User account is locked"], 401);
        }

        // Verify the password
        if (!password_verify($password, $hashed_password)) {
            $this->sessionModel->incrementLoginAttempts($user_id);
            $responseObj->errorResponse(["Username or Password is incorrect"], 401);
        }

        // Generate the access-token and refresh_tokens
        $access_token = Utils::generateToken();
        $refresh_token = Utils::generateToken();

        // Create the Login Session
        $returnData = $this->sessionModel->createSession(
            $user_id,
            $access_token,
            $this->access_seconds,
            $refresh_token,
            $this->refresh_seconds
        );
        $responseObj->successResponse(["Login Successful"], 201, $returnData);
    }

    private function refreshSession()
    {
        $responseObj = new Response();
        if (!array_key_exists('HTTP_AUTHORIZATION', $_SERVER)) {
            $responseObj->errorResponse(["Access Token not Provided"], 401);
        }
        $accessToken = $_SERVER['HTTP_AUTHORIZATION'];
        $requestData = Utils::getJsonData($responseObj);
        // Check if the refresh-token was provided
        if (!($requestData->refresh_token ?? false)) {
            $responseObj->errorResponse(["Refresh Token not Provided"], 400);
        }
        $returnData = $this->sessionModel->findRefreshSession($this->sessionId, $accessToken, $requestData->refresh_token);
        if ($returnData->rows_affected === 0) {
            $responseObj->errorResponse(["Access Token or Refresh Token does not match the Session Id"], 500);
        }
        // Extract data in to variables
        $sessionId = (int) $returnData->session->session_id;
        $userId = (int) $returnData->session->user_id;
        $refreshToken = $returnData->session->refresh_token;
        $isActive = (bool) $returnData->session->is_active;
        $loginAttempts = (int) $returnData->session->login_attempts;
        $accessTokenExpiry = $returnData->session->access_token_expiry;
        $refreshTokenExpiry = $returnData->session->refresh_token_expiry;

        // Check if the user is still active
        if (!$isActive) {
            $responseObj->errorResponse(["User account is not active"], 401);
        }
        // Check if the user's account is locked
        if ($loginAttempts >= 3) {
            $responseObj->errorResponse(["User account is locked"], 401);
        }
        // Check if the refresh-token is still active
        if (strtotime($accessTokenExpiry) < time()) {
            $responseObj->errorResponse(["Refresh Token has expired", "Please log in again"], 401);
        }

        // Generate the access-token and refresh_tokens
        $newAccessToken = Utils::generateToken();
        $newRefreshToken = Utils::generateToken();

        // Create the Login Session
        $returnData = $this->sessionModel->updateSession(
            $sessionId,
            $userId,
            $accessToken,
            $newAccessToken,
            $this->access_seconds,
            $refreshToken,
            $newRefreshToken,
            $this->refresh_seconds
        );

        $responseObj->successResponse(["Token Successfully Refreshed"], 200, $returnData);

    }

    private function deleteSession()
    {
        $responseObj = new Response();
        if (!array_key_exists('HTTP_AUTHORIZATION', $_SERVER)) {
            $responseObj->errorResponse(["Access Token not Provided"], 401);
        }
        $accessToken = $_SERVER['HTTP_AUTHORIZATION'];
        $returnData = $this->sessionModel->deleteSession($this->sessionId, $accessToken);

        if ($returnData->rows_affected === 0) {
            $responseObj->errorResponse(["Failed to logout:", "Invalid Token Provided"], 400);
        }
        $responseObj->successResponse(["Logout Successful"], 200, $returnData);
    }

}