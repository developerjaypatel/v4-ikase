<?php
ini_set('max_execution_time', 600); //300 seconds = 5 minutes

include_once("header.php");
include_once("functions.php");

$db = getConnection();

if (isset($_POST["batchscan_id"])) {
	$stacks = $_POST["stacks"];
	$batchscan_id = $_POST["batchscan_id"];
	$document_list = $_POST["document_list"];
	$customer_id = $_POST["customer_id"];
	$user_id = $_POST["user_id"];
	$uploaded = $_POST["uploaded"];
} else {
	$stacks = $_GET["stacks"];
	$batchscan_id = $_GET["batchscan_id"];
	$document_list = $_GET["document_list"];
	$customer_id = $_GET["customer_id"];
	$user_id = $_GET["user_id"];
	$uploaded = $_GET["uploaded"];
}

include("../api/customer_lookup.php");

if ($data_source != "") {
	$data_source = "`ikase_" . $data_source . "`";
} else {
	$data_source = "`ikase`";
}

$msg = "stitch started: " . $document_list . "
stacks:" . $stacks . "
batchscan_id:" . $batchscan_id;
include("../api/cls_logging.php");
	
//break it all up
$arrStackInfo = explode("|", $stacks);
//0 = timestamp, 1 stack_number, 2 = first_page, 3 = last_page
$thumbnail_folder = $arrStackInfo[0];

$arrListInfo = explode("|", $document_list);
$arrList = array();
foreach($arrListInfo as $stack_item) {
	//$arrList[] = "/home/cstmwb/public_html/autho/web/uploads/" . $customer_id . "/" . $uploaded . ".pdf[" . $stack_item . "]";
	$arrList[] = $_SERVER['DOCUMENT_ROOT'] . "\\uploads\\" . $customer_id . "\\" . $uploaded . ".pdf[" . $stack_item . "]";
}
$document_list = implode(" ", $arrList);

//$new_pdf_path = "/home/cstmwb/public_html/autho/web/uploads/" .$customer_id . "/" .  $uploaded . "_" . ($arrStackInfo[2]+1) . "_" . ($arrStackInfo[3]+1) . ".pdf";
$new_pdf_path = $_SERVER['DOCUMENT_ROOT'] . "\\uploads\\" . $customer_id . "\\imports\\" . $uploaded . "_" . ($arrStackInfo[2]+1) . "_" . ($arrStackInfo[3]+1) . ".pdf";

$msg = "new_pdf_path: " . $new_pdf_path;
$log->lwrite($msg);

exec("convert -density 150 " . $document_list . " " . $new_pdf_path);

$arrStitchList[] =  $arrStackInfo[2] . "~" . $arrStackInfo[3];
//get stitched
$sql = "SELECT stitched 
FROM " . $data_source . ".cse_batchscan
WHERE customer_id = " . $customer_id . " AND batchscan_id = " . $batchscan_id;

try {
	$stmt = $db->query($sql);
	$stmt->execute();
	$stitched = $stmt->fetchObject();
	
	$msg = "stitched found " . $batchscan_id;
	$log->lwrite($msg);
		
	//break up into array
	if ($stitched->stitched!="") {
		$arrStitch = explode("|", $stitched->stitched);
	} else {
		$arrStitch = array();
	}
	if (count($arrStitch) > 0) {
		//add to array
		$arrStitchList = array_merge($arrStitchList, $arrStitch);
		$arrStitchList = array_filter($arrStitchList, "noEmpty");
		sort($arrStitchList);
		$arrStitchList = array_unique($arrStitchList);
	}
	
	//update stitched
	$sql = "UPDATE " . $data_source . ".cse_batchscan
	SET stitched = '" . implode("|", $arrStitchList) . "'
	WHERE customer_id = " . $customer_id . " AND batchscan_id = " . $batchscan_id;
	
	$msg = $sql;
	$log->lwrite($msg);
	
	$stmt = $db->prepare($sql);
	$stmt->execute();

	//add the stack as a document, unattached so far
	$table_uuid = uniqid("KS", false);
	$notification_uuid = uniqid("KN", false);
	$document_name = $uploaded . "_" . ($arrStackInfo[2]+1) . "_" . ($arrStackInfo[3]+1) . ".pdf";
	$msg = "docname :" . $document_name  . " -- " . $batchscan_id;
	$log->lwrite($msg);
	//echo $document_name . "<br />";
	$document_date = date("Y-m-d H:i:s");
	$document_extension = ".pdf";
	if ($arrStackInfo[2] != $arrStackInfo[3]) {
		$description = ($arrStackInfo[2]+1) . "-" . ($arrStackInfo[3]+1);
	} else {
		$description = ($arrStackInfo[2]+1);
	}
	if (strlen(trim($description))==0) {
		die();
	}
	$description_html = $description;	//for now
	$type = "batchscan";
	$verified = "Y";
	
	$sql = "INSERT INTO " . $data_source . ".cse_document (document_uuid, parent_document_uuid, document_name, document_date, document_filename, document_extension, thumbnail_folder, description, description_html, type, verified, customer_id) 
			VALUES (:document_uuid, :parent_document_uuid, :document_name, :document_date, :document_filename, :document_extension, :thumbnail_folder, :description, :description_html, :type, :verified, :customer_id)";
			
	//echo "inserting " . $document_name . "<br />";
	
	$msg = "inserting " . $document_name  . " -- " . $batchscan_id;
	$log->lwrite($msg);

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
	
	$sql = "INSERT INTO " . $data_source . ".cse_notification (`document_uuid`, `notification_uuid`, `user_uuid`, `notification`, `notification_date`, `customer_id`)
	VALUES ('" . $table_uuid . "', '" . $notification_uuid . "', '" . $user_id . "','review', '" . date("Y-m-d H:i:s") . "', '" . $customer_id . "')";
	$msg = "notifying " . $document_name  . " -- " . $batchscan_id;
	$log->lwrite($msg);
	$log->lwrite($sql);


	$stmt = $db->prepare($sql);  
	$stmt->execute();
	//die(print_r($newEmployee));
	echo json_encode(array("id"=>$new_id)); 
} catch(PDOException $e) {	
	die( '{"error":{"text":'. $e->getMessage() .'}}'); 
}


$msg = $batchscan_id . "
convert -density 150 " . $document_list . " " . $new_pdf_path;
$log->lwrite($msg);

$msg = "stitch completed " . $document_list . " " . $batchscan_id;
$log->lwrite($msg);

// close log file
$log->lclose();
?>