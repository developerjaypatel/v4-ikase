<?php
include("manage_session.php");

include("connection.php");

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	//die();
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_medicalbilling`
	(`case_medicalbilling_uuid`, `case_uuid`, `medicalbilling_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id`)
	SELECT `case_medicalbilling_uuid`, ccn.`case_uuid`, `medicalbilling_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id` 
	FROM `" . $data_source . "`.`" . $data_source . "_case_medicalbilling`   ccn 
	INNER JOIN " . $data_source . ".badcases
	ON ccn.case_uuid = badcases.case_uuid 
	WHERE 1 ";

	echo $sql . "\r\n\r\n";
	//$stmt = $db->prepare($sql);
	//$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_medicalbilling` (
    `medicalbilling_uuid`, `corporation_uuid`, `user_uuid`, `billed`, `paid`, `adjusted`, `balance`, `finalized`, `still_treating`, `prior`, `lien`, `deleted`, `customer_id`)
	SELECT ev.`medicalbilling_uuid`, ev.`corporation_uuid`, ev.`user_uuid`, `billed`, `paid`, `adjusted`, `balance`, `finalized`, `still_treating`, `prior`, `lien`, ev.`deleted`, ev.`customer_id`
	FROM `" . $data_source . "`.`" . $data_source . "_medicalbilling`  ev
	INNER JOIN `ikase_" . $data_source . "`.`cse_case_medicalbilling` cce
	ON ev.medicalbilling_uuid = cce.medicalbilling_uuid
	INNER JOIN " . $data_source . ".badcases
	ON cce.case_uuid = badcases.case_uuid  
	WHERE 1 ";
	
	echo $sql . "\r\n\r\n";
	//$stmt = $db->prepare($sql);
	//$stmt->execute();
	
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
parent.setFeedback("medical billing transfer completed");
</script>