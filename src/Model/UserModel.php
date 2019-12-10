<?php
namespace Src\Model;

use Src\Controller\Response;

class UserModel {

    private $db = null;

    public function __construct($db)
    {
        $this->db = $db;
    }

    // MRL - Done
    public function findAll()
    {
        $sql = "
            SELECT 
                id, username, email, first_name, last_name, date_of_birth, gender, country_id, is_active, login_attempts
            FROM
                user;
        ";
        try {
            $statement = $this->db->query($sql);
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            return $result;
        } catch (\PDOException $e) {
            error_log("Database Error: " . $e->getMessage(), 0);
            $responseObj = new Response();
            $responseObj->errorResponse([$e->getMessage()], 500);
        }
    }

    // MRL - Done
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
            return $result;
        } catch (\PDOException $e) {
            error_log("Database Error: " . $e->getMessage(), 0);
            $responseObj = new Response();
            $responseObj->errorResponse([$e->getMessage()], 500);
        }
    }

    // MRL - Done
    public function insert(&$userData)
    {
        $responseObj = new Response();

        // Format Date of Birth
        $date_of_birth  = $userData->date_of_birth->format('Y-m-d');

        $sql = 'INSERT INTO user 
                (username, email, password, first_name, last_name, date_of_birth, gender, country_id) 
            VALUES 
                (:username, :email, :password, :first_name, :last_name, :date_of_birth, :gender, :country_id)
        ';
        try {
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
        } catch (\PDOException $e) {
            error_log("Database Insert-User Query Error: " . $e->getMessage(), 0);
            $responseObj->errorResponse(["Database Query Error: ", "Insert statement invalid"], 500);
        }

        // get row count
        $rowCount = $query->rowCount();

        if($rowCount === 0) {
            $responseObj->errorResponse(["There was an error creating the user account - please try again"], 500);
        }

        // Get last user id so we can return the user id in the json
        $userData->id = $this->db->lastInsertId();
        unset($userData->hashed_password);
    }

    public function update($id, Array $input)
    {
        $sql = "
            UPDATE user
            SET 
                firstname = :firstname,
                lastname  = :lastname,
                firstparent_id = :firstparent_id,
                secondparent_id = :secondparent_id
            WHERE id = :id;
        "; // TODO implement bind parameters

        try {
            $statement = $this->db->prepare($sql);
            $statement->execute(array(
                'id' => (int) $id,
                'firstname' => $input['firstname'],
                'lastname'  => $input['lastname'],
                'firstparent_id' => $input['firstparent_id'] ?? null,
                'secondparent_id' => $input['secondparent_id'] ?? null,
            ));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            error_log("Database Error: " . $e->getMessage(), 0);
            $responseObj = new Response();
            $responseObj->errorResponse([$e->getMessage()], 500);
        }
    }

    public function delete($id)
    {
        $sql = "
            DELETE FROM user
            WHERE id = :id;
        "; // TODO implement bind parameters

        try {
            $statement = $this->db->prepare($sql);
            $statement->execute(array('id' => $id));
            return $statement->rowCount();
        } catch (\PDOException $e) {
            error_log("Database Error: " . $e->getMessage(), 0);
            $responseObj = new Response();
            $responseObj->errorResponse([$e->getMessage()], 500);
        }
    }

    // Validation Functions

    // This function validates all the input data required to create a new user
    public function validateUser($inputData)
    {
        $username = $this->validateUsername($inputData->username ?? null);
        $email = $this->validateEmail($inputData->email ?? null);
        $hashed_password = $this->hashPassword($inputData->password ?? null);
        $first_name = $this->validateName($inputData->first_name ?? null);
        $last_name = $this->validateName($inputData->last_name ?? null);
        $date_of_birth = $this->validateDateOfBirth($inputData->date_of_birth ?? null);
        $gender = $this->validateGender($inputData->gender ?? null);
        $country_id = $this->validateCountryId($inputData->country_id ?? null);
//        // TODO Add multiple phone numbers

        return (object) [
            'username' => $username,
            'email' => $email,
            'hashed_password' => $hashed_password,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'date_of_birth' => $date_of_birth,
            'gender' => $gender,
            'country_id' => $country_id
        ];
    }
    // The rest of the validation functions validate specific fields/properties
    public static function valivateId($id)
    {
        if (!($id ?? false)) {
            throw new UserException("User Id is required for an existing user");
        }
        // Must be an integer > 0
        if (!filter_var($id, FILTER_VALIDATE_INT, array("options" => array("min_range"=>1)))) {
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
