<?php
include("settings_rek.php");

$link = mysqli_connect($MySqlHostname, $MySqlUsername, $MySqlPassword) or die("Unable to connect to database");

mysqli_select_db($link, $db) or die("Unable to select database");

$r_link = $link;

$host = $_SERVER['HTTP_HOST'];
