<?php
require "../bootstrap.php";
use Src\Controller\Response;
use Src\Controller\UserController;
use Src\Controller\PhoneController;
use Src\Controller\ContactController;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");


$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER["REQUEST_METHOD"];

switch (true) {
    case preg_match('/^\/user\/?$/', $uri):
        $controller = new UserController($dbConnection, $requestMethod, null);
        break;
    case preg_match_all('/^\/user\/([0-9]+)\/?$/', $uri, $matches):
        $userId = $matches[1][0];
        $controller = new UserController($dbConnection, $requestMethod, $userId);
        break;
    case preg_match('/^\/user\/phone\/?$/', $uri):
        $controller = new PhoneController($dbConnection, $requestMethod, null);
        break;
    case preg_match_all('/^\/user\/phone\/([0-9]+)\/?$/', $uri, $matches):
        $phoneId = $matches[1][0];
        $controller = new PhoneController($dbConnection, $requestMethod, $phoneId);
        break;
    case preg_match('/^\/contact\/?$/', $uri):
        $controller = new ContactController($dbConnection, $requestMethod, null);
        break;
    case preg_match_all('/^\/contact\/([0-9]+)\/?$/', $uri, $matches):
        $contactId = $matches[1][0];
        $controller = new ContactController($dbConnection, $requestMethod, $contactId);
        break;
    default:
        $response = new Response();
        $response->errorResponse(["Endpoint not found"], 405);
}

$controller->processRequest();



