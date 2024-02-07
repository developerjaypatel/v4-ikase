<?php
$fp = fopen($trackname, "a+");
fwrite($fp, "importing:" . $current . " - " . date("m/d/Y H:i:s"));
fclose($fp);

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;

$filename = $arrFileInfo[6];
$thumb_dir = $timestamp;

//add it as a batchscan via api
$url = $subscriber_url . "batchscan/batchscan_add.php";
$fields = array('filename'=>$filename, 'customer_id'=>$customer_id, 'user_id'=>$user_id, 'by'=>$user_name);
$fields_string = "";

//change the filename without extension
$filename = $uploaded;

foreach($fields as $key=>$value) { 
	$fields_string .= $key.'='.$value.'&'; 
}
rtrim($fields_string, '&');
//die($fields_string);
//open connection
$ch = curl_init();
//echo $url . "?" . $fields_string . "\r\n";

//set the url, number of POST vars, POST data
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HEADER, false); 
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
curl_setopt($ch, CURLOPT_COOKIEFILE, "cookies.txt");
curl_setopt($ch, CURLOPT_POST, count($fields_string));
curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);

$result = curl_exec($ch);
//die($result);
curl_close($ch);
$json_scan = json_decode($result);
$batchscan_id = $json_scan->id;
$batchscan_ip = $json_scan->ip;

$fp = fopen($trackname, "a+");
if (!is_numeric($batchscan_id) || $batchscan_id=="") {
	fwrite($fp, $result . "\r\n\r\nNo batchscan id -- " . date("m/d/Y H:i:s") . "\r\n");\
	fclose($fp);
	die();
} else {
	fwrite($fp, $batchscan_id . " -- " . $batchscan_ip . " on " . date("m/d/Y H:i:s") . "\r\n");
	fclose($fp);
}

//make these dynamic as well
$ftp_server = "52.34.166.217";
$ftp_username = "nick";
$ftp_pwd = "admin527#";

//connect to ftp
$conn_id = ftp_connect($ftp_server); 

// login with username and password 
$login_result = ftp_login($conn_id, $ftp_username, $ftp_pwd); 
if (!$login_result) {
	exit('FTP Login Failed\r\n');
} else {
	ftp_pasv($conn_id,true);
	echo "FTP login succeeded\r\n";
}

//go to the correct folder
ftp_chdir($conn_id, "batchscan");
ftp_chdir($conn_id, "scans");

//make sure import folder exists
$nList = ftp_nlist($conn_id, ".");
$blnCustomerFound = false;
foreach($nList as $listed) {
	$clean_file = str_replace("./", "", $listed);	
	//echo $clean_file . "\r\n";
	if ($clean_file==$customer_id) {
		$blnCustomerFound = true;
		break;
	}
} 

if (!$blnCustomerFound) {
	$dir = $customer_id;
	if (ftp_mkdir($conn_id, $dir)) {
		//echo "successfully created $dir\n";
	} else {
		die("There was a problem while creating $dir\n");
	}
}

ftp_chdir($conn_id, $customer_id);

//move thumbails over for each import
$dir = $thumb_dir;
$nList = ftp_nlist($conn_id, ".");
$blnThumbsFound = false;
foreach($nList as $listed) {
	$clean_file = str_replace("./", "", $listed);	
	//echo $clean_file . "\r\n";
	if ($clean_file==$dir) {
		$blnThumbsFound = true;
		break;
	}
} 

if (!$blnThumbsFound) {
	if (ftp_mkdir($conn_id, $dir)) {
		//echo "successfully created $dir\n";
	} else {
		die("There was a problem while creating $dir\n");
	}
}
$uploadDir =  $_SERVER['DOCUMENT_ROOT'] . '\\ikase\\' . $subscriber . 'uploads\\' . $customer_id . '\\imports\\';
$imports = scandir($uploadDir);
//die(print_r($imports));

