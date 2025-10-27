<?php
include("api/connection.php");
require_once('shared/legacy_session.php');
session_start();

// Check if the user is logged in
if (!isset($_SESSION["user_customer_id"])) {
    die("Access denied. Please log in.");
}

if (file_exists($_GET['file'])) {
    echo json_encode(["status" => true, "message" => "File exists"]);
    exit;
    // http_response_code(200); // Send HTTP 200 if file exists
    // echo "File exists";
} else {
    echo json_encode(["status" => false, "message" => "File does not exists"]);
    exit;
    // http_response_code(404); // Send HTTP 404 if file does not exist
    // echo "File does not exist";
}
http_response_code(400); // Send HTTP 400
exit;
?>
