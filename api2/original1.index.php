<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
ini_set('display_errors', '1');

$http_origin = $_SERVER['HTTP_ORIGIN'];

if ($http_origin == "https://www.matrixdocuments.com" || $http_origin == "https://www.cajetfile.com" || $http_origin == "https://www.ikase.xyz") {  
    header("Access-Control-Allow-Origin: $http_origin");
}

//include("manage_session.php");

session_start();

date_default_timezone_set('America/Los_Angeles');

require 'Slim/Slim.php';
include("connection.php");

$app = new Slim();

$app->get('/hello/:name', function ($name) {
    echo $name . " says hello on " . date("m/d/Y H:i:s");
});
?>