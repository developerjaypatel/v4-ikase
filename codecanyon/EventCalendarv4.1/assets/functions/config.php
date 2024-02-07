<?php 

    // These variables define the connection information for your MySQL database 
    $username = "josecarl"; 
    $password = "5W3v2p5wYe"; 
    $host = "localhost"; 
    $dbname = "josecarl_vistoner"; 	
	
    $options = array(PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'); 
    try { 
    $db = new PDO("mysql:host={$host};dbname={$dbname};charset=utf8", $username, $password, $options); 
	}
	catch(PDOException $ex) { 
		die("Database credentials are incorrect. {$ex->getMessage()}");
	}
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
    $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC); 
    header('Content-Type: text/html; charset=utf-8'); 
	
	
	
	// 1. Create a database connection
	$conection=mysqli_connect($host,$username,$password,$dbname);
	if (!$conection) {
		die("Database connection failed: " . mysqli_error());
	}

	// 2. Select a database to use 
	$db_selected = mysqli_select_db($conection, $dbname);
	if (!$db_selected) {
		die("Database selection failed: " . mysqli_error());
	}
	
    session_start(); 
?>