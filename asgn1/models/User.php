<?php

require_once('../controllers/UserException.php');

class User
{

    private $is_new;
    private $id;
    private $username;
    private $email;
    private $first_name;
    private $last_name;
    private $date_of_birth;
    private $gender;
    private $country_id;
    private $is_active = true;
    private $login_attempts = 0;

    // TODO Must still handle multiple phone numbers
    public function __construct(
        $is_new,
        $id,
        $username,
        $email,
        $first_name,
        $last_name,
        $date_of_birth,
        $gender,
        $country_id,
        $is_active,
        $login_attempts
    ) {
        $this->setisNew($is_new);
        $this->setID($id, $is_new);
        $this->setUsername($username);
        $this->setEmail($email);
        $this->setFirstName($first_name);
        $this->setLastName($last_name);
        $this->setDateOfBirth($date_of_birth);
        $this->setGender($gender);
        $this->setCountryId($country_id);
        $this->setIsActive($is_active);
        $this->setLoginAttempts($login_attempts);
    }

    // Setters
    public function setIsNew($is_new)
    {
        if (!is_bool($is_new)) {
            throw new UserException("Is-New Indicator invalid");
        }
        $this->is_new = (bool) $is_new;
    }
    public function setId($id, $is_new)
    {
        if ($is_new) return;
        $this->id = self::valivateId($id);
    }
    public function setUsername($username)
    {
        $this->username = self::validateUsername($username);
    }
    public function setEmail($email)
    {
        $this->email = self::validateEmail($email);
    }
    public function setFirstName($first_name)
    {
        $this->first_name = self::validateName($first_name);
    }
    public function setLastName($last_name)
    {
        $this->last_name = self::validateName($last_name);
    }
    public function setDateOfBirth($date_of_birth)
    {
        $this->date_of_birth = self::validateDateOfBirth($date_of_birth);
    }
    public function setGender($gender)
    {
        $this->gender = self::validateGender($gender);
    }
    public function setCountryId($country_id)
    {
        $this->country_id = self::validateCountryId($country_id);
    }
    public function setIsActive($is_active)
    {
        // TODO Validation
        $this->is_active = $is_active;
    }
    public function setLoginAttempts($login_attempts)
    {
        // TODO Validation
        $this->login_attempts = $login_attempts;
    }

    // Getters
    public function getId()
    {
        return $this->id;
    }
    public function getUsername()
    {
        return $this->username;
    }
    public function getEmail()
    {
        return $this->email;
    }
    public function getFirstName()
    {
        return $this->first_name;
    }
    public function getLastName()
    {
        return $this->last_name;
    }
    public function getDateOfBirth()
    {
        return $this->date_of_birth;
    }
    public function getGender()
    {
        return $this->gender;
    }
    public function getCountryId()
    {
        return $this->country_id;
    }
    public function getIsActive()
    {
        return $this->is_active;
    }
    public function getLoginAttempts()
    {
        return $this->login_attempts;
    }

    // Supporting Functions
    public function returnUserAsArray()
    {
        $user = [];
        $user['id'] = $this->getId();
        $user['username'] = $this->getUsername();
        $user['email'] = $this->getEmail();
        $user['first_nane'] = $this->getFirstName();
        $user['last_nane'] = $this->getLastName();
        $user['date_of_birth'] = $this->getDateOfBirth();
        $user['gender'] = $this->getGender();
        $user['country_id'] = $this->getCountryId();
        $user['is_active'] = $this->getIsActive();
        $user['login_attempts'] = $this->getLoginAttempts();
        return $user;
    }

        // Static Validation Functions
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
        public static function validateUsername($username)
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
        public static function validateEmail($email)
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
        public static function validatePassword($password)
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
        public static function hashPassword($password)
        {
            $password = self::validatePassword($password);
            return password_hash($password, PASSWORD_DEFAULT);
        }
        public static function validateName($name)
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
        public static function validateDateOfBirth($date_of_birth)
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

            $date_of_birth = new DateTime($date_of_birth);
            $now = new DateTime();
            $diff = $now->diff($date_of_birth);
            // check that it is not in the future
            // chech that the user is between 6 and 120 years old
            if ($date_of_birth > $now || $diff->y < 6 || $diff->y > 120) {
                throw new UserException("Date of birth is not in a valid date range");
            }

            return $date_of_birth;
        }
        public static function validateGender($gender)
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
        public static function validateCountryId($country_id)
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
}
