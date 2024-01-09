<?php
require_once('../shared/legacy_session.php');

include("connection.php");

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_negotiation`
	(`case_negotiation_uuid`, `case_uuid`, `negotiation_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id`)
	SELECT `case_negotiation_uuid`, ccn.`case_uuid`, `negotiation_uuid`, `attribute`, ccn.`last_updated_date`, 
	ccn.`last_update_user`, ccn.`deleted`, ccn.`customer_id` 
	FROM `" . $data_source . "`.`" . $data_source . "_case_negotiation`  ccn 
	INNER JOIN " . $data_source . ".badcases
	ON ccn.case_uuid = badcases.case_uuid
	WHERE 1";

	echo $sql . "\r\n\r\n";
	//$stmt = $db->prepare($sql);
	//$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_negotiation` (`negotiation_uuid`, `negotiation_date`, `negotiator`, `firm`, `worker`, `negotiation_type`, `amount`, `comments`, `deleted`, `customer_id`)
	SELECT ev.`negotiation_uuid`, `negotiation_date`, `negotiator`, `firm`, `worker`, `negotiation_type`, `amount`, `comments`, ev.`deleted`, ev.`customer_id` 
	FROM `" . $data_source . "`.`" . $data_source . "_negotiation`  ev
	INNER JOIN `ikase_" . $data_source . "`.`cse_case_negotiation` cce
	ON ev.negotiation_uuid = cce.negotiation_uuid
	INNER JOIN " . $data_source . ".badcases
	ON cce.case_uuid = badcases.case_uuid 
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
parent.setFeedback("negotiation transfer completed");
</script>
