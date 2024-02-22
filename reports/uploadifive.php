<?php
session_start();
/*
UploadiFive
Copyright (c) 2012 Reactive Apps, Ronnie Garcia
*/
$targetFile = "";
$attachmentFiles = array();
//die(print_r($_FILES));
if (!empty($_FILES)) {
	for($i=0;$i<count($_FILES['Filedata']['name']);$i++)
	{
		//die("err:" . $_FILES["Filedata"]["error"][$i]);
		if ($_FILES["Filedata"]["error"][$i]!=4) {
			//kase specific?
			// Set the upload directory
			$uploadDir = '\\uploads\\' . $_SESSION['user_customer_id'] . '\\';
			$uploadDir .= $case_id . '\\';
			
			if (!is_dir($_SERVER['DOCUMENT_ROOT'] . $uploadDir)) {
				mkdir($_SERVER['DOCUMENT_ROOT'] . $uploadDir, 0755, true);
			}
			
			// Set the allowed file extensions
			$fileTypes = array('jpg', 'jpeg', 'gif', 'png', 'pdf', 'fdf', 'rtf', 'txt', 'csv', 'doc', 'docx'); // Allowed file extensions
			
		
			$tempFile   = $_FILES['Filedata']['tmp_name'][$i];
			$uploadDir  = $_SERVER['DOCUMENT_ROOT'] . $uploadDir;
			$targetFile = $_FILES['Filedata']['name'][$i];
			$document_counter = 1;
			
			if ($_SERVER['REMOTE_ADDR']=='47.153.49.248') {
			//	die("t:" . $targetFile);
			}
			//if a specific directory is requested, the file will be overwritten
			while (file_exists($uploadDir . $targetFile)) {
				//break up the file name with ., add an increment in parentheses
				$arrFile = explode(".", $_FILES['Filedata']['name'][$i]);
				$arrFile[count($arrFile)-1] = $document_counter . "." .  $arrFile[count($arrFile)-1];
				
				$targetFile = implode("", $arrFile);
				//echo $uploadDir . $targetFile . " ==> " . file_exists($uploadDir . $targetFile) . "\r\n";
				//die("c:" . $targetFile);
				$document_counter++;
			}
		
			$targetFile = $uploadDir. $targetFile;
			
			$arrFile = explode(".", $targetFile);
			
			$thumbFile = "";
			if ($upload_dir=="") {
				if (strtolower($arrFile[count($arrFile)-1])=="pdf") {
					$thumbFile = str_replace(".pdf", ".jpg", $targetFile);
					$thumbFile = str_replace("/uploads/", "/pdfimage/", $thumbFile);
				}
			}
			// Validate the filetype
			$fileParts = pathinfo($_FILES['Filedata']['name'][$i]);
			if (in_array(strtolower($fileParts['extension']), $fileTypes)) {
				// Save the file
				//die( $targetFile . "<br />");
				move_uploaded_file($tempFile, $targetFile);
				
				if ($thumbFile!="") {
					//execute imageMagick's 'convert', setting the color space to RGB
					//This will create a jpg having the widthg of 200PX
					exec("convert \"{$targetFile}[0]\" -colorspace RGB -geometry 200 $thumbFile");
				}
				
				$arrFileDetails = explode("\\", $targetFile);
				$targetFile = $arrFileDetails[count($arrFileDetails) - 1];
			} else {
				// The file type wasn't allowed
				//echo 'Invalid file type.';
			}

			$attachmentFiles[] = $targetFile;
		}
	}
}
$attachmentFiles = implode(",", $attachmentFiles);
$targetFile = $attachmentFiles;
?>