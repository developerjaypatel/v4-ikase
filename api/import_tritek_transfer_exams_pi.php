<?php
require_once('../shared/legacy_session.php');

include("connection.php");

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_corporation_exam`
	(`corporation_exam_uuid`, `corporation_uuid`, `exam_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
	SELECT cex.`corporation_exam_uuid`, cex.`corporation_uuid`, cex.`exam_uuid`, cex.`attribute`, cex.`last_updated_date`, cex.`last_update_user`, cex.`deleted`, cex.`customer_id`
	FROM `" . $data_source . "`.`" . $data_source . "_corporation_exam` cex
	INNER JOIN `ikase_" . $data_source . "`.`cse_case_corporation` ccc
	ON cex.corporation_uuid = ccc.corporation_uuid
	INNER JOIN " . $data_source . ".badcases
	ON ccc.case_uuid = badcases.case_uuid ";

	//$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	////$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_exam`
	(`exam_uuid`, `exam_dateandtime`, `exam_status`, `exam_type`, `specialty`, `requestor`, `comments`, `permanent_stationary`, `fs_date`, `customer_id`, `deleted`)
	SELECT exa.`exam_uuid`, `exam_dateandtime`, `exam_status`, `exam_type`, `specialty`, `requestor`, `comments`, `permanent_stationary`, `fs_date`, exa.`customer_id`, exa.`deleted`
	FROM `" . $data_source . "`.`" . $data_source . "_exam` exa
	INNER JOIN `ikase_" . $data_source . "`.`cse_corporation_exam` cex
	ON exa.exam_uuid = cex.exam_uuid
	INNER JOIN `ikase_" . $data_source . "`.`cse_case_corporation` ccc
	ON cex.corporation_uuid = ccc.corporation_uuid
	INNER JOIN " . $data_source . ".badcases
	ON ccc.case_uuid = badcases.case_uuid
	";

	//$stmt = $db->prepare($sql); 
	echo $sql . "\r\n\r\n";
	////$stmt->execute();
	
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
parent.setFeedback("exam transfer completed");
</script>
