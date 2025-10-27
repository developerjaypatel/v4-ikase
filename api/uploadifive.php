<?php
require_once('../shared/legacy_session.php');
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
	$uploadDir = 'D:\\uploads\\' . $_SESSION['user_customer_id'] . '\\';
	if ($case_id != "") {
		$uploadDir .= $case_id . '\\';
	}
} else {
	$uploadDir = "\\" . $upload_dir . DC;
}
$uploadThumbDir = str_replace("uploads", "pdf_image", $uploadDir);

//die($_SERVER['DOCUMENT_ROOT'] . $uploadDir . "<br />" . is_dir($uploadDir));
if (!is_dir($uploadDir)) {
	mkdir($uploadDir, 0755, true);
}
if (!is_dir($uploadThumbDir)) {
	mkdir($uploadThumbDir, 0755, true);
}
if (!is_dir($uploadDir . "medium")) {
	mkdir($uploadDir . "medium", 0755, true);
}
if (!is_dir($uploadThumbDir . "medium")) {
	mkdir($uploadThumbDir . "medium", 0755, true);
}
if (!is_dir($uploadDir . "thumbnail")) {
	mkdir($uploadDir . "thumbnail", 0755, true);
}
if (!is_dir($uploadThumbDir . "thumbnail")) {
	mkdir($uploadThumbDir . "thumbnail", 0755, true);
}

//die($_SERVER['DOCUMENT_ROOT'] . $uploadDir);
//Define the directory to store the PDF Preview Image
$thumbDirectory = '/pdfimage/' . $_SESSION['user_customer_id'] . '/';
if (!is_dir($_SERVER['DOCUMENT_ROOT'] . $thumbDirectory)) {
	mkdir($_SERVER['DOCUMENT_ROOT'] . $thumbDirectory, 0755, true);
}
// Set the allowed file extensions
$fileTypes = array('jpg', 'jpeg', 'gif', 'png', 'pdf', 'fdf', 'rtf', 'txt', 'csv', 'mp3', 'doc', 'docx', 'mp3', 'wma','wav'); // Allowed file extensions

$verifyToken = md5('ikase_system' . $_POST['timestamp']);
//die($_POST['token'] ."==". $verifyToken);
// && $_POST['token'] == $verifyToken
if (!empty($_FILES)) {
	$tempFile   = $_FILES['Filedata']['tmp_name'];
	// Facing D:\ikase.orgD:\uploads\1033\testing.pdf file issue during upload so removed root folder path
	// $uploadDir  = $_SERVER['DOCUMENT_ROOT'] . $uploadDir;
	$uploadDir  = $uploadDir;
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
	
	$saveFileName = $targetFile;
	
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
		// if($_SERVER['REMOTE_ADDR'] == "103.238.107.229") {
		// 	die($tempFile);
		// }
		$fileTmpNm = file_get_contents($tempFile);
		if(move_uploaded_file($tempFile, $targetFile))
		{
			//Google Drive Implementation
			$accessToken = $_COOKIE['g_access_token'];
			//$saveFileName = str_replace("-", "_", $saveFileName);
			
			if(isset($accessToken) && $accessToken != 'Authorize') {
				
				$fileIkase = checkFileExist($accessToken, "name='iKase'");
				$ikaseFolderId = $fileIkase['files'][0]['id'];
				
				if(isset($ikaseFolderId) && !empty($ikaseFolderId)){
					$ikaseParentId = $ikaseFolderId;
				}else{
					$qParam = "{\"name\": \"iKase\", \"mimeType\": \"application/vnd.google-apps.folder\"}\r\n";
					$createIkase = createDriveFolder($accessToken, $qParam);
					$ikaseParentId = $createIkase['id'];
				}
				
				$fileIkaseCaseId = checkFileExist($accessToken, "name='upload_unassigned' and '".$ikaseParentId."' in parents");
				$ikaseCaseFolderId = $fileIkaseCaseId['files'][0]['id'];
				
				if(isset($ikaseCaseFolderId) && !empty($ikaseCaseFolderId)){
					$ikaseCaseParentId = $ikaseCaseFolderId;
				}else{
					$qParam = "{'name':'upload_unassigned','mimeType':'application/vnd.google-apps.folder','parents':['".$ikaseParentId."']}\r\n";
					$createIkaseCase = createDriveFolder($accessToken, $qParam);
					$ikaseCaseParentId = $createIkaseCase['id'];
				}
				
				uploadFileGDrive($fileTmpNm, $saveFileName, $ikaseCaseParentId, $accessToken);
			}
			
			
		}else{
			print_r($tempFile);
			//print_r($targetFile);
			die('fail');
		}
		
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
				$thumbnail_path = str_replace($case_id. DC, $case_id. "\\medium\\", $thumbnail_path);
				//die($thumbnail_path);
			}
			$thumbnail_path = str_replace("d:\\pdfimage\\", "d:\\ikase.org\\pdfimage\\", $thumbnail_path);
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
				$thumbnail_path = str_replace($case_id. DC, $case_id. "\\thumbnail\\", $thumbnail_path);
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

