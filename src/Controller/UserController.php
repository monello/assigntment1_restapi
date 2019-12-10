<?php
namespace Src\Controller;

use Src\Controller\Response;
use Src\Model\UserModel;

class UserController {

    private $db;
    private $requestMethod;
    private $userId;

    private $userModel;

    public function __construct($db, $requestMethod, $userId)
    {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->userId = $userId;

        $this->userModel = new UserModel($db);
    }

    // TODO Add some setters to help validate the class properties when setting them in the constructor

    public function processRequest()
    {
        switch ($this->requestMethod) {
            case 'GET':
                if ($this->userId) {
                    $this->getUser($this->userId); // MRL Done
                } else {
                    $this->getAllUsers(); // MRL Done
                };
                break;
            case 'POST':
                 $this->createUserFromRequest(); // MRL Done
                break;
            case 'PUT':
                $this->updateUserFromRequest($this->userId); // MRL Done
                break;
            // TODO Add a PATCH to update individual fields
            case 'DELETE':
                $this->deleteUser($this->userId); // MRL Done
                break;
            default:
                $responseObj = new Response();
                $responseObj->errorResponse(["Method not allowed"], 405);
        }
    }

    // MRL Done
    private function getAllUsers()
    {
        $result = $this->userModel->findAll();
        $responseObj = new Response();
        $responseObj->successResponse(["Success"], 200, $result);
    }

    // MRL Done
    private function getUser($id)
    {
        $responseObj = new Response();
        $result = $this->userModel->find($id);
        if (!$result) {
            $responseObj->errorResponse(["Record not found"], 404);
        }
        $responseObj->successResponse(["Success"], 200, $result);
    }

    // MRL Done
    private function createUserFromRequest()
    {
        $responseObj = new Response();
        $requestData = $this->getJsonData($responseObj);
        try {
            $userData = $this->userModel->validateUser($requestData);
        } catch (\Exception $e) {
            $responseObj->errorResponse(["Unable to create User", $e->getMessage()], 400);
        }
        $this->userModel->checkUniqueUsername($userData->username);
        $this->userModel->checkUniqueEmail($userData->email);
        $this->userModel->insert($userData);
        $responseObj->successResponse(["User Created"], 201, $userData);
    }

    // MRL Done
    private function updateUserFromRequest($id)
    {
        $responseObj = new Response();
        $result = $this->userModel->find($id);
        if (! $result) {
            $responseObj->errorResponse(["Resource not found"], 404);
        }
        $input = (array) json_decode(file_get_contents('php://input'), TRUE);
        if (! $this->validateUser($input)) { // TODO Spice up the validation
            $responseObj->errorResponse(["Invalid input"], 422); // TODO This is due to missing fields in the paylod, make this more specific
        }
        $this->userModel->update($id, $input);
        $responseObj->successResponse(["User Updated"], 201, []);
    }

    // MRL Done
    private function deleteUser($id)
    {
        $responseObj = new Response();
        $result = $this->userModel->find($id);
        if (! $result) {
            $responseObj->errorResponse(["Resource not found"], 404);
        }
        $this->userModel->delete($id);
        $responseObj->successResponse(["User Deleted"], 201, []);
    }

    // Utility Functions
    private function getJsonData($responseObj)
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
}
