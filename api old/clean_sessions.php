<?php
error_reporting(E_ALL ^ E_WARNING);
ini_set('display_errors', '1');


// Define the folder to clean
// (keep trailing slashes)
$captchaFolder  = 'C:\\inetpub\\wwwroot\\iKase.org\\sessions\\';


$fileTypes      = '*.txt';

// Here you can define after how many
// minutes the files should get deleted
$expire_time    = 2880; 
 
// Find all files of the given file type
foreach (glob($captchaFolder . $fileTypes) as $Filename) {
 
	// Read file creation time
	$FileCreationTime = filemtime ($Filename);
 
	// Calculate file age in seconds
	$FileAge = time() - $FileCreationTime; 
 
	// Is the file older than the given time span?
	if ($FileAge > ($expire_time * 60)){
 
		// Now do something with the olders files...
 
		echo "Delete file $Filename: ";
 
		// For example deleting files:
		unlink($Filename);
		
		//echo date("Y-m-d H:i:s", $FileCreationTime) . "<br /><br />";
		//die("stop");
	}
 
}

die("done at " . date("H:i:s"));
?>