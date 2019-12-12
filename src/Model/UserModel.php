<?php
namespace Src\Model;

use Src\Controller\Response;
use Src\System\UserContactException;
use Src\System\UserException;

class UserModel
{
    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
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
            // Get the contact numbers
            $userContactModel = new UserContactModel($this->db);
            $userContacts = $userContactModel->findAll($id);
            $result['contact_numbers'] = $userContacts['contact_numbers'];
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
        } catch (\PDOException $e) {
            error_log("Database Insert-User Query Error: " . $e->getMessage(), 0);
            $responseObj->errorResponse(["Database Query Error: ", "Insert statement invalid"], 500);
        } catch (UserContactException $e) {
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

    public function replace(&$userData)
    {
        $responseObj = new Response();
        // Format Date of Birth
        $date_of_birth  = $userData->date_of_birth->format('Y-m-d');

        try {
            // Prep the SQL
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
            $query->bindParam(':id', $userData->id, \PDO::PARAM_INT);
            $query->bindParam(':first_name', $userData->first_name, \PDO::PARAM_STR);
            $query->bindParam(':last_name', $userData->last_name, \PDO::PARAM_STR);
            $query->bindParam(':date_of_birth', $date_of_birth, \PDO::PARAM_STR);
            $query->bindParam(':gender', $userData->gender, \PDO::PARAM_STR);
            $query->bindParam(':country_id', $userData->country_id, \PDO::PARAM_INT);
            $query->execute();
        } catch (\PDOException $e) {
            error_log("Database Error: " . $e->getMessage(), 0);
            $responseObj = new Response();
            $responseObj->errorResponse(["Unable to Update User: ", "Database Error"], 500);
        } catch (UserContactException $e) {
            error_log("Database Insert-User Query Error: " . $e->getMessage(), 0);
            $responseObj->errorResponse(["Unable to Update User: ", "Insert statement invalid"], 500);
        }
        // Get row count
        $rowCount = $query->rowCount();
        if($rowCount === 0) {
            $responseObj->errorResponse(["Request Completed Successfully: ", "Data was unchanged"], 200);
        }
        return $rowCount;
    }

    public function delete($id)
    {
        // Dekete the user. (The ON CASCADE DELETE foreign key will take care of the contact numbers)
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

    public function updateUsername($id, $username)
    {
        $responseObj = new Response();
        try {
            // Prep the SQL
            $sql = "UPDATE user SET username = :username WHERE id = :id";
            $query = $this->db->prepare($sql);
            $query->bindParam(':id', $id, \PDO::PARAM_INT);
            $query->bindParam(':username', $username, \PDO::PARAM_STR);
            $query->execute();
        } catch (\PDOException $e) {
            error_log("Database Error: " . $e->getMessage(), 0);
            $responseObj = new Response();
            $responseObj->errorResponse(["Unable to Update Username: ", "Database Error"], 500);
        } catch (UserContactException $e) {
            error_log("Database Insert-User Query Error: " . $e->getMessage(), 0);
            $responseObj->errorResponse(["Unable to Update Username: ", "Insert statement invalid"], 500);
        }
        // Get row count
        $rowCount = $query->rowCount();
        if($rowCount === 0) {
            error_log("Unable to Update Username:  User record not found", 0);
            $responseObj->errorResponse(["Unable to Update Username: ", "User record not found"], 404);
        }
        return $rowCount;
    }

    public function updatePassword($id, $hashed_password)
    {
        $responseObj = new Response();
        try {
            // Prep the SQL
            $sql = "UPDATE user SET password = :password WHERE id = :id";
            $query = $this->db->prepare($sql);
            $query->bindParam(':id', $id, \PDO::PARAM_INT);
            $query->bindParam(':password', $hashed_password, \PDO::PARAM_STR);
            $query->execute();
        } catch (\PDOException $e) {
            error_log("Database Error: " . $e->getMessage(), 0);
            $responseObj = new Response();
            $responseObj->errorResponse(["Unable to Update Password: ", "Database Error"], 500);
        } catch (UserContactException $e) {
            error_log("Database Insert-User Query Error: " . $e->getMessage(), 0);
            $responseObj->errorResponse(["Unable to Update Password: ", "Insert statement invalid"], 500);
        }
        // Get row count
        $rowCount = $query->rowCount();
        if($rowCount === 0) {
            error_log("Unable to Update Password:  User record not found", 0);
            $responseObj->errorResponse(["Unable to Update Password: ", "User record not found"], 404);
        }
        return $rowCount;
    }

    public function updateEmail($id, $email)
    {
        $responseObj = new Response();
        try {
            // Prep the SQL
            $sql = "UPDATE user SET email = :email WHERE id = :id";
            $query = $this->db->prepare($sql);
            $query->bindParam(':id', $id, \PDO::PARAM_INT);
            $query->bindParam(':email', $email, \PDO::PARAM_STR);
            $query->execute();
        } catch (\PDOException $e) {
            error_log("Database Error: " . $e->getMessage(), 0);
            $responseObj = new Response();
            $responseObj->errorResponse(["Unable to Update Email: ", "Database Error"], 500);
        } catch (UserContactException $e) {
            error_log("Database Insert-User Query Error: " . $e->getMessage(), 0);
            $responseObj->errorResponse(["Unable to Update Email: ", "Insert statement invalid"], 500);
        }
        // Get row count
        $rowCount = $query->rowCount();
        if($rowCount === 0) {
            error_log("Unable to Update Email:  User record not found", 0);
            $responseObj->errorResponse(["Unable to Update Email: ", "User record not found"], 404);
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
        if (preg_match('/[\s\t]/', $username)) {
            throw new UserException("Username should not contain spaces.");
        }
        if (preg_match('/[^\w\.]/', $username)) {
            throw new UserException("Username should contain only the following characters: a - z, A - Z, 0 - 9, _ or .");
        }
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
        // check that the user is between 6 and 120 years old
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
}
