<?php
include("manage_session.php");

include("connection.php");

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_case_document`";
	
	echo $sql . "\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_document`";


	echo $sql . "\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_document`
	(`case_document_uuid`, `case_uuid`, `document_uuid`, `attribute_1`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id`)
	SELECT `case_document_uuid`, `case_uuid`, `document_uuid`, `attribute_1`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id` 
	FROM `" . $data_source . "`.`" . $data_source . "_case_document` 
	WHERE 1 AND customer_id = " . $customer_id;

	echo $sql . "\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_document` (`document_uuid`, `parent_document_uuid`, `document_name`, `document_date`, `document_filename`, `document_extension`, `thumbnail_folder`, `description`, `description_html`, `source`, `received_date`, `type`, `verified`, `customer_id`, `deleted`)
	SELECT `document_uuid`, `parent_document_uuid`, `document_name`, `document_date`, `document_filename`, `document_extension`, `thumbnail_folder`, `description`, `description_html`, `source`, `received_date`, `type`, `verified`, `customer_id`, `deleted`
	FROM `" . $data_source . "`.`" . $data_source . "_document` 
	WHERE 1 AND customer_id = " . $customer_id;
	
	echo $sql . "\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$db = null;
	
	$success = array("success"=> array("text"=>"done @" . date("H:i:s")));
	echo json_encode($success);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}	
//, `notedesc`, , `notepoint`, `notelock`, `lockedby`, `xsoundex`, `locator`, `enteredby`, `mailpoint`, `lockloc`, `docpointer`, `doctype`
include("cls_logging.php");
?>
<script language="javascript">
parent.setFeedback("documents transfer completed");
</script>