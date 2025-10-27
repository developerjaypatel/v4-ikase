<?php
header('Content-type: application/vnd.fdf');
error_reporting(E_ALL);
ini_set('display_errors', '1');
include ("connection.php");

foreach($_REQUEST as $index=>$req) {
	//echo $index . " = " . $req . "\n";
	$index = cleanWord($index);
	$arrReqData[$index] = $req;
}
$filename = $_SERVER['DOCUMENT_ROOT'] . "\\chats\\-1\\cf_data.html";
$jdata = json_encode($arrReqData);
$fp = fopen($filename, 'w');
fwrite($fp, $jdata);
fclose($fp);

$arrOutput = array();
$customer_id = -1;
$case_id = -1;
$form_name = "_";
$original_filename = "";
$document_path = "";
foreach($arrReqData as $index=>$req) {
	$index = cleanWord($index);
	$req = cleanWord($req);
	
	if ($index=="customer_id") {
		$customer_id = $req;
	}
	if ($index=="case_id") {
		$case_id = $req;
	}
	if ($index=="form_name") {
		$form_name = $req;
	}
	if ($index=="filename") {
		$original_filename = $req;
	}
	if ($index=="document_path") {
		$document_path = $req;
	}
	$row = $index . "=" . $req;
	$arrOutput[$index] = $req;
}
$response = json_encode($arrOutput);

$sql = "
INSERT INTO ikase.cse_fdf_responses (response, pdftk, form_name, document_path, customer_id, case_id) 
VALUES (:response, :pdftk, :form_name, :document_path, :customer_id, :case_id)";
$pdftk = "";
$response_id = "";
try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("response", $response);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->bindParam("case_id", $case_id);
	$stmt->bindParam("form_name", $form_name);
	$stmt->bindParam("document_path", $document_path);
	$stmt->bindParam("pdftk", $pdftk);
	$stmt->execute();
	
	$response_id = $db->lastInsertId();
	$db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
		echo json_encode($error);
}

//open the appropriate FDF
$somecontent = file_get_contents("../eams_forms/" . $form_name . ".fdf");
//break up by \r\n
$arrLines = explode("\r\n", $somecontent);
$arrReplace = array();
foreach($arrOutput as $field_name=>$field_value) {
	$field_name = str_replace("_", " ", $field_name);
	foreach($arrLines as $line) {
		if (strpos($line, "<</V")==0) {
			//look for fieldname /T(Attorneys name)>>
			if (strpos($line, "/T(" . $field_name . ")>>") !== false) {
				//extract placeholder
				//<</V($ASSIGNEDATTORNEY$)/T(Attorneys name)>>
				$start = 5;
				$end = strpos($line, ")/T(");
				$placeholder = substr($line, $start, ($end - $start));
				//it might be a compound placeholder
				$arrCompound = explode(" ", $placeholder);
				
				if (count($arrCompound)==1) {
					$placeholder = str_replace("$", "", $placeholder);
					pdfReplacement($placeholder, $field_value, $somecontent, $arrReplace);
				} else {
					//spanish cleanup
					$string = $somecontent;
					$string = str_replace("&ntilde;", "N", $string);
					$string = str_replace("ñ", "N", $string);
					
					$string = str_replace("&Ntilde;;", "N", $string);
					$string = str_replace("Ñ", "N", $string);
					
					$replacement = trim($field_value);
					$somecontent = str_replace($placeholder, $replacement, $string);
					
					$arrReplace[$placeholder] = $replacement;
				}
			}
		}
	}
}
pdfReplacement("CUSTOMERID", $customer_id, $somecontent, $arrReplace);
pdfReplacement("CASEID", $case_id, $somecontent, $arrReplace);
pdfReplacement("FORMNAME", $form_name, $somecontent, $arrReplace);
pdfReplacement("DOCUMENTPATH", $document_path, $somecontent, $arrReplace);

$host = $_SERVER['HTTP_HOST'];
pdfReplacement("DESTINATION", "http://" . $host . "/eams_forms/", $somecontent, $arrReplace);
	
//print_r($arrReplace);

$destination_folder = "D:/uploads/" . $customer_id . "/" . $case_id . "/eams_forms/";
$filename = $destination_folder . $form_name . ".fdf";
$filename_output =  $destination_folder . $document_path;
$source_dir = $_SERVER['DOCUMENT_ROOT'] . '\\eams_forms\\';

if (file_exists($filename)) {
	unlink($filename);
}
if (!$handle = fopen($filename, 'w')) {
	 echo "Cannot open file ($filename)";
	 exit;
}

// Write $somecontent to our opened file.
if (fwrite($handle, $somecontent) === FALSE) {
   echo "Cannot write to file ($filename)";
   exit;
}

$filename = "D:\\uploads\\" . $customer_id . "\\" . $case_id . "\\eams_forms\\" . $form_name . ".fdf";
$pdftk_output =  "D:\\uploads\\" . $customer_id . "\\" . $case_id . "\\eams_forms\\" . $original_filename;

$pdftk = "pdftk " . $source_dir . $form_name . ".pdf fill_form " . $filename. " output " . $pdftk_output;

//echo $pdftk;
exec($pdftk);

$sql = "
UPDATE ikase.cse_fdf_responses 
SET pdftk = :pdftk
WHERE response_id = :response_id";
try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("response_id", $response_id);
	$stmt->bindParam("pdftk", $pdftk);
	$stmt->execute();
	$db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
?>