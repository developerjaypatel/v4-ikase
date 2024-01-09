<?php
include ("connection.php");

$contents = $HTTP_RAW_POST_DATA;

$filename = $_SERVER['DOCUMENT_ROOT'] . "\\chats\\-1\\xraw.html";

$fptr = fopen($filename0, "w"); 
fputs($fptr, $contents); 
fclose($fptr);
