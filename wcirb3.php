<?php
error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED);
ini_set('display_errors', '1');

include("api/connection.php");

$home = file_get_contents("https://www.caworkcompcoverage.com/SearchResults.aspx?name=MATRIX");

die($home);
?>