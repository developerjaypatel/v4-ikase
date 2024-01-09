<?php
include("connection.php");

$params = passed_var("id", "get");
$arrParams = explode("a", $params);
$customer_id = $arrParams[0] / 3;
$activity_id = $arrParams[1] / $customer_id;

if (!is_numeric($customer_id)) {
	die("no no here");
}
if (!is_numeric($activity_id)) {
	die("no no there");
}

$sql = "SELECT data_source 
	FROM ikase.cse_customer
	WHERE customer_id = :customer_id";
	//die($sql);
try {
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("customer_id", $customer_id);
	
	$stmt->execute();
	$customer = $stmt->fetchObject();

	$data_source = $customer->data_source;
	if ($data_source=="") {
		$data_source = "ikase";
	} else {
		$data_source = "ikase_" . $data_source;
	}
	//return json_encode($queue);
	
	$sql = "SELECT activity
	FROM `" . $data_source . "`.`cse_activity` act
	WHERE activity_id = :activity_id
	AND customer_id = :customer_id";
	
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("activity_id", $activity_id);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->execute();
	$activity = $stmt->fetchObject();
	
	if (is_object($activity)) {
		$arrAct = explode("\r\n", $activity->activity);
		$arrFile = explode(".php?", $arrAct[2]);
		$arrFinal = explode(".pdf", $arrFile[1]);
		//die(print_r($arrFinal));
		$arrParams = explode("&", $arrFinal[0]);
		$case_id = str_replace("case_id=", "", $arrParams[0]);
		$file = str_replace("file=", "", $arrParams[1]);

		$case_dir = UPLOADS_PATH.$customer_id.DC.$case_id.DC;
		$uploadDir = $case_dir . "refervocational\\";
		
		$uploadDir = "../uploads/" . $customer_id . "/" . $case_id . "/refervocational/";
		$path = $uploadDir . $file .  ".pdf";
		
		include("download.php");
	}

} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
	die();
}

die();
