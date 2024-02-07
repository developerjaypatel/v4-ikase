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
	
	echo $sql . "\r\n\r\n";
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	//get the partytype, add it to the partietype table if not in there
	$sql = "SELECT DISTINCT `partie_type` `type`
	FROM `" . $data_source . "`.`" . $data_source . "_partie_type`
	WHERE 1
	ORDER BY partie_type";
	//cpointer = 2021
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$partytypes = $stmt->fetchAll(PDO::FETCH_OBJ);
	$arrTritekType = array();
	$sort_order = 99;
	foreach($partytypes as $key=>$partytype){
		$arrTritekType[] = $partytype->type;
	}
	//get the partytype, add it to the partietype table if not in there
	$sql = "SELECT DISTINCT `partytype` `type` 
	FROM `" . $data_source . "`.`parties`
	WHERE 1
	ORDER BY partytype";
	//cpointer = 2021
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$partytypes = $stmt->fetchAll(PDO::FETCH_OBJ);
	$sort_order = 99;
	foreach($partytypes as $key=>$partytype){
		$blurb = "";
		$show_employee = "N";
		$employee_title = "";
		$adhoc = "";
		$blnContinue = false;
		$partytype_name = $partytype->type;
		//echo "part:" . $partytype_name . "\r\n";
		//132A
		if (strpos($partytype->type, "132")!==false) {
			$partytype->type = "132A";
			$show_employee = "Y";
			$employee_title = "Attorney";
			$adhoc = "eams_ref_number,tax_id,bar_number";
		}
		if (strpos($partytype->type, "reporting")!==false) {
			$partytype->type = "Court Reporter";
			$employee_title = "Reporter";
		}
		if (strpos($partytype->type, "DWC")!==false) {
			$partytype->type = "DWC";
			$partytype_name = $partytype->type;
		}
		if (strpos($partytype->type, "Interpret")!==false) {
			$partytype->type = "Interpreter";
			$employee_title = "Interpreter";
		}
		if (strpos($partytype->type, "Prior")!==false) {
			$partytype->type = "Prior Attorney";
			$employee_title = "Attorney";
			//skip adding already in defaults
			$blnContinue = true;
		}
		if (strpos(strtolower($partytype->type), "new app")!==false ) {
			$partytype->type = "Applicant Attorney";
			$employee_title = "Attorney";
			//skip adding already in defaults
			$blnContinue = true;
		}
		if (strpos($partytype->type, "New opp")!==false) {
			$partytype->type = "Defense Attorney";
			$blurb = "defense";
			//skip adding already in defaults
			$blnContinue = true;
		}
		if (strpos(strtolower($partytype->type), "sub")!==false) {
			$partytype->type = "Prior Attorney";
			//skip adding already in defaults
			$blnContinue = true;
		}
		
		//All upper case
		if ($partytype_name == strtoupper($partytype_name)) {
			
			//leave room for initials
			if (strlen($partytype_name) > 4) {
				$partytype->type = strtolower($partytype_name);
				$partytype->type = ucwords($partytype->type);
				
				//echo $partytype_name." ==> ".$partytype->type. "\r\n\r\n";
			}
		}
		
		if ($blurb == "") {
			//blurb
			$blurb = strtolower($partytype->type);
			$blurb = str_replace("'", "", $blurb);
			$blurb = str_replace(" ", "_", $blurb);
			$blurb = str_replace("&", "", $blurb);
			$blurb = str_replace("__", "_", $blurb);
		}

		$sort_order++;
		if (!in_array($partytype->type, $arrTritekType)) {
			$arrTritekType[] = $partytype->type;
			
			if (!$blnContinue){
				//insert it
				$sql = "
				INSERT INTO `" . $data_source . "`.`" . $data_source . "_partie_type`
				(`partie_type`, `employee_title`, `blurb`, `color`, `show_employee`, `adhoc_fields`, 
				`sort_order`)
				VALUES('" . addslashes($partytype->type) . "', '" . addslashes($employee_title) . "', '" . $blurb . "', '_card_fade', '" . $show_employee . "', '" . $adhoc . "', " . $sort_order . ")";
				echo $sql . "\r\n" . "\r\n";
				$stmt = $db->prepare($sql);
				$stmt->execute();
				
				//clear out entries		
				$sql = "DELETE FROM `" . $data_source . "`.`" . $data_source . "_corporation`
				WHERE `type` = '" . $blurb . "'
				AND customer_id = " . $customer_id;;
				
				echo $sql . "\r\n\r\n";
				$stmt = $db->prepare($sql);
				$stmt->execute();
				
				//clear out entries
				$sql = "DELETE FROM `" . $data_source . "`.`" . $data_source . "_case_corporation`
				WHERE `attribute` = '" . $blurb . "'
				AND customer_id = " . $customer_id;;
				
				echo $sql . "\r\n\r\n";
				$stmt = $db->prepare($sql);
				$stmt->execute();
			}
		}
		//i dont remember why below is here
		
		//now loop through the cases for the partytype 
		$sql = "SELECT DISTINCT mc.case_id, mc.case_uuid, mc.cpointer,
		`db`.`partytype`, `db`.`firm`, `db`.`add1`, `db`.`add2`, `db`.`city`, `db`.`state`, `db`.`zip`, `db`.`tel`, `db`.`telext`, `db`.`fax`, `db`.`email`, `db`.`contact`, `db`.`contactlas`, `db`.`contactfir`, `db`.`contactmid`, `db`.`salutation`, `db`.`comments`, `db`.`partypnt`, `db`.`taxid`, `db`.`recno`, `db`.`visible`, `db`.`visundo`, `db`.`searchkey`
		FROM `" . $data_source . "`.`" . $data_source . "_case` mc
		INNER JOIN `" . $data_source . "`.`parties`
		ON mc.cpointer = `parties`.`cpointer`
		INNER JOIN `" . $data_source . "`.`partydb` `db`
		ON parties.partypnt = db.partypnt
		WHERE parties.partytype = '" . addslashes($partytype_name) . "'
		
		ORDER BY mc.cpointer ASC";
		//AND mc.cpointer = 2021
		$stmt = $db->prepare($sql);
		echo $sql . "\r\n\r\n";

		$stmt->execute();
		
		$cases = $stmt->fetchAll(PDO::FETCH_OBJ);
		//die(print_r($cases));
		foreach($cases as $key=>$case){
			$last_updated_date = date("Y-m-d H:i:s");
			//get the pointer
			echo "<br>Processing -> " . $key. " == " . $case->cpointer . "\r\n\r\n";
			
			$case_uuid = $case->case_uuid;
			$time = microtime();
			$time = explode(' ', $time);
			$time = $time[1] + $time[0];
			$row_start_time = $time;
			
			$corporation_uuid = uniqid("KS", false);
			$parent_table_uuid = uniqid("RD", false);
			
			$full_address = $case->add1;
			if ($case->add2!="") {
				$full_address .= ", " . $case->add2;
			}
			$full_address .= ", " . $case->city;
			$full_address .= ", " . $case->state;
			$full_address .= " " . $case->zip;
			
			$arrSet = array();
			$arrSet[] = addslashes($case->contactfir . " " . $case->contactlas);
			$arrSet[] = addslashes($case->contactfir);
			$arrSet[] = addslashes($case->contactlas);
			$arrSet[] = addslashes($case->firm);
			$arrSet[] = addslashes($blurb);
			$arrSet[] = addslashes($full_address);
			$arrSet[] = addslashes($case->add1);
			$arrSet[] = addslashes($case->city);
			$arrSet[] = $case->state;
			$arrSet[] = $case->zip;
			$arrSet[] = addslashes($case->add2);
			if ($case->telext=="") {
				$arrSet[] = addslashes($case->tel);
			} else {
				$arrSet[] = addslashes($case->tel . " " . $case->telext);
			}
			$arrSet[] = addslashes($case->email);
			$arrSet[] = $case->fax;
			$arrSet[] = addslashes($case->salutation);
			
			//insert the parent record first
			$sql_partie = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation` (`corporation_uuid`, `customer_id`, `full_name`, `first_name`, `last_name`, `company_name`, `type`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `email`, `fax`, `salutation`, `last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`) 
					VALUES('" . $parent_table_uuid . "', '" . $customer_id . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N', '" . $parent_table_uuid . "', '')";
					
			$stmt = $db->prepare($sql_partie);  
			echo $sql_partie . "\r\n\r\n"; 
			$stmt->execute();
			
			$sql_partie = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_corporation` (`corporation_uuid`, `customer_id`, `full_name`, `first_name`, `last_name`, `company_name`, `type`, `full_address`, `street`, `city`, `state`, `zip`, `suite`, `phone`, `email`, `fax`, `salutation`, `last_updated_date`, `last_update_user`, `deleted`, `parent_corporation_uuid`, `copying_instructions`)  
					VALUES('" . $corporation_uuid . "', '" . $customer_id . "', '" . implode("','", $arrSet) . "', '" . $last_updated_date . "', 'system', 'N', '" . $parent_table_uuid . "', '')";
					
			$stmt = $db->prepare($sql_partie); 
			echo $sql_partie . "\r\n\r\n";  
			$stmt->execute();
			
			$case_table_uuid = uniqid("KA", false);
			$attribute_1 = "main";
			//now we have to attach the employer to the case 
			$sql_partie = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_corporation` 
			(`case_corporation_uuid`, `case_uuid`, `corporation_uuid`, `attribute`, 
			`last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $corporation_uuid . "', '" . addslashes($blurb) . "', 
			'" . $last_updated_date . "', 'system', '" . $customer_id . "')";
					
			$stmt = $db->prepare($sql_partie);
			echo $sql_partie . "\r\n\r\n";   
			$stmt->execute();
			
			$time = microtime();
			$time = explode(' ', $time);
			$time = $time[1] + $time[0];
			$finish_time = $time;
			$total_time = round(($finish_time - $row_start_time), 4);
			echo "Time spent:" . $total_time . "\r\n\r\n";
		}
	}
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $row_start_time), 4);
	echo "Time spent:" . $total_time . "\r\n\r\n";
	
	$success = array("success"=> array("text"=>"done", "duration"=>$total_time));
	echo json_encode($success);
	
	$db = null;
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
}

include("cls_logging.php");
?>
<script language="javascript">
parent.setFeedback("partie types import completed");
</script>