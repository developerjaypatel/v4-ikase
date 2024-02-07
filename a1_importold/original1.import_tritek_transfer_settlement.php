<?php
include("manage_session.php");

include("connection.php");

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_settlementsheet`";
	
	echo $sql . "\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	//$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_check` ";
	$sql = "DELETE FROM `ikase_" . $data_source . "`.`cse_injury_settlement`
	WHERE injury_settlement_uuid NOT LIKE 'KA%'";
	echo $sql . "\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_injury_settlement` (`injury_settlement_uuid`, `settlement_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
	SELECT `injury_settlement_uuid`, `settlement_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`
	FROM `" . $data_source . "`.`" . $data_source . "_injury_settlement` 
	WHERE 1 AND customer_id = " . $customer_id;
	
	echo $sql . "\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_settlementsheet`
	(`settlementsheet_uuid`, `date_settled`, `due`, `data`, `deleted`, `customer_id`)
	SELECT `settlementsheet_uuid`, `date_settled`, `due`, `data`, `deleted`, `customer_id`
	FROM `" . $data_source . "`.`" . $data_source . "_settlementsheet` 
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
parent.setFeedback("costs transfer completed");
</script>