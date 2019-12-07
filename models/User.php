<?php

require_once('../controllers/UserException.php');

class User
{

    private $is_new;
    private $id;
    private $username;
    private $email;
    private $password;
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
        $password,
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
        $this->setPassword($password);
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
        $this->id = (int) $id;
    }
    public function setUsername($username)
    {
        // TODO Validation
        $this->username = trim($username);
    }
    public function setEmail($email)
    {
        // TODO Validation
        $this->email = trim($email);
    }
    public function setPassword($password)
    {
        // TODO Validation
        $this->password = trim($password);
    }
    public function setFirstName($first_name)
    {
        // TODO Validation
        $this->first_name = trim($first_name);
    }
    public function setLastName($last_name)
    {
        // TODO Validation
        $this->last_name = trim($last_name);
    }
    public function setDateOfBirth($date_of_birth)
    {
        // TODO Validation
        $this->date_of_birth = $date_of_birth;
    }
    public function setGender($gender)
    {
        // TODO Validation
        $this->gender = $gender;
    }
    public function setCountryId($country_id)
    {
        // TODO Validation
        $this->country_id = $country_id;
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
}
