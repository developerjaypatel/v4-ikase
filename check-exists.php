<?php
echo "0";
die();

session_start();

$customer_id = $_SESSION['user_customer_id'];
/*
UploadiFive
Copyright (c) 2012 Reactive Apps, Ronnie Garcia
*/

// Define a destination
$targetFolder = '\\uploads'; // Relative to the root and should match the upload folder in the uploader script
//echo $_SERVER['DOCUMENT_ROOT'] . $targetFolder . '/' . $customer_id . '/' . $_POST['filename'] . "\r\n";

$filename = $_SERVER['DOCUMENT_ROOT'] . $targetFolder . '\\' . $customer_id . '\\' . $_POST['filename'];
if (file_exists($filename)) {	
	echo "1|Upload date: " . date ("l, F jS Y \@ g:iA", filemtime($filename));
} else {
	echo 0;
}
?>