<?php
namespace Src\Controller;

use Src\Controller\Response;
use Src\Model\ContactException;
//use Src\Model\UserModel;

class ContactController {

    private $db;
    private $requestMethod;
    private $contactId;
    private $resultsPerPage;

    private $contactModel;

    public function __construct($db, $requestMethod, $contactId)
    {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->contactId = $contactId;
        $this->resultsPerPage = getenv('RESULTS_PER_PAGE');

//        $this->userModel = new UserModel($db);
    }

//    // TODO Add some setters to help validate the class properties when setting them in the constructor

    public function processRequest()
    {
        echo "ROUTE CONTACT\n";
        echo "METHOD: $this->requestMethod\n";
        if ($this->contactId) {
            echo "CONTACT ID: $this->contactId\n";
        }
        switch ($this->requestMethod) {
            case 'GET':
                if ($this->contactId) {
                    $this->getContact($this->contactId);
                }
                else {
                    $this->getAllContacts();
                }
                break;
//            case 'POST':
//                 $this->createUserFromRequest();
//                break;
//            case 'PUT':
//                $this->updateUserFromRequest($this->userId);
//                break;
//            case 'PATCH':
//                $this->patchUserFromRequest($this->userId);
//                break;
//            case 'DELETE':
//                $this->deleteUser($this->userId);
//                break;
            default:
                $responseObj = new Response();
                $responseObj->errorResponse(["Method Not Allowed"], 405);
        }
    }

    private function getAllContacts()
    {
        $page = 0;
        if (array_key_exists("page", $_GET)) {
            $page = $_GET["page"];
        }
        echo "PAGE: $page\n";
        echo "RESULTS PER PAGE: " . $this->resultsPerPage . "\n";
        $offset = 0;
        if ($page > 0) {
            $offset = $page * $this->resultsPerPage;
        }
        echo "OFFSET: $offset\n";

//        $result = $this->userModel->findAll();
//        $responseObj = new Response();
//        $responseObj->successResponse(["Success"], 200, $result);
    }

    private function getContact($id)
    {
//        $responseObj = new Response();
//        $result = $this->userModel->find($id);
//        if (!$result) {
//            $responseObj->errorResponse(["Record not found"], 404);
//        }
//        $responseObj->successResponse(["Success"], 200, $result);
    }

//    private function createUserFromRequest()
//    {
//        $responseObj = new Response();
//        $requestData = $this->getJsonData($responseObj);
//        try {
//            $userData = $this->userModel->validateUser($requestData, false);
//        } catch (\Exception $e) {
//            $responseObj->errorResponse(["Unable to create User", $e->getMessage()], 400);
//        }
//        $this->userModel->checkUniqueUsername($userData->username);
//        $this->userModel->checkUniqueEmail($userData->email);
//        $rowsAffected = $this->userModel->insert($userData);
//        // prep the return data
//        $returnData = [];
//        $returnData['rows_affected'] = $rowsAffected;
//        $returnData['users'] = [$userData];
//        $responseObj->successResponse(["User Created"], 201, $returnData);
//    }
//
//    private function updateUserFromRequest($id)
//    {
//        $responseObj = new Response();
//        // Get the data payload
//        $requestData = $this->getJsonData($responseObj);
//        $requestData->id = $id;
//        try {
//            $userData = $this->userModel->validateUser($requestData, true);
//        } catch (\Exception $e) {
//            $responseObj->errorResponse(["Unable to Update User", $e->getMessage()], 422);
//        }
//        // Update the user record
//        $rowsAffected = $this->userModel->replace($userData);
//        // Prep the return data
//        $returnData = [];
//        $returnData['rows_affected'] = $rowsAffected;
//        $returnData['users'] = [$userData];
//        $responseObj->successResponse(["User Created"], 201, $returnData);
//    }
//
//    private function patchUserFromRequest($id) {
//        // grab the data from the payload
//        // check which fields are being updated
//        // validate those fields individually
//
//        // each telephone number that has an id will be directly replaced
//        // each telephone number that does not have an id will be added
//        // ecch telephone number
//
//    }
//
//    private function deleteUser($id)
//    {
//        $responseObj = new Response();
//        try {
//            $id = $this->userModel->validateId($id);
//        } catch (UserException $e) {
//            $responseObj->errorResponse(["Unable to Delete User", $e->getMessage()], 422);
//        }
//        try {
//            $this->userModel->delete($id);
//        } catch (UserException $e) {
//            $responseObj->errorResponse(["Unable to Delete User: ", $e->getMessage()], 404);
//        }
//        $responseObj->successResponse(["User Deleted"], 201, []);
//    }
//
//    // Utility Functions
//    private function getJsonData($responseObj)
//    {
//        // Check tha the request's Content-Type header is JSON
//        if($_SERVER['CONTENT_TYPE'] !== 'application/json') {
//            $responseObj->errorResponse(["Content Type header not set to JSON"], 400);
//        }
//        // Check that the posted content is in JSON Format
//        $rawPostData = file_get_contents('php://input');
//        $data = json_decode($rawPostData);
//        if (!$data) {
//            $responseObj->errorResponse(["Request body is not valid JSON"], 400);
//        }
//        return $data;
//    }
}
