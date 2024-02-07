<?php
error_reporting(E_ALL ^ E_DEPRECATED);
ini_set('display_errors', '1');	

include("manage_session.php");
session_write_close();
/*
UploadiFive
Copyright (c) 2012 Reactive Apps, Ronnie Garcia
*/
//kase specific?
$case_id = "";
if (isset($_POST['case_id'])) {
	$case_id = $_POST['case_id'];
	if ($case_id == -1) {
		$case_id = "";
	}
}
$upload_dir = "";
if (isset($_POST['upload_dir'])) {
	$upload_dir = $_POST['upload_dir'];
}
if ($upload_dir=="") {
	// Set the upload directory
	$uploadDir = '\\uploads\\' . $_SESSION['user_customer_id'] . '\\';
	if ($case_id != "") {
		$uploadDir .= $case_id . '\\';
	}
} else {
	$uploadDir = "\\" . $upload_dir . "\\";
}
$uploadThumbDir = str_replace("uploads", "pdf_image", $uploadDir);

//die($_SERVER['DOCUMENT_ROOT'] . $uploadDir . "<br />" . is_dir($_SERVER['DOCUMENT_ROOT'] . $uploadDir));
if (!is_dir($_SERVER['DOCUMENT_ROOT'] . $uploadDir)) {
	mkdir($_SERVER['DOCUMENT_ROOT'] . $uploadDir, 0755, true);
}
if (!is_dir($_SERVER['DOCUMENT_ROOT'] . $uploadThumbDir)) {
	mkdir($_SERVER['DOCUMENT_ROOT'] . $uploadThumbDir, 0755, true);
}
if (!is_dir($_SERVER['DOCUMENT_ROOT'] . $uploadDir . "medium")) {
	mkdir($_SERVER['DOCUMENT_ROOT'] . $uploadDir . "medium", 0755, true);
}
if (!is_dir($_SERVER['DOCUMENT_ROOT'] . $uploadThumbDir . "medium")) {
	mkdir($_SERVER['DOCUMENT_ROOT'] . $uploadThumbDir . "medium", 0755, true);
}
if (!is_dir($_SERVER['DOCUMENT_ROOT'] . $uploadDir . "thumbnail")) {
	mkdir($_SERVER['DOCUMENT_ROOT'] . $uploadDir . "thumbnail", 0755, true);
}
if (!is_dir($_SERVER['DOCUMENT_ROOT'] . $uploadThumbDir . "thumbnail")) {
	mkdir($_SERVER['DOCUMENT_ROOT'] . $uploadThumbDir . "thumbnail", 0755, true);
}

//die($_SERVER['DOCUMENT_ROOT'] . $uploadDir);
//Define the directory to store the PDF Preview Image
$thumbDirectory = '/pdfimage/' . $_SESSION['user_customer_id'] . '/';
if (!is_dir($_SERVER['DOCUMENT_ROOT'] . $thumbDirectory)) {
	mkdir($_SERVER['DOCUMENT_ROOT'] . $thumbDirectory, 0755, true);
}
// Set the allowed file extensions
$fileTypes = array('jpg', 'jpeg', 'gif', 'png', 'pdf', 'fdf', 'rtf', 'txt', 'csv', 'mp3', 'doc', 'docx', 'mp3', 'wma'); // Allowed file extensions