//Google Drive Implementation 2021-09-30 12:30 PM
function createDriveFolder($accessToken, $qParam){
	$ch = curl_init();
	$options = [
		CURLOPT_URL =>  "https://www.googleapis.com/drive/v3/files",
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_POST => true,
		CURLOPT_HTTPHEADER => [
			'Authorization:Bearer ' . $accessToken,
			'Accept: application/json',
			'Content-Type: application/json',
		],
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_POSTFIELDS => $qParam,
		CURLOPT_SSL_VERIFYHOST => 0,
	];
	
	curl_setopt_array($ch, $options);
	$result = curl_exec($ch);
	curl_close ($ch);
	
	$resultJ = json_decode($result, true);
	return $resultJ;
}


function checkFileExist($accessToken, $qParam){
	$ch = curl_init();
	$qParam = urlencode($qParam);
	$options = [
		CURLOPT_URL =>  "https://www.googleapis.com/drive/v3/files?q=".$qParam,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_HTTPHEADER => [
			'Authorization:Bearer ' . $accessToken,
			'Accept: application/json',
			'Content-Type: application/json',
		],
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_SSL_VERIFYHOST => 0,
	];
	curl_setopt_array($ch, $options);
	$result = curl_exec($ch);
	curl_close ($ch);
	
	$resultJ = json_decode($result, true);
	return $resultJ;
}

function uploadFileGDrive($uploaded_file, $saveFileName, $ikaseCaseParentId, $accessToken){
	$fileTmpNm = $uploaded_file;
	$boundary = "xxxxxxxxxx";
	$data = "--" . $boundary . "\r\n";
	$data .= "Content-Type: application/json; charset=UTF-8\r\n\r\n";
	$data .= "{'name':'" .$saveFileName. "','parents':['".$ikaseCaseParentId."']}\r\n";
	$data .= "--" . $boundary . "\r\n";
	$data .= "Content-Transfer-Encoding: base64\r\n\r\n";
	$data .= base64_encode($fileTmpNm);
	$data .= "\r\n--" . $boundary . "--";
			
	$ch = curl_init();
	$options = [
		CURLOPT_URL =>  'https://www.googleapis.com/upload/drive/v3/files?uploadType=multipart',
		CURLOPT_POST => true,
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_POSTFIELDS => $data,
		CURLOPT_HTTPHEADER => [
			'Authorization:Bearer ' . $accessToken,
			'Accept: application/json',
			'Content-Type:multipart/related; boundary=' . $boundary
		],
		CURLOPT_SSL_VERIFYPEER => false,
		CURLOPT_SSL_VERIFYHOST => 0,
	];
	
	curl_setopt_array($ch, $options);
	$result = curl_exec($ch);
	curl_close ($ch);
}
// #END# Google Drive Implementation 2021-09-30 12:30 PM