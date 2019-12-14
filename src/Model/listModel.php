<?php


namespace Src\Model;


use Src\Controller\Response;

class listModel {

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function fetch($list)
    {
        $sql = "SELECT * FROM $list";
        try {
            $query = $this->db->prepare($sql);
            $query->execute();
            $result = $query->fetchAll(\PDO::FETCH_ASSOC);
            // Get row count
            $rowCount = $query->rowCount();
            // prep the return data
            $returnData = [];
            $returnData['rows_affected'] = $rowCount;
            $returnData['countries'] = $result;
            return $returnData;
        } catch (\PDOException $e) {
            error_log("Database Error: " . $e->getMessage(), 0);
            $responseObj = new Response();
            $responseObj->errorResponse([$e->getMessage()], 500);
        }
    }
}