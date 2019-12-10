<?php
namespace Src\Model;

use Src\Controller\Response;

class UserModel {

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // TODO Dissallow this action on the UserController (fetching all users, will nevere be a scenario), but keep this code until the ContactsController is done as an example
    public function findAll()
    {
        $sql = "
            SELECT 
                id, username, email, first_name, last_name, date_of_birth, gender, country_id, is_active, login_attempts
            FROM
                user;
        ";
        try {
            $query = $this->db->query($sql);
            $result = $query->fetchAll(\PDO::FETCH_ASSOC);
            // Get row count
            $rowCount = $query->rowCount();
            // prep the return data
            $returnData = [];
            $returnData['rows_affected'] = $rowCount;
            $userData = $this->getAllUserPhoneNumbers($result);
            $returnData['users'] = $userData;
            return $returnData;
        } catch (\PDOException $e) {
            error_log("Database Error: " . $e->getMessage(), 0);
            $responseObj = new Response();
            $responseObj->errorResponse([$e->getMessage()], 500);
        }
    }

    public function find($id)
    {
        $sql = "
            SELECT 
                id, username, email, first_name, last_name, date_of_birth, gender, country_id, is_active, login_attempts
            FROM
                user
            WHERE id = :id;
        ";
        try {
            $query = $this->db->prepare($sql);
            $query->bindParam(':id', $id, \PDO::PARAM_INT);
            $query->execute();
            $result = $query->fetchAll(\PDO::FETCH_ASSOC);
            // Get row count
            $rowCount = $query->rowCount();
            // prep the return data
            $returnData = [];
            $returnData['rows_affected'] = $rowCount;
            // Get the phone numbers
            $this->getUserPhoneNumbers($id, $result[0]);
            $returnData['users'] = $result;
            return $returnData;
        } catch (\PDOException $e) {
            error_log("Database Error: " . $e->getMessage(), 0);
            $responseObj = new Response();
            $responseObj->errorResponse([$e->getMessage()], 500);
        }
    }

    public function insert(&$userData)
    {
        $responseObj = new Response();
        // Format Date of Birth
        $date_of_birth  = $userData->date_of_birth->format('Y-m-d');

        try {
            $this->db->beginTransaction();

            // Insert User record
            $sql = 'INSERT INTO user 
                    (username, email, password, first_name, last_name, date_of_birth, gender, country_id) 
                VALUES 
                    (:username, :email, :password, :first_name, :last_name, :date_of_birth, :gender, :country_id)
            ';
            $query = $this->db->prepare($sql);
            $query->bindParam(':username', $userData->username, \PDO::PARAM_STR);
            $query->bindParam(':email', $userData->email, \PDO::PARAM_STR);
            $query->bindParam(':password', $userData->hashed_password, \PDO::PARAM_STR);
            $query->bindParam(':first_name', $userData->first_name, \PDO::PARAM_STR);
            $query->bindParam(':last_name', $userData->last_name, \PDO::PARAM_STR);
            $query->bindParam(':date_of_birth', $date_of_birth, \PDO::PARAM_STR);
            $query->bindParam(':gender', $userData->gender, \PDO::PARAM_STR);
            $query->bindParam(':country_id', $userData->country_id, \PDO::PARAM_INT);
            $query->execute();

            $newId = $this->db->lastInsertId();

            // Insert Telephone numbers
            $sql_number = 'INSERT INTO user_contact_number
                    (user_id, country_code, number, type, is_primary) 
                VALUES 
                    (:user_id, :country_code, :number, :type, :is_primary)
            ';
            foreach ($userData->phone_numbers as $phone_number) {
                $query = $this->db->prepare($sql_number);
                $query->bindParam(':user_id', $newId, \PDO::PARAM_INT);
                $query->bindParam(':country_code', $phone_number->country_code, \PDO::PARAM_STR);
                $query->bindParam(':number', $phone_number->number, \PDO::PARAM_STR);
                $query->bindParam(':type', $phone_number->type, \PDO::PARAM_INT);
                $query->bindParam(':is_primary', $phone_number->is_primary, \PDO::PARAM_BOOL);
                $query->execute();

                // grab the phone new id
                $phone_number->id = $this->db->lastInsertId();
            }

            // Commit the trancsation
            $this->db->commit();
        } catch (\PDOException $e) {
            // roll back update/insert if error
            $this->db->rollBack();
            error_log("Database Insert-User Query Error: " . $e->getMessage(), 0);
            $responseObj->errorResponse(["Database Query Error: ", "Insert statement invalid"], 500);
        }
        // Get row count
        $rowCount = $query->rowCount();
        if($rowCount === 0) {
            $responseObj->errorResponse(["There was an error creating the user account - please try again"], 500);
        }
        // Get last user id so we can return the user id in the json
        $userData->id = $newId;
        unset($userData->hashed_password);
        return $rowCount;
    }

