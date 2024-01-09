<?php
include("manage_session.php");

include("connection.php");

try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_partie_type`";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_setting`";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_partie_type`
	(`partie_type_id`, `partie_type`, `employee_title`, `blurb`, `color`, `show_employee`, `adhoc_fields`, `sort_order`)
	SELECT `partie_type_id`, `partie_type`, `employee_title`, `blurb`, `color`, `show_employee`, `adhoc_fields`, `sort_order`
	FROM `" . $data_source . "`.`" . $data_source . "_partie_type`;";

	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_setting`
	(`setting_uuid`, `customer_id`, `category`, `setting`, `setting_value`, `setting_type`, `default_value`)
	SELECT `setting_uuid`, `customer_id`, `category`, `setting`, `setting_value`, `setting_type`, `default_value`
	FROM `" . $data_source . "`.`" . $data_source . "_setting`;";

	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$db = null;
	
	$success = array("success"=> array("text"=>"done @" . date("H:i:s")));
	echo json_encode($success);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}	
include("cls_logging.php");
?>
<script language="javascript">
parent.setFeedback("partie types transfer completed");
</script>