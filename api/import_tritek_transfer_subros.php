<?php
require_once('../shared/legacy_session.php');

include("connection.php");

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_corporation_financial`";
	
	echo $sql . "\r\n\r\n";
	$stmt = DB::run($sql);
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_financial` ";

	echo $sql . "\r\n\r\n";
	$stmt = DB::run($sql);
	//die();
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_corporation_financial`
	(`corporation_financial_uuid`, `corporation_uuid`, `financial_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id`)
	SELECT `corporation_financial_uuid`, `corporation_uuid`, `financial_uuid`, `attribute`, `last_updated_date`, 
	`last_update_user`, `deleted`, `customer_id` 
	FROM `" . $data_source . "`.`" . $data_source . "_corporation_financial` 
	WHERE 1 AND customer_id = " . $customer_id;

	echo $sql . "\r\n\r\n";
	$stmt = DB::run($sql);
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_financial` (
    `financial_uuid`, `financial_info`, `case_id`, `deleted`, `customer_id`, `financial_defendant`)
	SELECT `financial_uuid`, `financial_info`, `case_id`, `deleted`, `customer_id`, `financial_defendant`
	FROM `" . $data_source . "`.`" . $data_source . "_financial` 
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
parent.setFeedback("financials transfer completed");
</script>