    // TODO username, email, password and phone numbers done separately
    public function replace(&$userData)
    {
        $responseObj = new Response();
        // Format Date of Birth
        $date_of_birth  = $userData->date_of_birth->format('Y-m-d');

        try {
            $this->db->beginTransaction();
            // prep the SQL
            $sql = "
                UPDATE user
                SET 
                    first_name = :first_name, 
                    last_name = :last_name, 
                    date_of_birth = :date_of_birth, 
                    gender = :gender, 
                    country_id = :country_id
                WHERE id = :id;
            ";
            $query = $this->db->prepare($sql);
            $query->bindParam(':id', $userData->id, \PDO::PARAM_STR);
            $query->bindParam(':first_name', $userData->first_name, \PDO::PARAM_STR);
            $query->bindParam(':last_name', $userData->last_name, \PDO::PARAM_STR);
            $query->bindParam(':date_of_birth', $date_of_birth, \PDO::PARAM_STR);
            $query->bindParam(':gender', $userData->gender, \PDO::PARAM_STR);
            $query->bindParam(':country_id', $userData->country_id, \PDO::PARAM_INT);
            $query->execute();

            // Delete all the existing numbers
            $delete_numbers_sql = '
                DELETE FROM user_contact_number
                WHERE user_id = :user_id
            ';
            $query = $this->db->prepare($delete_numbers_sql);
            $query->bindParam(':user_id', $userData->id, \PDO::PARAM_STR);
            $query->execute();

            // Insert the ne Telephone numbers
            $sql_number = 'INSERT INTO user_contact_number
                    (user_id, country_code, number, type, is_primary) 
                VALUES 
                    (:user_id, :country_code, :number, :type, :is_primary)
            ';
            foreach ($userData->phone_numbers as $phone_number) {
                $query = $this->db->prepare($sql_number);
                $query->bindParam(':user_id', $userData->id, \PDO::PARAM_INT);
                $query->bindParam(':country_code', $phone_number->country_code, \PDO::PARAM_STR);
                $query->bindParam(':number', $phone_number->number, \PDO::PARAM_STR);
                $query->bindParam(':type', $phone_number->type, \PDO::PARAM_INT);
                $query->bindParam(':is_primary', $phone_number->is_primary, \PDO::PARAM_BOOL);
                $query->execute();

                // grab the phone new id
                $phone_number->id = $this->db->lastInsertId();
            }

            // Commit the trancsation
            $this->db->commit();
        } catch (\PDOException $e) {
            // roll back update/insert if error
            $this->db->rollBack();
            error_log("Database Error: " . $e->getMessage(), 0);
            $responseObj = new Response();
            $responseObj->errorResponse([$e->getMessage()], 500);
        }
        // Get row count
        $rowCount = $query->rowCount();
        if($rowCount === 0) {
            $responseObj->errorResponse(["Unable to Update User: ", "User record not found"], 404);
        }
        return $rowCount;
    }

    public function delete($id)
    {
        $sql = "DELETE FROM user WHERE id = :id;";
        try {
            $query = $this->db->prepare($sql);
            $query->bindParam(':id', $id, \PDO::PARAM_INT);
            $query->execute();
        } catch (\PDOException $e) {
            error_log("Database Error: " . $e->getMessage(), 0);
            $responseObj = new Response();
            $responseObj->errorResponse([$e->getMessage()], 500);
        }
        // Get row count
        $rowCount = $query->rowCount();
        if($rowCount === 0) {
            throw new UserException("User record not found");
        }
        return $rowCount;
    }

    // Validation Functions

    // This function validates all the input data required to create a new user
    public function validateUser($inputData, $isUpdate=true)
    {
        $cleanData = (object) [];
        if ($isUpdate) {
            $cleanData->id = $this->validateId($inputData->id ?? null);
        } else {
            $cleanData->username = $this->validateUsername($inputData->username ?? null);
            $cleanData->email = $this->validateEmail($inputData->email ?? null);
            $cleanData->hashed_password = $this->hashPassword($inputData->password ?? null);
        }
        $cleanData->first_name = $this->validateName($inputData->first_name ?? null);
        $cleanData->last_name =  $this->validateName($inputData->last_name ?? null);
        $cleanData->date_of_birth = $this->validateDateOfBirth($inputData->date_of_birth ?? null);
        $cleanData->gender = $this->validateGender($inputData->gender ?? null);
        $cleanData->country_id = $this->validateCountryId($inputData->country_id ?? null);
        $cleanData->phone_numbers = $this->validatePhoneNumbers($inputData->phone_numbers ?? null);
        return $cleanData;
    }

