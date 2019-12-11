<?php
namespace Src\Controller;

use Src\Controller\Response;
use Src\System\UserContactException;
use Src\Model\UserContactModel;
use Src\System\Utils;

class UserContactController {

    private $db;
    private $requestMethod;
    private $uri;
    private $userId;
    private $contactId;

    private $userContactModel;

    public function __construct($db, $requestMethod, $uri, $userId, $contactId)
    {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->uri = $uri;
        $this->userId = $userId;
        $this->contactId = $contactId;

        $this->userContactModel = new UserContactModel($db);
    }

    public function processRequest()
    {
        switch ($this->requestMethod) {
            case 'GET':
                if ($this->userId) {
                    if ($this->contactId) {
                        $this->getUserContact();
                    } else {
                        $this->getUserContacts();
                    };
                }
                else {
                    // Don't allow listing all users (this could be allowed in the future if there were Admin Users).
                    $responseObj = new Response();
                    $responseObj->errorResponse(["Method Forbidden"], 403);
                };
                break;
            case 'POST':
                 $this->createUserContacts();
                break;
            case 'PUT':
                // Allowing Only one record at a time can be updated
                if ($this->contactId) {
                    $this->updateUserContact();
                } else {
                    $responseObj = new Response();
                    $responseObj->errorResponse(["Method Forbidden"], 403);
                };
                break;
            case 'DELETE':
                if ($this->userId) {
                    if ($this->contactId) {
                        $this->deleteUserContact();
                    } else {
                        $this->deleteUserContacts();
                    };
                }
                else {
                    // Don't allow deleting contact numbers for all users, not even Admins
                    $responseObj = new Response();
                    $responseObj->errorResponse(["Method Forbidden"], 403);
                };
                break;
            default:
                $responseObj = new Response();
                $responseObj->errorResponse(["Method Not Allowed"], 405);
        }
    }

    private function createUserContacts()
    {
        $responseObj = new Response();
        $requestData = Utils::getJsonData($responseObj);
        try {
            $userContactData = $this->userContactModel->validateContactNumbers($requestData);
        } catch (\Exception $e) {
            $responseObj->errorResponse(["Unable to create User Contact Number", $e->getMessage()], 400);
        }
        try {
            $this->db->beginTransaction();
            $rowsAffected = $this->userContactModel->insert($this->userId, $userContactData);
            $this->db->commit();
        } catch (\PDOException $e) {
            $this->db->rollBack();
            $responseObj->errorResponse(["Unable to Insert User Contact", $e->getMessage()], 500);
        } catch (UserContactException $e) {
            $this->db->rollBack();
            $responseObj->errorResponse([$e->getMessage()], 404);
        }
        // prep the return data
        $returnData = [];
        $returnData['rows_affected'] = $rowsAffected;
        $returnData['users'] = [$userContactData];
        $responseObj->successResponse(["User Contact Number Created"], 201, $returnData);
    }

    private function getUserContact()
    {
        $responseObj = new Response();
        $result = $this->userContactModel->findOne($this->contactId);
        if (!$result["rows_affected"]) {
            $responseObj->errorResponse(["User Contact Number Records not found"], 404);
        }
        $responseObj->successResponse(["Success"], 200, $result);
    }

    private function getUserContacts()
    {
        $responseObj = new Response();

        $result = $this->userContactModel->findAll($this->userId);
        if (!$result["rows_affected"]) {
            $responseObj->errorResponse(["User Contact Number Records not found"], 404);
        }
        $responseObj->successResponse(["Success"], 200, $result);
    }

    // TODO Ideally one would like to ensure that there is only one primary contact number per User. (this is getting a bit farr off the scope, I will attempt it if I have time available)
    private function updateUserContact()
    {
        $responseObj = new Response();
        $requestData = Utils::getJsonData($responseObj);
        try {
            $userContactData = $this->userContactModel->validateContactNumbers($requestData);
        } catch (\Exception $e) {
            $responseObj->errorResponse(["Unable to create User Contact Number", $e->getMessage()], 400);
        }
        try {
            $this->db->beginTransaction();
            // Delete the existing Contact Number
            $this->userContactModel->deleteOne($this->contactId);
            // Insert the updated Contact Number
            $rowsAffected = $this->userContactModel->insert($this->userId, $userContactData);
            $this->db->commit();
        } catch (\PDOException $e) {
            $this->db->rollBack();
            $responseObj->errorResponse(["Unable to Delete User Contact", $e->getMessage()], 500);
        } catch (UserContactException $e) {
            $this->db->rollBack();
            $responseObj->errorResponse([$e->getMessage()], 404);
        }
        // prep the return data
        $returnData = [];
        $returnData['rows_affected'] = $rowsAffected;
        $responseObj->successResponse(["User Contact Number Updated"], 201, $returnData);
    }

    private function deleteUserContact()
    {
        $responseObj = new Response();
        try {
            $result = $this->userContactModel->deleteOne($this->contactId);
        } catch (\PDOException $e) {
            $responseObj->errorResponse(["Unable to Delete User", $e->getMessage()], 500);
        } catch (UserContactException $e) {
            $responseObj->errorResponse([$e->getMessage()], 404);
        }
        $responseObj->successResponse(["Success"], 200, $result);
    }

    private function deleteUserContacts()
    {
        $responseObj = new Response();
        try {
            $result = $this->userContactModel->deleteAll($this->userId);
        } catch (\PDOException $e) {
            $responseObj->errorResponse(["Unable to Delete User", $e->getMessage()], 500);
        } catch (UserContactException $e) {
            $responseObj->errorResponse([$e->getMessage()], 404);
        }
        $responseObj->successResponse(["Success"], 200, $result);
    }
}
