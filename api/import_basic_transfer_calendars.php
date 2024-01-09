<?php
require_once('../shared/legacy_session.php');

include("connection.php");
$customer_id = passed_var("customer_id", "get");
if (!is_numeric($customer_id)) {
	die();
}
try {
	$db = getConnection();
	
	//lookup the customer name
	include("customer_lookup.php");
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_calendar` ";
	echo $sql . "\r\n\r\n";
	$stmt = DB::run($sql);
	
	
	//FIRST two letters
	$first_two = substr($data_source, 0, 2);

	$sql_calendar = "INSERT INTO `ikase_" . $data_source . "`.`cse_calendar`
	(`calendar_uuid`, `calendar`, `sort_order`, `customer_id`, `mandatory`, `active`)
	SELECT CONCAT('" . $first_two . "', SUBSTRING(`calendar_uuid`, 3)), `calendar`, `sort_order`, 
	'" . $customer_id . "', `mandatory`, `active`
	FROM `ikase`.`cse_calendar`
	WHERE `customer_id` = 1033
	ORDER BY `sort_order`";
	echo $sql_calendar . "<br /><br />\r\n\r\n";
	$stmt = DB::run($sql_calendar);
	
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
parent.setFeedback("calendars transfer completed");
</script>
