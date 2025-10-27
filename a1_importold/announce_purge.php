<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');

$dir = "C:\\inetpub\\wwwroot\\ikase.org\\uploads\\" . $customer_id . "\\announce\\";
	
$files = scandir($dir);
$prefix =  date("Ymd");

foreach($files as $file) {
	if (strpos($file, ".docx")!==false) {
		if (strpos($file, $prefix)===false) {
			unlink($dir . $file);
		}
	}
}

echo "done";
