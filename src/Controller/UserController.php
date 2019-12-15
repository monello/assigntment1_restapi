<?php
namespace Src\Controller;

use Src\System\UserException;
use Src\Model\UserModel;
use Src\System\Utils;

class UserController
{
    private $db;
    private $requestMethod;
    private $userId;
    private $uri;

    private $userModel;

    public function __construct($db, $requestMethod, $uri, $userId)
    {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->uri = $uri;
        $this->userId = $userId;

        $this->userModel = new UserModel($db);
    }

    public function processRequest()
    {
        $auth = new AuthController($this->db);

        switch ($this->requestMethod) {
            case 'GET':
                if ($this->userId) {
                    $auth->authenticate();
                    $this->getUser();
                }
                else {
                    // Don't allow listing all users (this could be allowed in the future if there were Admin Users).
                    $responseObj = new Response();
                    $responseObj->errorResponse(["Method Forbidden"], 403);
                };
                break;
            case 'POST':
                 $this->createUser();
                break;
            case 'PUT':
                $auth->authenticate();
                $this->updateUser();
                break;
            case 'PATCH':
                if ($this->userId) {
                    $auth->authenticate();
                    $this->patchUser();
                }
                else {
                    // Cannot Patch any data without a user-id
                    $responseObj = new Response();
                    $responseObj->errorResponse(["Method Forbidden"], 403);
                };
                break;
            case 'DELETE':
                if ($this->userId) {
                    $auth->authenticate();
                    $this->deleteUser();
                }
                else {
                    // Cannot Patch any data without a user-id
                    $responseObj = new Response();
                    $responseObj->errorResponse(["Method Forbidden"], 403);
                };
                break;
            default:
                $responseObj = new Response();
                $responseObj->errorResponse(["Method Not Allowed"], 405);
        }
    }


    private function getUser()
    {
        $responseObj = new Response();
        $result = $this->userModel->find($this->userId);
        if (!$result["rows_affected"]) {
            $responseObj->errorResponse(["User Record not found"], 404);
        }
        $responseObj->successResponse(["Success"], 200, $result);
    }

    // This would be registering a new user, so there would not be an auth-token for this user
    private function createUser()
    {
        $responseObj = new Response();
        $requestData = Utils::getJsonData($responseObj);
        try {
            $userData = $this->userModel->validateUser($requestData, false);
        } catch (\Exception $e) {
            $responseObj->errorResponse(["Unable to create User", $e->getMessage()], 400);
        }
        $this->userModel->checkUniqueUsername($userData->username);
        $this->userModel->checkUniqueEmail($userData->email);
        $rowsAffected = $this->userModel->insert($userData);
        // prep the return data
        $returnData = [];
        $returnData['rows_affected'] = $rowsAffected;
        $returnData['users'] = [$userData];
        $responseObj->successResponse(["User Created"], 201, $returnData);
    }

    private function patchUser()
    {
        switch (true) {
            case (preg_match('/\/username\/?$/', $this->uri)):
                $this->updateUsername();
                break;
            case (preg_match('/\/password\/?$/', $this->uri)):
                $this->updatePassword();
                break;
            case (preg_match('/\/email\/?$/', $this->uri)):
                $this->updateEmail();
                break;
            default:
                $responseObj = new Response();
                $responseObj->errorResponse(["Method Forbidden"], 403);
        }
    }

    private function updateUsername()
    {
        $responseObj = new Response();
        $requestData = Utils::getJsonData($responseObj);
        try {
            $username = $this->userModel->validateUsername($requestData->username);
        } catch (\Exception $e) {
            $responseObj->errorResponse(["Unable to update Username", $e->getMessage()], 400);
        }
        $this->userModel->checkUniqueUsername($username);
        $rowsAffected = $this->userModel->updateUsername($this->userId, $username);
        // prep the return data
        $returnData = [];
        $returnData['rows_affected'] = $rowsAffected;
        $returnData['username'] = [$username];
        $responseObj->successResponse(["Username Updated"], 200, $returnData);
    }

    private function updatePassword()
    {
        $responseObj = new Response();
        $requestData = Utils::getJsonData($responseObj);
        try {
            $hashed_password = $this->userModel->hashPassword($requestData->password);
        } catch (\Exception $e) {
            $responseObj->errorResponse(["Unable to update Password", $e->getMessage()], 400);
        }

        $rowsAffected = $this->userModel->updatePassword($this->userId, $hashed_password);
        // prep the return data
        $returnData = [];
        $returnData['rows_affected'] = $rowsAffected;
        $responseObj->successResponse(["Password Updated"], 200, $returnData);
    }

    // TODO Ideally you'd want to have the user confirm the new email address, have a confirmed (Y/N) flag and some token that can be sent via email.
    //  - Token management would include expiring the token automatically after X-time and expiring it immediately after user confirmed
    private function updateEmail()
    {
        $responseObj = new Response();
        $requestData = Utils::getJsonData($responseObj);
        try {
            $email = $this->userModel->validateEmail($requestData->email);
        } catch (\Exception $e) {
            $responseObj->errorResponse(["Unable to update Password", $e->getMessage()], 400);
        }
        $this->userModel->checkUniqueEmail($email);
        $rowsAffected = $this->userModel->updateEmail($this->userId, $email);
        // prep the return data
        $returnData = [];
        $returnData['rows_affected'] = $rowsAffected;
        $returnData['email'] = [$email];
        $responseObj->successResponse(["Password Updated"], 200, $returnData);
    }

    private function updateUser()
    {
        $responseObj = new Response();
        // Get the data payload
        $requestData = Utils::getJsonData($responseObj);
        $requestData->id = $this->userId;
        try {
            $result = $this->userModel->find($this->userId);
            if (!$result["rows_affected"]) {
                $responseObj->errorResponse(["User Record not found"], 404);
            }
            $userData = $this->userModel->validateUser($requestData, true);
        } catch (\Exception $e) {
            $responseObj->errorResponse(["Unable to Update User", $e->getMessage()], 422);
        }
        // Update the user record
        $rowsAffected = $this->userModel->replace($userData);
        // Prep the return data
        $returnData = [];
        $returnData['rows_affected'] = $rowsAffected;
        $returnData['users'] = [$userData];
        $responseObj->successResponse(["User Updated"], 201, $returnData);
    }

    private function deleteUser()
    {
        $responseObj = new Response();
        try {
            $id = $this->userModel->validateId($this->userId);
        } catch (UserException $e) {
            $responseObj->errorResponse(["Unable to Delete User", $e->getMessage()], 422);
        }
        try {
            $this->userModel->delete($id);
        } catch (UserException $e) {
            $responseObj->errorResponse(["Unable to Delete User: ", $e->getMessage()], 404);
        }
        $responseObj->successResponse(["User Deleted"], 201, []);
    }

}
