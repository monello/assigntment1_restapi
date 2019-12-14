<?php
require "../bootstrap.php";
use Src\Controller\Response;
use Src\Controller\SessionController;
use Src\Controller\UserController;
use Src\Controller\UserContactController;
use Src\Controller\ContactController;
use Src\Controller\ListController;

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: OPTIONS,GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestMethod = $_SERVER["REQUEST_METHOD"];

switch (true) {
    case preg_match('/^\/session\/?$/', $uri):
        $controller = new SessionController($dbConnection, $requestMethod, $uri, null);
        break;
    case preg_match_all('/^\/session\/([0-9]+)\/?$/', $uri, $matches):
        $sessionId = $matches[1][0];
        $controller = new SessionController($dbConnection, $requestMethod, $uri, $sessionId);
        break;
    case preg_match('/^\/user\/?$/', $uri):
        $controller = new UserController($dbConnection, $requestMethod, $uri, null);
        break;
    case preg_match_all('/^\/user\/([0-9]+)\/?$/', $uri, $matches):
    case preg_match_all( '/^\/user\/([0-9]+)\/(username|password|email)\/?$/', $uri, $matches):
        $userId = $matches[1][0];
        $controller = new UserController($dbConnection, $requestMethod, $uri, $userId);
        break;
    case preg_match_all('/^\/user\/([0-9]+)\/contactnumber\/?$/', $uri,$matches):
        $userId = $matches[1][0];
        $controller = new UserContactController($dbConnection, $requestMethod, $uri, $userId, null);
        break;
    case preg_match_all('/^\/user\/([0-9]+)\/contactnumber\/([0-9]+)\/?$/', $uri, $matches):
        $userId = $matches[1][0];
        $contactNumberId = $matches[2][0];
        $controller = new UserContactController($dbConnection, $requestMethod, $uri, $userId, $contactNumberId);
        break;
    case preg_match('/^\/contact\/?$/', $uri):
        $controller = new ContactController($dbConnection, $requestMethod, $uri, null);
        break;
    case preg_match_all('/^\/contact\/([0-9]+)\/?$/', $uri, $matches):
        $contactId = $matches[1][0];
        $controller = new ContactController($dbConnection, $requestMethod, $uri, $contactId);
        break;
    case preg_match_all('/^\/list\/([a-z]+)\/?$/', $uri, $matches):
        $list = $matches[1][0];
        $controller = new ListController($dbConnection, $requestMethod, $uri, $list);
        break;
    default:
        $response = new Response();
        $response->errorResponse(["Endpoint not found - foobar"], 405);
}

$controller->processRequest();
