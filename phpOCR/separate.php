<?php
session_start();
ini_set('max_execution_time', 10*MIN);

$batchscan_id = $_POST["batchscan_id"] ?: $_GET["batchscan_id"];
if ($batchscan_id=="" || !is_numeric($batchscan_id)){
	die();
}

$uploaded = $_POST["uploaded"] ?: $_GET["uploaded"];
if ($uploaded=="") {
	die("no file");
}

$pages = $_POST["pages"] ?: $_GET["pages"];
if ($pages=="") {
	die("no pages");
}

$db = DB::conn(DB::DB_CASEUSER);
//get separators
$sql = "SELECT separators FROM cse_batchscan
WHERE customer_id = " . $_SESSION['user_customer_id'] . " AND batchscan_id = " . $batchscan_id;

try {
	$stmt = $db->query($sql);
	$stmt->execute();
	$batchscan = $stmt->fetchObject();
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	die(json_encode($error));
}

//break up into array
if ($batchscan->separators!="") {
	$arrSeparators = explode("|", $batchscan->separators);
} else {
	$arrSeparators = array();
}
//die(print_r($arrSeparators));
//get the stacks
$arrStack = array();
$document_count = -1;
for ($jnt=0;$jnt<$pages;$jnt++) {
	if (in_array($jnt, $arrSeparators)) {
		$document_count++;
		continue;
	}
	$arrStack[$document_count][] = $jnt;
}
//timestamp for file names
$timestamp = time();
//die(print_r($arrStack));
//loop to stitch stacks
for($int=0;$int<count($arrStack);$int++) {
	if (!isset($arrStack[$int])) {
		continue;
	}
	$max_page = (count($arrStack[$int]) -1);
	$new_pdf_path = "/home/cstmwb/public_html/autho/web/uploads/" .$_SESSION['user_customer_id'] . "/" .  $uploaded . "_" . $timestamp . "_" . $int . "_" . $arrStack[$int][0] . "_" . $arrStack[$int][$max_page] . ".pdf";
	echo $new_pdf_path . "<br />";
	//add the stack as a document, unattached so far
	$table_uuid = uniqid("KS", false);
	$document_name = $uploaded . "_" . $timestamp . "_" . $int . "_" . $arrStack[$int][0] . "_" . $arrStack[$int][$max_page] . ".pdf";
	$document_date = date("Y-m-d h:i:s");
	$document_extension = ".pdf";
	if ($arrStack[$int][0] != $arrStack[$int][$max_page]) {
		$description = $arrStack[$int][0] . "-" . $arrStack[$int][$max_page];
	} else {
		$description = $arrStack[$int][0];
	}
	if (strlen(trim($description))==0) {
		continue;
	}
	$description_html = $description;	//for now
	$type = "batchscan";
	$verified = "Y";
	$customer_id = $_SESSION['user_customer_id'];
	$sql = "INSERT INTO cse_document (document_uuid, parent_document_uuid, document_name, document_date, document_filename, document_extension, description, description_html, type, verified, customer_id) 
			VALUES (:document_uuid, :parent_document_uuid, :document_name, :document_date, :document_filename, :document_extension, :description, :description_html, :type, :verified, :customer_id)";
			echo "inserting " . $document_name . "<br />";
	try {
		
		$stmt = $db->prepare($sql);
		$stmt->bindParam("document_uuid", $table_uuid);
		//reason for batch uuid, use batch id for now
		$stmt->bindParam("parent_document_uuid", $batchscan_id);
		$stmt->bindParam("document_name", $document_name);
		$stmt->bindParam("document_date", $document_date);
		$stmt->bindParam("document_filename", $document_name);
		$stmt->bindParam("document_extension", $document_extension);
		$stmt->bindParam("description", $description);
		$stmt->bindParam("description_html", $description_html);
		$stmt->bindParam("type", $type);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("verified", $verified);
		$stmt->execute();
		$new_id = $db->lastInsertId();
		//die(print_r($newEmployee));
		//echo json_encode(array("id"=>$new_id)); 
	} catch(PDOException $e) {	
		die( '{"error":{"text":'. $e->getMessage() .'}}'); 
	}
	//create a list of the output for stitching
	$arrList = array();
	foreach($arrStack[$int] as $stack_item) {
		$arrList[] = "/home/cstmwb/public_html/autho/web/uploads/" . $_SESSION['user_customer_id'] . "/" . $uploaded . ".pdf[" . $stack_item . "]";
	}
	
	$document_list = implode(" ", $arrList);
	//stitch
	echo "stitching " . $new_pdf_path . "<br />";
	//exec("convert -density 150 " . $document_list . " " . $new_pdf_path);
}
$source = "<a href='../../web/uploads/" . $_SESSION['user_customer_id'] . "/" . $uploaded . ".pdf' target='_blank'>Source</a>";

/*
//clean_up
copy("/home/cstmwb/public_html/autho/web/uploads/" . $_SESSION['user_customer_id'] . "/" . $uploaded . ".pdf", "/home/cstmwb/public_html/autho/web/uploads/" . $_SESSION['user_customer_id'] . "/" . $uploaded . "_" . $timestamp . ".pdf");
unlink("/home/cstmwb/public_html/autho/web/uploads/" . $_SESSION['user_customer_id'] . "/" . $uploaded . ".pdf");
for ($jnt=0;$jnt<$pages;$jnt++) { 
	$image_path = "/home/cstmwb/public_html/autho/web/uploads/" . $_SESSION['user_customer_id'] . "/" . $uploaded . "_" . $jnt . ".png";
	$new_path = "/home/cstmwb/public_html/autho/web/uploads/" . $_SESSION['user_customer_id'] . "/" . $uploaded . "_" . $timestamp . "_" . $jnt . ".png";
	copy($image_path, $new_path);
	unlink($image_path);
}
*/

//update stacked status
$sql = "UPDATE cse_batchscan
SET stacked = 'Y', time_stamp = '" . $timestamp . "'
WHERE customer_id = " . $_SESSION['user_customer_id'] . " AND batchscan_id = " . $batchscan_id;

try {	
	$stmt = DB::run($sql);
} catch(PDOException $e) {
    die(json_encode(["error" => ["text" => $e->getMessage()]]));
}

echo json_encode(["success" => "Y", "source" => $source, "stacks" => count($arrStack), "timestamp" => $timestamp]);
