<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

include("manage_session.php");
set_time_limit(3000);

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;

include("connection.php");
$customer_id = passed_var("customer_id", "get");
if (!is_numeric($customer_id)) {
	die();
}

try {
	$db = getConnection();
	
	include("customer_lookup.php");
	
	$sql_truncate = "TRUNCATE `" . $data_source . "`.`" . $data_source . "_partie_type`";
	$stmt = $db->prepare($sql_truncate);
	$stmt->execute();
	//die($sql_truncate);
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$row_start_time = $time;
	
		
	$sql = "
	INSERT INTO `" . $data_source . "`.`" . $data_source . "_partie_type`
	(`partie_type_id`, `partie_type`, `employee_title`, `blurb`, `color`, `show_employee`, `adhoc_fields`, 
	`sort_order`)
	SELECT `partie_type_id`, `partie_type`, `employee_title`, `blurb`, `color`, `show_employee`, 
	`adhoc_fields`, `sort_order`
	FROM `ikase`.`cse_partie_type`;";
	
	//echo $sql . "\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$stmt = null; $db = null;
	
	//now add all the a1 partie types as well, not already blurbed in our database
	$sql = "SELECT cpt.blurb, ctypes.ctype, ctypes.type_name
	FROM 
	 (
		SELECT DISTINCT REPLACE(TRIM(`type`), ' ', '_') ctype, TRIM(`type`) type_name
		FROM " . $data_source . "." . $data_source . "_corporation corp
		WHERE `type` != ''
		ORDER BY TRIM(`type`)
	) ctypes
	LEFT OUTER JOIN `" . $data_source . "`.`" . $data_source . "_partie_type` cpt
	ON ctypes.ctype = cpt.blurb
	WHERE blurb IS NULL";
	
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$types = $stmt->fetchAll(PDO::FETCH_OBJ);
	$stmt->closeCursor(); $stmt = null; $db = null;
	//die(print_r($types));
	foreach($types as $tindex =>$type) {
		
		$color = "_card_fade";
		if ($tindex > 0) {
			$color .= "_" . ($tindex + 1);
		}
		$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_partie_type`
		(`partie_type`, `employee_title`, `blurb`, `color`, `sort_order`)
		VALUES ('" . addslashes(ucwords($type->type_name)) . "', '', '" . $type->ctype . "', '" . $color . "', 60)";
		//die($sql);
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$stmt = null; $db = null;
	}
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $row_start_time), 4);
	//echo "Time spent:" . $total_time . "\r\n\r\n";
	
	$success = array("success"=> array("text"=>"done", "duration"=>$total_time));
	echo json_encode($success);
	
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}

include("cls_logging.php");
?>
<script language="javascript">
parent.setFeedback("partie types import completed");
</script>