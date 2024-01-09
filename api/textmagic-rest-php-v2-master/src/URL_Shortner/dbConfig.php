<?php
// Database configuration
// $dbHost     = "localhost";
// $dbUsername = "root";
// $dbPassword = "";
// $dbName     = "ikase";

// $dbHost     = "25.70.61.4";
// $dbUsername = "root";
// $dbPassword = "admin527#";
// $dbName     = "ikase";

$dsn = 'mysql:dbname=ikase;host=25.70.61.4';
$dbUsername = "root";
$dbPassword = "admin527#";

//$mysql = "mysql";
// Create database connection
try{
  
$db = new PDO($dsn, $dbUsername, $dbPassword);

   //return $db = new PDO($dbHost,$dbName, $dbUsername, $dbPassword);
}catch(PDOException $e){
    echo "Connection failed: " . $e->getMessage();
}