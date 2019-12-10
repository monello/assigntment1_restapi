<?php

require_once('../controllers/db.php');
require_once('../models/Response.php');

// Test the Database Connection
try {
    $db = DB::connectDB();
}
catch(PDOException $exception) {
    // log connection error for troubleshooting and return a json error response
    error_log("Connection Error: " . $exception, 0);
    $response = new Response();
    $response->setHttpStatusCode(500);
    $response->setSuccess(false);
    $response->addMessage("Database Connection Error");
    $response->send();
    exit;
}

// Test an SQL query
try {
    $db = DB::connectDB();
    $query = $db->prepare('select * from lst_countries');
    $query->execute();

    // Get the Row Count
    $rowCount = $query->rowCount();
}
catch(PDOException $exception) {
    $response = new Response();
    $response->setHttpStatusCode(500);
    $response->setSuccess(false);
    $response->addMessage("There was an running the SQL");
    $response->send();
    exit;
}

// Test a successful reponse
$response = new Response();
$response->setSuccess(true);
$response->setHttpStatusCode(200);
$response->addMessage('Records found: ' . $rowCount);
$response->addMessage('All seems good - FooBar');
$response->send();