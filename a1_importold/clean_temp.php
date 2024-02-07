<?php
error_reporting(E_ALL ^ E_WARNING);
ini_set('display_errors', '1');



// Define the folder to clean
// (keep trailing slashes)
$captchaFolder  = 'C:\\Windows\\Temp\\';


$fileTypes      = '*.tmp';

// Here you can define after how many
// minutes the files should get deleted
$expire_time    = 120; 
 
// Find all files of the given file type
foreach (glob($captchaFolder . $fileTypes) as $Filename) {
 
	// Read file creation time
	$FileCreationTime = filectime($Filename);
 
	// Calculate file age in seconds
	$FileAge = time() - $FileCreationTime; 
 
	// Is the file older than the given time span?
	if ($FileAge > ($expire_time * 60)){
 
		// Now do something with the olders files...
 
		print "Delete file $Filename\n";
 
		// For example deleting files:
		unlink($Filename);
		
		//echo "\r\n";
		//die("stop");
	}
 
}

$fileTypes      = 'magick*';

// Here you can define after how many
// minutes the files should get deleted
$expire_time    = 120; 
 
// Find all files of the given file type
foreach (glob($captchaFolder . $fileTypes) as $Filename) {
 
	// Read file creation time
	$FileCreationTime = filectime($Filename);
 
	// Calculate file age in seconds
	$FileAge = time() - $FileCreationTime; 
 
	// Is the file older than the given time span?
	if ($FileAge > ($expire_time * 60)){
 
		// Now do something with the olders files...
 
		print "Delete file $Filename\n";
		
		//die();
		// For example deleting files:
		unlink($Filename);
		
	}
 
}
$fileTypes      = 'sess_*';

// Here you can define after how many
// minutes the files should get deleted
$expire_time    = 2880; 
 
// Find all files of the given file type
foreach (glob($captchaFolder . $fileTypes) as $Filename) {
 
	// Read file creation time
	$FileCreationTime = filectime($Filename);
 
	// Calculate file age in seconds
	$FileAge = time() - $FileCreationTime; 
 
	// Is the file older than the given time span?
	if ($FileAge > ($expire_time * 60)){
 
		// Now do something with the olders files...
 
		print "Delete file $Filename\n";
		
		//die();
		// For example deleting files:
		unlink($Filename);
		
	}
 
}

die();
?>