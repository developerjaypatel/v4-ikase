<?php
	include("settings.php");
	/* make connection to database */
	/* If no connection made, display error Message */
	$link = mysql_connect($MySqlHostname, $MySqlUsername, $MySqlPassword) OR die("Unable to connect to database");   	
	//die($MySqlHostname.", ".$MySqlUsername.", ". $MySqlPassword);   
	
	/* Select the database name to be used or else print error message if unsuccessful*/
	mysql_select_db($db, $link) or die( "Unable to select database"); 
	
	$r_link = $link;
	
	$host = $_SERVER['HTTP_HOST'];
?>