<?php
/*
Uploadify
Copyright (c) 2012 Reactive Apps, Ronnie Garcia
Released under the MIT License <http://www.opensource.org/licenses/mit-license.php> 
*/

// Define a destination
$targetFolder = '/autho/web/uploads/'; // Relative to the root

//$verifyToken = md5('unique_salt' . $_POST['timestamp']);
// && $_POST['token'] == $verifyToken
if (!empty($_FILES)) {
	$tempFile = $_FILES['Filedata']['tmp_name'];
	$targetPath = $_SERVER['DOCUMENT_ROOT'] . $targetFolder;
	$targetFile = rtrim($targetPath,'/') . '/' . $_FILES['Filedata']['name'];
	$arrFile = explode(".", $targetFile);
	$filename_itself = $arrFile[count($arrFile)-2];
	//add the id
	$filename_itself .= "_" . date("Ymdhis");
	$arrFile[count($arrFile)-2] = $filename_itself;
	$targetFile = implode(".", $arrFile);
	
	// Validate the file type
	$fileTypes = array('jpg','jpeg','JPG','JPEG','gif','png','pdf'); // File extensions
	$fileParts = pathinfo($_FILES['Filedata']['name']);
	
	if (in_array($fileParts['extension'],$fileTypes)) {
		move_uploaded_file($tempFile,$targetFile);
		
		$targetFile = $arrFile[count($arrFile)-2] . "." . $arrFile[count($arrFile)-1];
		
		$targetFile = str_replace("/home/cstmwb/public_html/autho/web/uploads/", "", $targetFile);
		echo $targetFile;		
	} else {
		echo 'Invalid file type.';
	}
}
