<?php
require_once('../shared/legacy_session.php');

ini_set('max_execution_time', 10*MIN);

include_once("header.php");
include_once("../api/connection.php");

$db = getConnection();

if (isset($_POST["batchscan_id"])) {
	$batchscan_id = passed_var("batchscan_id", "post");
	$customer_id = passed_var("customer_id", "post");
	$uploaded = passed_var("uploaded", "post");
} else {
	$batchscan_id = passed_var("batchscan_id", "get");
	$customer_id = passed_var("customer_id", "get");
	$uploaded = passed_var("uploaded", "get");
}

$msg = "save started: " . $batchscan_id . "
batchscan_id:" . $batchscan_id;
include("../api/cls_logging.php");
	

$arrList = array();
$arrList[] = UPLOADS_PATH . $customer_id . DC . $uploaded . ".pdf";

$document_list = implode(" ", $arrList);
//die(print_r($arrList));

$new_pdf_path = UPLOADS_PATH . $customer_id . "\\imports\\" . $uploaded . ".pdf";
//die($new_pdf_path);
$msg = "new_pdf_path: " . $new_pdf_path;
$log->lwrite($msg);

//$arrStitchList[] =  $arrStackInfo[2] . "~" . $arrStackInfo[3];

try {
	$sql = "UPDATE cse_batchscan
	SET stitched = 'unassigned'
	WHERE customer_id = " . $customer_id . " AND batchscan_id = " . $batchscan_id;
	
	$msg = $sql;
	$log->lwrite($msg);
	
	$stmt = DB::run($sql);

	//add the stack as a document, unattached so far
	$table_uuid = uniqid("KS", false);
	$notification_uuid = uniqid("KN", false);
	$document_name = $uploaded . ".pdf";
	$msg = "docname :" . $document_name  . " -- " . $batchscan_id;
	$log->lwrite($msg);
	//echo $document_name . "<br />";
	$document_date = date("Y-m-d H:i:s");
	$document_extension = ".pdf";
	$thumbnail_folder = "pdfimage/" . $customer_id;
	$description = "unassigned document uploaded on " . date("m/d/Y H:i:s");
	$description_html = $description;	//for now
	$type = "unassigned";
	$verified = "Y";
	
	$sql = "INSERT INTO cse_document (document_uuid, parent_document_uuid, document_name, document_date, document_filename, document_extension, thumbnail_folder, description, description_html, type, verified, customer_id) 
			VALUES (:document_uuid, :parent_document_uuid, :document_name, :document_date, :document_filename, :document_extension, :thumbnail_folder, :description, :description_html, :type, :verified, :customer_id)";
			
	$stmt = $db->prepare($sql);  
	$stmt->bindParam("document_uuid", $table_uuid);
	//reason for batch uuid, use batch id for now
	$stmt->bindParam("parent_document_uuid", $batchscan_id);
	$stmt->bindParam("document_name", $document_name);
	$stmt->bindParam("document_date", $document_date);
	$stmt->bindParam("document_filename", $document_name);
	$stmt->bindParam("document_extension", $document_extension);
	$stmt->bindParam("thumbnail_folder", $thumbnail_folder);
	$stmt->bindParam("description", $description);
	$stmt->bindParam("description_html", $description_html);
	$stmt->bindParam("type", $type);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->bindParam("verified", $verified);
	$stmt->execute();
	$new_id = $db->lastInsertId();
	
	//notification to the uploader
	$sql = "INSERT INTO cse_notification (`document_uuid`, `notification_uuid`, `user_uuid`, `notification`, `notification_date`, `customer_id`)
	VALUES ('" . $table_uuid . "', '" . $notification_uuid . "', '" . $_SESSION["user_id"] . "','review', '" . date("Y-m-d H:i:s") . "', '" . $_SESSION["user_customer_id"] . "')";
	
	$stmt = DB::run($sql);
	//die(print_r($newEmployee));
	
	//now track the document so we know who uploaded it
	$operation = "insert";
	$sql = "INSERT INTO cse_document_track (`user_uuid`, `user_logon`, `operation`, `document_id`, `document_uuid`, `parent_document_uuid`, `document_name`, `document_date`, `document_filename`, `document_extension`, `thumbnail_folder`, `description`, `description_html`, `type`, `verified`, `deleted`, `customer_id`)
	SELECT '" . $_SESSION['user_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $operation . "', `document_id`, `document_uuid`, `parent_document_uuid`, `document_name`, `document_date`, `document_filename`, `document_extension`, `thumbnail_folder`, `description`, `description_html`, `type`, `verified`, `deleted`, `customer_id`
	FROM cse_document
	WHERE document_id = " . $new_id;
	$stmt = DB::run($sql);
	
	echo json_encode(array("id"=>$new_id)); 
} catch(PDOException $e) {	
	die( '{"error":{"text":'. $e->getMessage() .'}}'); 
}

//exec("convert -density 150 " . $document_list . " " . $new_pdf_path);

$msg = $batchscan_id . "
convert -density 150 " . $document_list . " " . $new_pdf_path;
$log->lwrite($msg);

$msg = "stitch completed " . $document_list . " " . $batchscan_id;
$log->lwrite($msg);

// close log file
$log->lclose();
?>
