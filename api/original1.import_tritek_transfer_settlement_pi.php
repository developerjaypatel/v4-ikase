<?php
include("manage_session.php");

include("connection.php");

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_injury_settlement` (`injury_settlement_uuid`, `settlement_uuid`, `injury_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
	SELECT `injury_settlement_uuid`, ev.`settlement_uuid`, ev.`injury_uuid`, ev.`attribute`, ev.`last_updated_date`, ev.`last_update_user`, ev.`deleted`, ev.`customer_id`
	FROM `" . $data_source . "`.`" . $data_source . "_injury_settlement`  ev
	INNER JOIN `ikase_" . $data_source . "`.`cse_case_injury` cce
	ON ev.injury_uuid = cce.injury_uuid
	INNER JOIN " . $data_source . ".badcases
	ON cce.case_uuid = badcases.case_uuid  
	WHERE 1";
	
	echo $sql . "\r\n\r\n";
	//$stmt = $db->prepare($sql);
	//$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_settlementsheet`
	(`settlementsheet_uuid`, `date_settled`, `due`, `data`, `deleted`, `customer_id`)
	SELECT sett.`settlementsheet_uuid`, `date_settled`, `due`, `data`, sett.`deleted`, sett.`customer_id`
	FROM `" . $data_source . "`.`" . $data_source . "_settlementsheet` sett
	INNER JOIN `ikase_" . $data_source . "`.`cse_injury_settlement` ev
	ON sett.settlementsheet_uuid = ev.settlement_uuid
	INNER JOIN `ikase_" . $data_source . "`.`cse_case_injury` cce
	ON ev.injury_uuid = cce.injury_uuid
	INNER JOIN " . $data_source . ".badcases
	ON cce.case_uuid = badcases.case_uuid  
	WHERE 1";

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
parent.setFeedback("costs transfer completed");
</script>