<?php
include("manage_session.php");

include("connection.php");

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
			
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_personal_injury`
	(`personal_injury_uuid`, `case_id`, `personal_injury_date`, `statute_limitation`, `statute_interval`, `loss_date`, `personal_injury_description`, `personal_injury_info`, `personal_injury_details`, `personal_injury_other_details`, `deleted`, `customer_id`)
	SELECT `personal_injury_uuid`, ccase.`case_id`, `personal_injury_date`, `statute_limitation`, `statute_interval`, `loss_date`, `personal_injury_description`, `personal_injury_info`, `personal_injury_details`, `personal_injury_other_details`, cpi.`deleted`, cpi.`customer_id`
	FROM `" . $data_source . "`.`" . $data_source . "_personal_injury` cpi
	
	INNER JOIN `" . $data_source . "`.`" . $data_source . "_case` cce
	ON cpi.case_id = cce.case_id
	
	INNER JOIN " . $data_source . ".badcases
	ON cce.case_uuid = badcases.case_uuid  
	
	INNER JOIN `ikase_" . $data_source . "`.`cse_case` ccase
	ON cce.case_uuid = ccase.case_uuid
	
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
parent.setFeedback("accidents transfer completed");
</script>