<?php
require "../bootstrap.php";
use Src\Controller\Response;
use Src\Controller\UserController;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode( '/', $uri );

// all of our endpoints start with /user
// everything else results in a 404 Not Found
// TODO Add more routes ex: Contacts and Sessions
if ($uri[1] !== 'user') {
    $response = new Response();
    $response->errorResponse(["Endpoint not found"], 405);
}

// the user id is, of course, optional and must be a number:
$userId = null;
if (isset($uri[2])) {
    $userId = (int) $uri[2];
}

$requestMethod = $_SERVER["REQUEST_METHOD"];

// pass the request method and user ID to the UserController and process the HTTP request:
$controller = new UserController($dbConnection, $requestMethod, $userId);
$controller->processRequest();
