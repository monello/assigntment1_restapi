<?php
namespace Src\Controller;

use Src\Model\listModel;

class ListController
{
    private $db;
    private $requestMethod;
    private $uri;
    private $list;
    private $listModel;

    public function __construct($db, $requestMethod, $uri, $list)
    {
        $this->db = $db;
        $this->requestMethod = $requestMethod;
        $this->uri = $uri;
        $this->list = $list;
        $this->listModel = new listModel($db);
    }

    public function processRequest()
    {
        switch ($this->requestMethod) {
            case 'GET':
                $this->getList();
                break;
            default:
                $responseObj = new Response();
                $responseObj->errorResponse(["Method Not Allowed"], 405);
        }
    }

    private function getList()
    {
        $responseObj = new Response();
        $list = $this->whiteList();
        $result = $this->listModel->fetch($list, $this->list);
        if (!$result["rows_affected"]) {
            $responseObj->errorResponse(["No data in this list table"], 404);
        }
        $responseObj->successResponse(["Success"], 200, $result);
    }

    private function whiteList()
    {
        $listTables = [
            'countries' => 'lst_countries',
            'phonetypes' => 'lst_contact_number_types'
        ];
        if (!($listTables[$this->list] ?? false)) {
            $responseObj = new Response();
            $responseObj->errorResponse(["Invalid list table"], 404);
        }

        return $listTables[$this->list];
    }
}