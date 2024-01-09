<?php
include("manage_session.php");
set_time_limit(10000);
if (ob_get_level() == 0) ob_start();

include("connection.php");

$customer_id = passed_var("customer_id", "get");
if (!is_numeric($customer_id)) {
	die();
}

try {
	$db = getConnection();
	
	include("customer_lookup.php");
	
	$sql_truncate = "TRUNCATE `" . $data_source . "`.`" . $data_source . "_document`; 
	TRUNCATE `" . $data_source . "`.`" . $data_source . "_case_document`; ";
	
	echo $sql_truncate . "\r\n\r\n";
	$stmt = $db->prepare($sql_truncate);
	$stmt->execute();
	
	$sql = "SELECT DISTINCT mc.case_id, mc.case_uuid, mc.cpointer
	FROM `" . $data_source . "`.`" . $data_source . "_case` mc
	INNER JOIN `" . $data_source . "`.`doctrk1` `doc`
	ON mc.cpointer = `doc`.CASENO
	ORDER BY mc.cpointer";
	
	//echo $sql . "\r\n\r\n";
	//die($sql);
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$header_start_time = $time;

	$cases = $stmt->fetchAll(PDO::FETCH_OBJ);
	/*
	$cases = new stdClass;
	$case = new stdClass;
	$case->cpointer = 71;
	$case->case_uuid = "CASEUUID";
	$cases->case = $case;
	*/
	foreach($cases as $key=>$case){
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$row_start_time = $time;
		
		echo "<br>Processing -> " . $key. " == " . $case->cpointer . "\r\n\r\n";
		
		$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_document` (`case_document_uuid`, `case_uuid`, `document_uuid`, `attribute_1`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
		SELECT '" . $case->case_uuid . "', '" . $case->case_uuid . "', 
		CONCAT(`CASENO`,'_DOC_', `ACTNO`) AS `document_uuid`, 
		'document', `DATE` notedate, `INITIALS`, 'N', " . $customer_id . "
		FROM `" . $data_source . "`.`doctrk1`
		WHERE `CASENO` = '" . $case->cpointer . "'";
		echo $sql . "\r\n\r\n";
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_document` (document_uuid, parent_document_uuid, document_name, document_date, type, `description`, `description_html`, customer_id) 
		SELECT CONCAT(`CASENO`,'_DOC_', `ACTNO`) AS `document_uuid`, CONCAT(`CASENO`,'_DOC_', `ACTNO`) AS `parent_document_uuid`,
		`EVENT`, `DATE`, IFNULL(`GROUP`, '') `type`, '', '', '" . $customer_id . "' 
		FROM `" . $data_source . "`.`doctrk1` 
		WHERE 1
		AND `CASENO` = '" . $case->cpointer . "'";
		
		echo $sql . "\r\n\r\n";
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $row_start_time), 4);
		echo "Time spent:" . $total_time . "\r\n\r\n";

	}
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	$success = array("success"=> array("text"=>"done", "duration"=>$total_time));
	echo json_encode($success);
	
	$db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
	?>
    <script language="javascript">
parent.setFeedback("document import error");
</script>
    <?php
    die();
}

include("cls_logging.php");
?>
<script language="javascript">
parent.setFeedback("documents import completed");
</script>