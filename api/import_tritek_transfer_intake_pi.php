<?php
require_once('../shared/legacy_session.php');

include("connection.php");

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_claim`
	(case_uuid, claim_info, deleted, customer_id)
	SELECT ca.case_uuid, claim_info, deleted, customer_id
	FROM `" . $data_source . "`." . $data_source . "_claim ca
	INNER JOIN " . $data_source . ".badcases
	ON ca.case_uuid = badcases.case_uuid
	WHERE 1";
	
	echo $sql . "\r\n\r\n";
	//$stmt = $db->prepare($sql);
	//$stmt->execute();
	
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
parent.setFeedback("intake transfer completed");
</script>
