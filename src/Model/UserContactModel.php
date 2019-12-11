<?php
namespace Src\Model;

use Src\Controller\Response;
use Src\System\UserContactException;

class UserContactModel {

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function insert($user_id, &$userContactsData)
    {
        $responseObj = new Response();
        $rowTotalCount = 0;
        try {
            $sql_number = 'INSERT INTO user_contact_number
                    (user_id, country_code, number, type, is_primary) 
                VALUES 
                    (:user_id, :country_code, :number, :type, :is_primary)
            ';
            foreach ($userContactsData as $contact_number) {
                try {
                    $query = $this->db->prepare($sql_number);
                    $query->bindParam(':user_id', $user_id, \PDO::PARAM_INT);
                    $query->bindParam(':country_code', $contact_number->country_code, \PDO::PARAM_STR);
                    $query->bindParam(':number', $contact_number->number, \PDO::PARAM_STR);
                    $query->bindParam(':type', $contact_number->type, \PDO::PARAM_INT);
                    $query->bindParam(':is_primary', $contact_number->is_primary, \PDO::PARAM_BOOL);
                    $query->execute();
                    // grab the contact new id
                    $rowCount = $query->rowCount();
                    if(!$rowCount) {
                        error_log("Unable to insert User Contact number");
                        throw new UserContactException("Unable to insert User Contact number");
                    }
                    $rowTotalCount += $rowCount;
                    $contact_number->id = $this->db->lastInsertId();
                } catch (\PDOException $e) {
                    error_log($e->getMessage());
                    throw new UserContactException("SQL Error. " . $e->getMessage());
                }
            }
        } catch (\PDOException $e) {
            error_log("Database Insert-User Query Error: " . $e->getMessage(), 0);
            $responseObj->errorResponse(["Database Query Error: ", "Insert statement invalid"], 500);
        } catch (UserContactException $e) {
            error_log("Database Insert-User Query Error: " . $e->getMessage(), 0);
            $responseObj->errorResponse(["Database Query Error: ", "Insert statement invalid"], 500);
        }
        if($rowTotalCount === 0) {
            $responseObj->errorResponse(["There was an error creating the user contact numbers - please try again"], 500);
        }
        return $rowTotalCount;
    }

    public function findOne($id)
    {
        $sql = '
            SELECT * FROM user_contact_number
            WHERE id = :id
        ';
        $query = $this->db->prepare($sql);
        $query->bindParam(':id', $id, \PDO::PARAM_INT);
        $query->execute();
        $result = $query->fetchAll(\PDO::FETCH_ASSOC);
        return [
            "rows_affected" => $query->rowCount(),
            "contact_numbers" => $result
        ];
    }

    public function findAll($user_id)
    {
        $sql = '
            SELECT * FROM user_contact_number
            WHERE user_id = :user_id
        ';
        $query = $this->db->prepare($sql);
        $query->bindParam(':user_id', $user_id, \PDO::PARAM_INT);
        $query->execute();
        $result = $query->fetchAll(\PDO::FETCH_ASSOC);
        return [
            "rows_affected" => $query->rowCount(),
            "contact_numbers" => $result
        ];
    }

    public function deleteOne($id)
    {
        $delete_numbers_sql = '
            DELETE FROM user_contact_number
            WHERE id = :id
        ';
        try {
            $query = $this->db->prepare($delete_numbers_sql);
            $query->bindParam(':id', $id, \PDO::PARAM_STR);
            $query->execute();
            // Get row count
            $rowCount = $query->rowCount();
            if($rowCount === 0) {
                error_log("User-Contact record not found");
                throw new UserContactException("User-Contact record not found");
            }
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            throw new UserContactException("SQL Error. " . $e->getMessage());
        }
        return [
            "rows_affected" => $rowCount
        ];
    }

    public function deleteAll($user_id)
    {
        $delete_numbers_sql = '
            DELETE FROM user_contact_number
            WHERE user_id = :user_id
        ';
        try {
            $query = $this->db->prepare($delete_numbers_sql);
            $query->bindParam(':user_id', $user_id, \PDO::PARAM_STR);
            $query->execute();
            // Get row count
            $rowCount = $query->rowCount();
            if($rowCount === 0) {
                error_log("User-Contact record not found");
                throw new UserContactException("User-Contact records not found");
            }
        } catch (\PDOException $e) {
            error_log($e->getMessage());
            throw new UserContactException("SQL Error. " . $e->getMessage());
        }
        return [
            "rows_affected" => $rowCount
        ];
    }

    // Validation Functions

    public function validateContactNumbers($contact_numbers)
    {
        if (!($contact_numbers ?? false) || !count($contact_numbers)) {
            throw new UserException("At least one Contact Number is required");
        }
        // loop through each provided number
        $cleanNumbers = [];
        $primaryNumberCount = 0;
        foreach ($contact_numbers as $contact_number) {
            $contact_number = $this->validateContactNumber($contact_number);
            if ($contact_number->is_primary) {
                $primaryNumberCount++;
            }
            $cleanNumbers[] = $contact_number;
        }
        if (!$primaryNumberCount) {
            throw new UserContactException("One Contact Number must be set to the Primary number");
        } elseif ($primaryNumberCount > 1) {
            throw new UserContactException("Only one Contact Number must be set to the Primary number");
        } else {
            return $cleanNumbers;
        }
    }
    public function validateContactNumber($contact_number)
    {
        // Country Code
        // ------------
        if (!($contact_number->country_code ?? false)) {
            throw new UserContactException("Country Code is required for all Contact Numbers");
        }
        $contact_number->country_code = trim($contact_number->country_code);
        // Remove Spaces (not going to moan about spaces)
        $contact_number->country_code = preg_replace('/\s/', '', $contact_number->country_code);
        // Check for Invalid Characters
        $hasBadChars = preg_match('/[^0-9\+]/', $contact_number->country_code);
        $codeLength = strlen($contact_number->country_code);
        if ($hasBadChars || $codeLength < 3 || $codeLength > 10) {
            throw new UserException("Contact Number, Country Code is invalid");
        }

        // Number
        // ------------
        if (!($contact_number->number ?? false)) {
            throw new UserContactException("Number is required for all Contact Numbers");
        }
        $contact_number->number = trim($contact_number->number);
        // Reduce Spaces (not going to moan about spaces)
        $contact_number->number = preg_replace('/\s+/', ' ', $contact_number->number);
        // Check for Invalid Characters
        $hasBadChars = preg_match('/[^0-9\s\(\)\-]/', $contact_number->number);
        $codeLength = strlen($contact_number->number);
        if ($hasBadChars || $codeLength < 5 || $codeLength > 20) {
            throw new UserContactException("Contact Number, Number is invalid");
        }

        // Number Type
        // ------------
        if (!($contact_number->type ?? false) && $contact_number->type !== 0) {
            throw new UserContactException("Number-Type is required for all Contact Numbers");
        }
        if (!filter_var($contact_number->type, FILTER_VALIDATE_INT, ["options" => ["min_range"=>1,"max_range"=>3]])) {
            throw new UserContactException("Contact Number, Number-Type is invalid");
        }

        // Is Primary Flag
        // ---------------
        if (!isset($contact_number->is_primary)) {
            throw new UserContactException("Is Primary option for Contact number may not be blank");
        }
        if ($contact_number->is_primary !== true && $contact_number->is_primary !== false) {
            throw new UserContactException("Is Primary option for Contact number is invalid (type)");
        }

        return $contact_number;
    }

}
