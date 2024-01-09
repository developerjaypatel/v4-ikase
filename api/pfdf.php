<?php
$contents = $HTTP_RAW_POST_DATA;
$filename = $_SERVER['DOCUMENT_ROOT'] . "\\chats\\-1\\pdf.html";
$fptr = fopen($filename, "w"); 
fputs($fptr, $contents); 
fclose($fptr);
die();