foreach($imports as $targetFile) {
	if ($targetFile=="." || $targetFile=="..") {
		continue;
	}
	$path_parts = pathinfo($uploadDir . $targetFile);
	//echo $path_parts['filename'] . " -> " . $filename . " === " . strpos($path_parts['filename'], $filename);
	//die();
	$blnContinue = true;
	if (strpos($path_parts['filename'], $filename) > -1) {
		$blnContinue = false;
	}
	if ($blnContinue) {
		continue;
	}
		
	//upload the file itself
	if (ftp_put($conn_id, $targetFile, $uploadDir . $targetFile, FTP_BINARY)) {
		//echo "successfully uploaded $file\n";
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $header_start_time), 4);
		
		//echo $targetFile . " in " . $total_time . "\r\n";
		//$arrOutput = array("scan"=>$json_scan, "filename"=>$targetFile, "total_time"=>$total_time);
		//die(json_encode($arrOutput));
	} else {
		//echo "There was a problem while uploading $file\n";
		echo json_encode(array("ftp error"=>"-1"));
		die();
	}
	
	//let's extract the first page
	$arrFileinfo = explode("_", $path_parts['filename']);
	$first_page = $arrFileinfo[count($arrFileinfo) - 2];
	$last_page = $arrFileinfo[count($arrFileinfo) - 1];
	$first_pageFile = $_SERVER['DOCUMENT_ROOT'] . '\\ikase\\' . $subscriber . 'uploads\\' . $customer_id . "\\" . $thumb_dir . "\\" . $filename . "_" . $first_page . ".png";
	
	if (!file_exists($first_pageFile)) {
		die($first_pageFile . " not exists");
		$first_pageFile = "";
		continue;
	} 
	
	//ftp_chdir($conn_id, $thumb_dir);
	//echo $thumb_dir . "/" . $filename . "_" . $first_page . ".png , " . $first_pageFile . "\r\n\r\n";

	//$targetFile
	$remote_file = $thumb_dir . "/" . $filename . "_" . $first_page . ".png";
	//die($remote_file);
	if (ftp_put($conn_id, $remote_file, $first_pageFile, FTP_BINARY)) {
		echo "successfully uploaded $remote_file\n";
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $header_start_time), 4);
		
		$description = $first_page . "_" . $last_page;
		//now add it as a document
		//add it as a batchscan via api
		//$url = "https://www.ikase.org/api/documents/remoteadd";
		$url = $subscriber_url . "batchscan/document_add.php";
		$fields = array('customer_id'=>$customer_id, 'user_id'=>$user_id, 'user_name'=>$user_name, "parent_document_uuid"=>$batchscan_id, "document_filename"=>$targetFile, "document_name"=>$targetFile, "document_date"=>date("Y-m-d H:i:s"), "document_extension"=>"pdf", "thumbnail_folder"=>$thumb_dir, "description"=>$description, "description_html"=>$description, "type"=>"batchscan", "verified"=>"Y");
		$fields_string = "";
		
		foreach($fields as $key=>$value) { 
			$fields_string .= $key.'='.$value.'&'; 
		}
		rtrim($fields_string, '&');
		
		//echo $url . "?" . $fields_string . "\r\n";
		//open connection
		$ch = curl_init();
		
		//set the url, number of POST vars, POST data
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, false); 
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_COOKIEFILE, "cookies.txt");
		curl_setopt($ch, CURLOPT_POST, count($fields_string));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
		
		$result = curl_exec($ch);
		//echo $result . "\r\n";
		
		curl_close($ch);
		echo $targetFile . " in " . $total_time . "\r\n";
	} else {
		//echo "There was a problem while uploading $file\n";
		echo json_encode(array("ftp error"=>"-1", "file"=>$file));
		die();
	}
}

ftp_close($conn_id);

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$finish_time = $time;
$total_time = round(($finish_time - $header_start_time), 4);

echo "\r\Imports Processed in " . $total_time . "\r\n";
?>