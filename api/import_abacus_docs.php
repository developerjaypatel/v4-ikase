<?php
require_once('../shared/legacy_session.php');

include("connection.php");

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_case_document` ";
	$stmt = DB::run($sql);
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_document` ";
	$stmt = DB::run($sql);
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_document` 
(`document_uuid`, `parent_document_uuid`, `document_name`, `document_date`, `document_filename`, `document_extension`, `thumbnail_folder`, `description`, `description_html`, `source`, `received_date`, `type`, `verified`, `deleted`, `customer_id`)
	SELECT CONCAT(ID, U_ID) `documents_uuid`, CONCAT(ID, U_ID) `parent_document_uuid`, `DOC_NAME` `document_name`, `WHEN` `document_date`, 
	`FULL_PATH` `document_filename`, '', '', '', '', '', `WHEN` `received_date`, 'DOC' `type`, 'Y' `verified`,
	'N' `deleted`, 
	'" . $customer_id . "' `customer_id` 
	FROM `" . $data_source . "`.`lawdocs` 
	WHERE 1
	AND CASENUM IS NOT NULL";
	//die($sql);
	$stmt = DB::run($sql);
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_document`
	(`case_document_uuid`, `case_uuid`, `document_uuid`, `attribute_1`, `attribute_2`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id`)
	SELECT law3.U_ID, law3.CASENUM, CONCAT(lnot.ID, lnot.U_ID), 'main' `attribute_1`, '' `attribute_2`, '" . date("Y-m-d H:i:s") . "' last_updated_date, 
	'system' last_update_user, 
	'N' deleted, '" . $customer_id . "' `customer_id`
	FROM `" . $data_source . "`.lawdocs lnot
	INNER JOIN `" . $data_source . "`.law3 
	ON lnot.CASENUM = law3.CASENUM
	AND lnot.CASENUM IS NOT NULL";

	$stmt = DB::run($sql);
	
	$success = array("success"=> array("text"=>"done @" . date("H:i:s")));
	echo json_encode($success);
} catch(PDOException $e) {
	echo $e->getMessage() . "\r\n";
	die($sql);
	$error = array("error"=> array("text"=>$e->getMessage())>$e->getMessage());
	echo json_encode($error);
}	
?>
<script language="javascript">
parent.setFeedback("documents transfer completed");
</script>
