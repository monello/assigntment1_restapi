<?php
require_once('db.php');
require_once('../models/Response.php');
require_once('../models/User.php');

// Set up a db connection
try {
    $db = DB::connectDB();
} catch (PDOException $exception) {
    $message = $exception->getMessage();
    error_log("Database Connection Error: " . $message, 0);
    errorResponse(["Database Connection Error"], 500);
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'POST':
        createUser($db);
        break;
    case 'PATCH': // do partial updated vs PUT that does a full replace
        updateUser($db);
        break;
    case 'GET':
        getUser($db);
        break;
    case 'DELETE':
        deleteUser($db);
        break;
    default:
        errorResponse(["Endpoint not found"], 405);
}

// REQUEST: 'POST /users' | SUCCESS: 201
function createUser($db)
{
    $jsonData = getJsonData();

    // TODO Must still handle multiple phone numbers
    try {
        $user = new User(
            true,
            null,
            $jsonData->username ?? null,
            $jsonData->email ?? null,
            $jsonData->first_name ?? null,
            $jsonData->last_name ?? null,
            $jsonData->date_of_birth ?? null,
            $jsonData->gender ?? null,
            $jsonData->country_id ?? null,
            null,
            0
        );
    } catch(UserException $exception) {
        $message = $exception->getMessage();
        errorResponse(["Unable to create User", $message], 400);
    }

    // Check that username is unique
        checkUniqueUsername($db, $user);

    // Check that email is unique
    checkUniqueEmail($db, $user);

    // Validate and hash password
    $password = \User::validatePassword($jsonData->password);
    $hashed_password = \User::hashPassword($password);

    // Object to variables
    $username       = $user->getUsername();
    $email          = $user->getEmail();
    $first_name     = $user->getFirstName();
    $last_name      = $user->getLastName();
    $date_of_birth  = $user->getDateOfBirth()->format('Y-m-d');
    $gender         = $user->getGender();
    $country_id     = $user->getCountryId();

    try {
        // Insert the User record
        $sql = 'INSERT into user (username, email, password, first_name, last_name, date_of_birth, gender, country_id) values (:username, :email, :password, :first_name, :last_name, :date_of_birth, :gender, :country_id)';
        $query = $db->prepare($sql);
        $query->bindParam(':username', $username, PDO::PARAM_STR);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->bindParam(':password', $hashed_password, PDO::PARAM_STR);
        $query->bindParam(':first_name', $first_name, PDO::PARAM_STR);
        $query->bindParam(':last_name', $last_name, PDO::PARAM_STR);
        $query->bindParam(':date_of_birth', $date_of_birth, PDO::PARAM_STR);
        $query->bindParam(':gender', $gender, PDO::PARAM_STR);
        $query->bindParam(':country_id', $country_id, PDO::PARAM_INT);
        $query->execute();
    } catch (PDOException $exception) {
        $message = $exception->getMessage();
        error_log("Database Insert-User Query Error: " . $message, 0);
        errorResponse(["Database Query Error: ", "Insert statement invalid"], 500);
    }

    // get row count
    $rowCount = $query->rowCount();

    if($rowCount === 0) {
        errorResponse(["There was an error creating the user account - please try again"], 500);
    }

    // Get last user id so we can return the user id in the json
    $user->setId($db->lastInsertId(), false);

    // Set some defaults
    $user->setIsActive(true);
    $user->setLoginAttempts(0);

    // Prepare the return data
    $userData = $user->returnUserAsArray();
    $returnData = [];
    $returnData['rows_returned'] = $rowCount;
    $returnData['users'] = $userData;

    successResponse(["User Created"], 201, $returnData);
}

// REQUESTL 'PATCH /users/##' | SUCCESS: 200
function updateUser($db)
{
    // Prepare the return data
    $returnData = [];
    $returnData['rows_returned'] = 1;
    $returnData['tasks'] = [];

    successResponse("User Updated", 200, $returnData);
}

// REQUEST: 'GET /users/##' | SUCCESS: 200
function getUser($db)
{
    // Prepare the return data
    $returnData = [];
    $returnData['rows_returned'] = 1;
    $returnData['tasks'] = [];

    successResponse("User Found", 200, $returnData);
}

// REQUEST: 'DELETE /users/##' | SUCCESS: 200
function deleteUser($db)
{
    // Prepare the return data
    $returnData = [];
    $returnData['rows_returned'] = 1;
    $returnData['tasks'] = [];

    successResponse(["User Deleted"], 200, $returnData);
}

function checkUniqueUsername($db, $user)
{
    try {
        // Check if the username already exists
        $username = $user->getUserName();
        $query = $db->prepare('SELECT id from user where username = :username');
        $query->bindParam(':username', $username, PDO::PARAM_STR);
        $query->execute();
        // Get the row-count
        $rowCount = $query->rowCount();
        if($rowCount !== 0) {
            errorResponse(["Username already exists"], 409);
        }
    } catch (PDOException $exception) {
        $message = $exception->getMessage();
        error_log("Database Insert-User Query Error: " . $message, 0);
        errorResponse(["There was an issue creating a user account"], 500);
    }
}

function checkUniqueEmail($db, $user)
{
    try {
        // Check if the username already exists
        $email = $user->getEmail();
        $query = $db->prepare('SELECT id from user where email = :email');
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->execute();
        // Get the row-count
        $rowCount = $query->rowCount();
        if($rowCount !== 0) {
            errorResponse(["Email Address already exists"], 409);
        }
    } catch (PDOException $exception) {
        errorResponse(["There was an issue creating a user account"], 500);
    }
}

function getJsonData()
{
     // Check tha the request's Content-Type header is JSON
    if($_SERVER['CONTENT_TYPE'] !== 'application/json') {
        errorResponse(["Content Type header not set to JSON"], 400);
    }
    // Check that the posted content is in JSON Format
    $rawPostData = file_get_contents('php://input');
    if(!$jsonData = json_decode($rawPostData)) {
        errorResponse(["Request body is not valid JSON"], 400);
    }
    return $jsonData;
}

function successResponse($messages, $code=200, $returnData=[])
{
    $response = new Response();
    $response->setHttpStatusCode($code);
    $response->setSuccess(true);
    if (count($messages)) {
        foreach ($messages as $message) {
            $response->addMessage($message);
        }
    }
    $response->setData($returnData);
    $response->send();
    exit;
}

function errorResponse($messages, $code)
{
    $response = new Response();
    $response->setHttpStatusCode($code);
    $response->setSuccess(false);
    if (count($messages)) {
        foreach ($messages as $message) {
            $response->addMessage($message);
        }
    }
    $response->send();
    exit;
}
