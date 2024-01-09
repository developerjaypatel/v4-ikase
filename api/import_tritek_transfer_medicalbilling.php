<?php
require_once('../shared/legacy_session.php');

include("connection.php");

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	/*
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_case_medicalbilling`";
	
	echo $sql . "\r\n\r\n";
	$stmt = DB::run($sql);
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_medicalbilling` ";

	echo $sql . "\r\n\r\n";
	$stmt = DB::run($sql);
	*/
	
	//die();
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_case_medicalbilling`
	(`case_medicalbilling_uuid`, `case_uuid`, `medicalbilling_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id`)
	SELECT `case_medicalbilling_uuid`, `case_uuid`, `medicalbilling_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id` 
	FROM `" . $data_source . "`.`" . $data_source . "_case_medicalbilling` 
	WHERE 1 AND customer_id = " . $customer_id;

	echo $sql . "\r\n\r\n";
	$stmt = DB::run($sql);
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_medicalbilling` (
    `medicalbilling_uuid`, `corporation_uuid`, `user_uuid`, `bill_date`, `billed`, `paid`, `adjusted`, `balance`, `finalized`, `still_treating`, `prior`, `lien`, `deleted`, `customer_id`)
	SELECT `medicalbilling_uuid`, `corporation_uuid`, `user_uuid`, `bill_date`, `billed`, `paid`, `adjusted`, `balance`, `finalized`, `still_treating`, `prior`, `lien`, `deleted`, `customer_id`
	FROM `" . $data_source . "`.`" . $data_source . "_medicalbilling` 
	WHERE 1 AND customer_id = " . $customer_id;
	
	echo $sql . "\r\n\r\n";
	$stmt = DB::run($sql);
	
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
