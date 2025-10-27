<?php
/* include("api/connection.php");
include("api/manage_session.php");
session_start();
// var_dump($_SESSION);
// Check if the user is logged in
if (!isset($_SESSION["user_customer_id"])) {
    die("Access denied. Please log in.");
}

$path = $_GET['file'];//echo $path;die;
if (!$path || !file_exists($path)) {
    die("Image not found.");
}

// Default thumbnail coming without drive name and full path so need to modify it
// echo strpos($path,"merge_documents"); die();
if($path=="merge_documents/default_file_placeholder.jpg")
{
    $path = "D:/ikase.org/".$path;
} 

// Depends on file extension decide Content-Type
$filename = $path;
$extension = pathinfo($filename, PATHINFO_EXTENSION);
// echo $path; die();
if($extension=="pdf"){
    header("Content-Type: application/pdf");
}elseif($extension=="jpeg" || $extension=="jpg" || $extension=="png"){
    header("Content-Type: image/jpeg");
}
readfile($path);
exit; */

include("api/connection.php");
require_once('shared/legacy_session.php');
session_start();
//print_r($_SESSION);//die;
// Check if the user is logged in
if (!isset($_SESSION["user_customer_id"])) {
    die("Access denied. Please log in.");
}

$path = $_GET['file'];
if (!$path || !file_exists($path)) {
    die("Image not found.");
}

// Depends on file extension decide Content-Type
$filename = $path;
$extension = pathinfo($filename, PATHINFO_EXTENSION);
if($extension=="pdf"){
    header("Content-Type: application/pdf");
}elseif($extension=="jpeg" || $extension=="jpg" || $extension=="png"){
    header("Content-Type: image/jpeg");
}
readfile($path);
exit;
?>