    // The rest of the validation functions validate specific fields/properties
    public function validateId($id)
    {
        if (!($id ?? false) && $id !== 0) {
            throw new UserException("User Id is required for an existing user");
        }
        // Must be an integer > 0
        if (!filter_var($id, FILTER_VALIDATE_INT, ["options" => ["min_range"=>1]])) {
            throw new UserException("User Id is invalid");
        }
        return (int) $id;
    }
    public function validateUsername($username)
    {
        if (!($username ?? false)) {
            throw new UserException("Username is required");
        }
        $username = filter_var($username, FILTER_SANITIZE_STRING);
        $username = trim($username);
        if (strlen($username) < 4 || strlen($username) > 100) {
            throw new UserException("Username is not a valid length");
        }
        return $username;
    }
    public function validateEmail($email)
    {
        if (!($email ?? false)) {
            throw new UserException("Email is required");
        }
        $email = trim($email);
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new UserException("Not a valid email");
        }
        return $email;
    }
    public function validatePassword($password)
    {
        if (!($password ?? false)) {
            throw new UserException("Password is required");
        }
        // Don't trim the password, rather inform the user of white-space
        if (preg_match('/[\s\t]/', $password)) {
            throw new UserException("Password should not contain spaces.");
        }
        // Validate password strength
        $uppercase = preg_match('/[A-Z]/', $password);
        $lowercase = preg_match('/[a-z]/', $password);
        $number = preg_match('/[0-9]/', $password);
        $specialChars = preg_match('/[^\w]/', $password);
        if(!$uppercase || !$lowercase || !$number || !$specialChars || strlen($password) < 6) {
            throw new UserException("Password should be at least 6 characters in length and should include at least one upper case letter, one number, and one special character.");
        }
        return $password;
    }
    public function validateName($name)
    {
        if (!($name ?? false)) {
            throw new UserException("First name and Last Name are both required");
        }
        $name = trim($name);
        // Reduce repeating spaces
        $name = preg_replace('/\s{2,}/', ' ', $name);
        $hasNumbers = preg_match('/[0-9]/', $name);
        $nameLength = strlen($name);
        if ($hasNumbers || $nameLength < 2 || $nameLength > 100) {
            throw new UserException("First name or Last Name is invalid");
        }
        return $name;
    }
    public function validateDateOfBirth($date_of_birth)
    {
        if (!($date_of_birth ?? false)) {
            throw new UserException("Date of Birth is required");
        }

        date_default_timezone_set('Africa/Johannesburg');

        // check that is in a valid date
        $date = explode('-', $date_of_birth);
        $year = $date[0];
        $month = $date[1];
        $day = $date[2];
        if (!checkdate($month, $day, $year)) {
            throw new UserException("Not a valid date");
        }

        $date_of_birth = new \DateTime($date_of_birth);
        $now = new \DateTime();
        $diff = $now->diff($date_of_birth);
        // check that it is not in the future
        // chech that the user is between 6 and 120 years old
        if ($date_of_birth > $now || $diff->y < 6 || $diff->y > 120) {
            throw new UserException("Date of birth is not in a valid date range");
        }

        return $date_of_birth;
    }
    public function validateGender($gender)
    {
        // Gender id optional, but if it is provided it has to be specific
        if ($gender ?? false) {
            $re = '/^(Female|Male|Other)$/mi';
            if (!preg_match_all($re, $gender, $matches, PREG_SET_ORDER, 0)) {
                throw new UserException("Invalid Gender value provided");
            }
        }
        return $gender;
    }
    public function validateCountryId($country_id)
    {
        if (!($country_id ?? false)) {
            throw new UserException("Country is required");
        }
        // Must be an integer > 0
        if (!filter_var($country_id, FILTER_VALIDATE_INT, array("options" => array("min_range"=>1)))) {
            throw new UserException("Country provided is invalid");
        }
        return $country_id;
    }
    public function validatePhoneNumbers($phone_numbers)
    {
        if (!($phone_numbers ?? false) || !count($phone_numbers)) {
            throw new UserException("At least one Phone Number is required");
        }
        // loop throug each provided number
        $cleanNumbers = [];
        $primaryNumberCount = 0;
        foreach ($phone_numbers as $phone_number) {
            $phone_number = $this->validatePhoneNumber($phone_number);
            if ($phone_number->is_primary) {
                $primaryNumberCount++;
            }
            $cleanNumbers[] = $phone_number;
        }
        if (!$primaryNumberCount) {
            throw new UserException("One Phone Number must be set to the Primary number");
        } elseif ($primaryNumberCount > 1) {
            throw new UserException("Only one Phone Number must be set to the Primary number");
        } else {
            return $cleanNumbers;
        }
    }
    public function validatePhoneNumber($phone_number)
    {
        // Country Code
        // ------------
        if (!($phone_number->country_code ?? false)) {
            throw new UserException("Country Code is required for all Phone Numbers");
        }
        $phone_number->country_code = trim($phone_number->country_code);
        // Remove Spaces (not going to moan about spaces)
        $phone_number->country_code = preg_replace('/\s/', '', $phone_number->country_code);
        // Check for Invalid Characters
        $hasBadChars = preg_match('/[^0-9\+]/', $phone_number->country_code);
        $codeLength = strlen($phone_number->country_code);
        if ($hasBadChars || $codeLength < 3 || $codeLength > 10) {
            throw new UserException("Phone Number, Country Code is invalid");
        }

        // Number
        // ------------
        if (!($phone_number->number ?? false)) {
            throw new UserException("Number is required for all Phone Numbers");
        }
        $phone_number->number = trim($phone_number->number);
        // Reduce Spaces (not going to moan about spaces)
        $phone_number->number = preg_replace('/\s+/', ' ', $phone_number->number);
        // Check for Invalid Characters
        $hasBadChars = preg_match('/[^0-9\s\(\)\-]/', $phone_number->number);
        $codeLength = strlen($phone_number->number);
        if ($hasBadChars || $codeLength < 5 || $codeLength > 20) {
            throw new UserException("Phone Number, Number is invalid");
        }

        // Number Type
        // ------------
        if (!($phone_number->type ?? false) && $phone_number->type !== 0) {
            throw new UserException("Number-Type is required for all Phone Numbers");
        }
        if (!filter_var($phone_number->type, FILTER_VALIDATE_INT, ["options" => ["min_range"=>1,"max_range"=>3]])) {
            throw new UserException("Phone Number, Number-Type is invalid");
        }

        // Is Primary Flag
        // ---------------
        if (!isset($phone_number->is_primary)) {
            throw new UserException("Is Primary option for Phone number may not be blank");
        }
        if ($phone_number->is_primary !== true && $phone_number->is_primary !== false) {
            throw new UserException("Is Primary option for Phone number is invalid (type)");
        }

        return $phone_number;

    }

    // Utility Functions

    public function hashPassword($password)
    {
        $password = self::validatePassword($password);
        return password_hash($password, PASSWORD_DEFAULT);
    }
    public function checkUniqueUsername($username)
    {
        $responseObj = new Response();
        try {
            // Check if the username already exists
            $query = $this->db->prepare('SELECT id from user where username = :username');
            $query->bindParam(':username', $username, \PDO::PARAM_STR);
            $query->execute();
            // Get the row-count
            $rowCount = $query->rowCount();
            if($rowCount !== 0) {
                $responseObj->errorResponse(["Username already exists"], 409);
            }
        } catch (\PDOException $exception) {
            $message = $exception->getMessage();
            error_log("Database Insert-User Query Error: " . $message, 0);
            $responseObj->errorResponse(["There was an issue creating a user account"], 500);
        }
    }
    public function checkUniqueEmail($email)
    {
        $responseObj = new Response();
        try {
            // Check if the username already exists
            $query = $this->db->prepare('SELECT id from user where email = :email');
            $query->bindParam(':email', $email, \PDO::PARAM_STR);
            $query->execute();
            // Get the row-count
            $rowCount = $query->rowCount();
            if($rowCount !== 0) {
                $responseObj->errorResponse(["Email Address already exists"], 409);
            }
        } catch (PDOException $exception) {
            $responseObj->errorResponse(["There was an issue creating a user account"], 500);
        }
    }
    // TODO - This scenation should not be needed in this app
    public function getAllUserPhoneNumbers($userData)
    {
        $returnData = [];
        foreach ($userData as $user) {
            $this->getUserPhoneNumbers($user["id"], $user);
            $returnData[] = $user;
        }
        return $returnData;
    }
    public function getUserPhoneNumbers($id, &$userData)
    {
        $sql = '
            SELECT * FROM user_contact_number
            WHERE user_id = :id
        ';
        try {
            $query = $this->db->prepare($sql);
            $query->bindParam(':id', $id, \PDO::PARAM_INT);
            $query->execute();
            $result = $query->fetchAll(\PDO::FETCH_ASSOC);
            // Get row count
            $rowCount = $query->rowCount();
            if ($rowCount > 0) {
                $userData["phone_numbers"] = $result;
            } else {
                $userData["phone_numbers"] = [];
            }
        } catch (\PDOException $e) {
            error_log("Database Error: " . $e->getMessage(), 0);
            $responseObj = new Response();
            $responseObj->errorResponse([$e->getMessage()], 500);
        }
    }
}
