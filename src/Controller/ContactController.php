<?php
namespace Src\Controller;

use Src\Controller\Response;
use Src\System\ContactException;
use Src\Model\ContactModel;

class ContactController {

    private $db;
    private $requestMethod;
    private $uri;
    private $contactId;
    private $resultsPerPage;

    private $contactModel;

    public function __construct($db, $requestMethod, $uri, $contactId)
    {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->uri = $uri;
        $this->contactId = $contactId;
        $this->resultsPerPage = getenv('RESULTS_PER_PAGE');

        $this->contactModel = new ContactModel($db);
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

}
