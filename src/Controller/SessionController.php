<?php
namespace Src\Controller;

use Src\Controller\Response;
use Src\Model\SessionModel;
use Src\System\SessionException;
use Src\System\Utils;

class SessionController {

    /**
     * POST /session = create a new session (Log In)
     * DELETE /session/1 = delete an existing session (Log Out)
     * PATCH /session/1 = Refresh Session/Token
     */

    private $db;
    private $requestMethod;
    private $sessionId;
    private $uri;

    private $sessionModel;

    public function __construct($db, $requestMethod, $uri, $sessionId)
    {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->uri = $uri;
        $this->sessionId = $sessionId;

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
        $userData = $this->sessionModel->find($requestData->username);
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
        //  - using 24 random bytes to generate a token then encode this as base64
        //  - suffix with unix time stamp to guarantee uniqueness (stale tokens)
        $access_token = base64_encode(bin2hex(openssl_random_pseudo_bytes(24)).time());
        $refresh_token = base64_encode(bin2hex(openssl_random_pseudo_bytes(24)).time());
        $access_token_expiry_seconds = 60 * 20;
        $refresh_token_expiry_seconds = 60 * 60 * 24 * 14;

        // Create the Login Session
        $returnData = $this->sessionModel->createSession(
            $user_id,
            $access_token,
            $access_token_expiry_seconds,
            $refresh_token,
            $refresh_token_expiry_seconds
        );
        $responseObj->successResponse(["Login Successful"], 201, $returnData);
    }

    private function refreshSession()
    {
        echo "REFRESH SESSION\n";
    }

    private function deleteSession()
    {
        echo "LOGGED OUT - EXISTING SESSION DELETED\n";
    }

}