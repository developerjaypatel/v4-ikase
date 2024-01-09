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
	
	
	$sql = "TRUNCATE `ikase_" . $data_source . "`.`cse_eams_forms`";
	echo $sql . "\r\n";
	$stmt = DB::run($sql);
	
	$sql = "INSERT INTO `ikase_" . $data_source . "`.`cse_eams_forms`
	SELECT * FROM `ikase`.`cse_eams_forms` WHERE deleted = 'N'
	AND name != ''
    AND (customer_id = 0 OR customer_id = 1033)";
	//die($sql);
	echo $sql . "\r\n";
	$stmt = DB::run($sql);
	
	$success = array("success"=> array("text"=>"done"));
	echo json_encode($success);
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}	

include("cls_logging.php");
?>
<script language="javascript">
parent.setFeedback("eams forms transfer completed");
</script>
