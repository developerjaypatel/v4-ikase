<?php
require_once('../shared/legacy_session.php');

include("connection.php");

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	//die();
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_corporation_financial`
	(`corporation_financial_uuid`, `corporation_uuid`, `financial_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id`)
	SELECT `corporation_financial_uuid`, cex.`corporation_uuid`, `financial_uuid`, cex.`attribute`, cex.`last_updated_date`, 
	cex.`last_update_user`, cex.`deleted`, cex.`customer_id` 
	FROM `" . $data_source . "`.`" . $data_source . "_corporation_financial`  cex
	INNER JOIN `ikase_" . $data_source . "`.`cse_case_corporation` ccc
	ON cex.corporation_uuid = ccc.corporation_uuid
	INNER JOIN " . $data_source . ".badcases
	ON ccc.case_uuid = badcases.case_uuid 
	WHERE 1";

	echo $sql . "\r\n\r\n";
	//$stmt = $db->prepare($sql);
	//$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_financial` (
    `financial_uuid`, `financial_info`, `case_id`, `deleted`, `customer_id`, `financial_defendant`)
	SELECT exa.`financial_uuid`, `financial_info`, exa.`case_id`, exa.`deleted`, exa.`customer_id`, exa.`financial_defendant`
	FROM `" . $data_source . "`.`" . $data_source . "_financial`  exa
	INNER JOIN `ikase_" . $data_source . "`.`cse_corporation_financial` cex
	ON exa.financial_uuid = cex.financial_uuid
	INNER JOIN `ikase_" . $data_source . "`.`cse_case_corporation` ccc
	ON cex.corporation_uuid = ccc.corporation_uuid
	INNER JOIN " . $data_source . ".badcases
	ON ccc.case_uuid = badcases.case_uuid
	WHERE 1 ";
	
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
parent.setFeedback("financials transfer completed");
</script>
