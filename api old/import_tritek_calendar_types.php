<?php
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
	
	//lookup the customer name
	$sql_customer = "SELECT data_source
	FROM  `ikase`.`cse_customer` 
	WHERE customer_id = :customer_id";
	
	$stmt = $db->prepare($sql_customer);
	$stmt->bindParam("customer_id", $customer_id);
	$stmt->execute();
	$customer = $stmt->fetchObject();
	$data_source = $customer->data_source;
	
	
	$sql_truncate = "TRUNCATE `" . $data_source . "`.`" . $data_source . "_setting`";
	$stmt = $db->prepare($sql_truncate);
	$stmt->execute();
	//die($sql_truncate);
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$row_start_time = $time;
	
		
	$sql = "
	INSERT INTO `" . $data_source . "`.`" . $data_source . "_setting`
	(`setting_uuid`, `customer_id`, `category`, `setting`, `setting_value`, `setting_type`, `default_value`)
	SELECT `setting_uuid`, '" . $customer_id . "', `category`, `setting`, `setting_value`, `setting_type`, `default_value`
	FROM `ikase`.`cse_setting`
	WHERE (category = 'calendar_type' OR category = 'delay')
	AND customer_id = 1033 ;";
	
	echo $sql . "\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	
	//get the caltype, add it to the partietype table if not in there
	$sql = "SELECT DISTINCT LOWER(`evdesc`) `type` 
	FROM `" . $data_source . "`.`events` ev
	LEFT OUTER JOIN `" . $data_source . "`.evcode evc ON ev.evcode = evc.evcode 
	WHERE 1
	AND evdesc != ''
	AND evdesc IS NOT NULL
	AND evdesc NOT IN (SELECT `setting_value`
	FROM `ikase`.`cse_setting`
	WHERE category = 'calendar_type'
	AND customer_id = 1033 )
	ORDER BY  LOWER(`evdesc`)";
	echo $sql . "\r\n\r\n";
	//cpointer = 2021
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	
	$caltypes = $stmt->fetchAll(PDO::FETCH_OBJ);
	//die(print_r($caltypes));
	$arrTritekType = array();
	$sort_order = 99;
	foreach($caltypes as $key=>$caltype){
		$blurb = "";
		$show_employee = "N";
		$employee_title = "";
		$adhoc = "";
		$blnContinue = false;
		$caltype_name = $caltype->type;
						
		//leave room for initials
		if (strlen($caltype_name) > 4) {
			$caltype->type = strtolower($caltype_name);
			$caltype->type = ucwords($caltype->type);
			
			//echo $caltype_name." ==> ".$caltype->type. "\r\n\r\n";
		}
		

		$caltype->type = str_replace("Wcab", "WCAB", $caltype->type);
		$caltype->type = str_replace("Msc", "MSC", $caltype->type);
		$caltype->type = str_replace("Aoe/coe", "AOE/COE", $caltype->type);
		$caltype_name = $caltype->type;
		$arrCat = explode(" ", $caltype_name);
		$category = $arrCat[0];
		$sort_order++;
		if (!in_array($caltype->type, $arrTritekType)) {
			$arrTritekType[] = $caltype->type;
			
			if (!$blnContinue){
				//insert it
				$calendar_uuid = uniqid("KS", false);
				$sql = "
				INSERT INTO `" . $data_source . "`.`" . $data_source . "_setting`
				(`setting_uuid`, `customer_id`, `category`, `setting`, `setting_value`, `setting_type`, `default_value`)
				VALUES('" . $calendar_uuid  . "', '" . $customer_id . "','calendar_type', '" . $caltype->type . "', '" . $category . "', '', '')";
				echo $sql . "\r\n" . "\r\n";
				
				$stmt = $db->prepare($sql);
				$stmt->execute();
				
			}
		}
	}
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $row_start_time), 4);
	echo "Time spent:" . $total_time . "<br />
<br />
";
	
	$success = array("success"=> array("text"=>"done", "duration"=>$total_time));
	echo json_encode($success);
	
	$db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}
?>
<script language="javascript">
parent.setFeedback("calendar types import completed");
</script>