$verifyToken = md5('ikase_system' . $_POST['timestamp']);
//die($_POST['token'] ."==". $verifyToken);
// && $_POST['token'] == $verifyToken
if (!empty($_FILES)) {
	$tempFile   = $_FILES['Filedata']['tmp_name'];
	$uploadDir  = $_SERVER['DOCUMENT_ROOT'] . $uploadDir;
	$targetFile = $_FILES['Filedata']['name'];
	
	$document_counter = 1;
	if ($upload_dir=="") {
		//if a specific directory is requested, the file will be overwritten
		if (file_exists($uploadDir . $targetFile)) {
			//break up the file name with ., add an increment in parentheses
			$arrFile = explode(".", $_FILES['Filedata']['name']);
			/*
			if ($document_counter < 27) {
				$suffix = "_" . chr(64 + $document_counter); 
			}
			if ($document_counter > 27) {
				$suffix .= "_" . chr(64 + $document_counter- 27) . chr(64 + $document_counter- 27); 
			}
			*/
			$suffix = "_" . date("ymjGis");
			$arrFile[count($arrFile)-1] = $suffix . "." .  $arrFile[count($arrFile)-1];
			
			$targetFile = implode("", $arrFile);
	
			//echo $uploadDir . $targetFile . " ==> " . file_exists($uploadDir . $targetFile) . "\r\n";
			//die("c:" . $targetFile);
			$document_counter++;
		}
	}
	
	$targetFile = $uploadDir. $targetFile;
	$targetFile = strtolower($targetFile);
	$targetFile = str_replace("&", "_", $targetFile);
	//die("t:" . $targetFile);
	
	$arrFile = explode(".", $targetFile);
	
	$thumbFile = "";
	if ($upload_dir=="") {
		if (strtolower($arrFile[count($arrFile)-1])=="pdf") {
			$thumbFile = str_replace(".pdf", ".jpg", $targetFile);
			$thumbFile = str_replace("\\uploads\\", "\\pdfimage\\", $thumbFile);
		}
	}
	// Validate the filetype
	$fileParts = pathinfo($_FILES['Filedata']['name']);
	if (in_array(strtolower($fileParts['extension']), $fileTypes)) {
		// Save the file
		//die( $tempFile . "<br />");
		move_uploaded_file($tempFile, $targetFile);
		
		//die($targetFile . "<br />" . $thumbFile . "<br />");
		if ($thumbFile!="") {
			//execute imageMagick's 'convert', setting the color space to RGB
    		//This will create a jpg having the widthg of 200PX
	    	//exec("convert \"{$targetFile}[0]\" -background white -colorspace RGB -geometry 625 \"$thumbFile\"");
			
			$image_magick = new imagick(); 
			$image_magick->readImage($targetFile . "[0]");
			//$image_magick = $image_magick->flattenImages();
			$image_magick = $image_magick->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
			$image_magick->setResolution(300,300);
			$image_magick->thumbnailImage(800, 800, true);
			$image_magick->setImageFormat('jpg');
			//$thumbnail_path = $upload_dir . "\\medium\\" . str_replace(".pdf", ".jpg", $file->name);
			$thumbnail_path = $thumbFile;
			
			//put it in the right place if there is a case id
			if ($case_id != "") {
				//medium thumbnail folder
				$thumbnail_path = str_replace("pdfimage", "uploads", $thumbnail_path);
				$thumbnail_path = str_replace($case_id. "\\", $case_id. "\\medium\\", $thumbnail_path);
				//die($thumbnail_path);
			}
			$image_magick->writeImage($thumbnail_path);
			
			//thumbnail
			$image_magick = new imagick(); 
			$image_magick->readImage($targetFile . "[0]");
			//$image_magick = $image_magick->flattenImages();
			$image_magick = $image_magick->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);
			$image_magick->setResolution(300,300);
			$image_magick->thumbnailImage(75, 75, true);
			$image_magick->setImageFormat('jpg');
			//$thumbnail_path = $upload_dir . "\\medium\\" . str_replace(".pdf", ".jpg", $file->name);
			$thumbnail_path = $thumbFile;
			
			//put it in the right place if there is a case id
			if ($case_id != "") {
				//medium thumbnail folder
				$thumbnail_path = str_replace("pdfimage", "uploads", $thumbnail_path);
				$thumbnail_path = str_replace($case_id. "\\", $case_id. "\\thumbnail\\", $thumbnail_path);
				$image_magick->writeImage($thumbnail_path);
				//die($targetFile);
			}
		}
		$arrFileDetails = explode("\\", $targetFile);
		$targetFile = $arrFileDetails[count($arrFileDetails) - 1];
		echo $targetFile;
	} else {
		// The file type wasn't allowed
		echo 'Invalid file type.';
	}
}
?>