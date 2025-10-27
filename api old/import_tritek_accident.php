<?php
include("manage_session.php");
set_time_limit(3000);
error_reporting(E_ALL);
ini_set('display_errors', '1');	
	
$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;

include("connection.php");

?>
<script language="javascript">
parent.setFeedback("accident import started");
</script>
<?php
function addToArray(&$arr, $fieldname, $value) {
	if ($value!="") {
		$arr[] = array("name"=>$fieldname . "Input", "value"=>$value);
	}
}


try {
	$db = getConnection();
	include("customer_lookup.php");
	$db = null;
	
	$sql = "SELECT mc.case_id, mc.case_uuid, mc.cpointer, accident.apointer, `client`.proppnt
	FROM `" . $data_source . "`.`" . $data_source . "_case` mc 
	INNER JOIN `" . $data_source . "`.`client`
	ON mc.cpointer = client.cpointer
	INNER JOIN `" . $data_source . "`.`accident`
	ON client.`accipoint` =  accident.apointer
	WHERE 1 
	#AND accident.apointer = '991830'
	#AND accident.apointer = '2031109'
	
	AND mc.case_id NOT IN (
		SELECT DISTINCT case_id 
		FROM `" . $data_source . "`.`" . $data_source . "_personal_injury`
		WHERE deleted = 'N'
	)
	
	LIMIT 0, 1";
	echo $sql . "<br /><br />\r\n\r\n";
	//die();
	
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$cases = $stmt->fetchAll(PDO::FETCH_OBJ);
	$stmt->closeCursor(); $stmt = null; $db = null;
	
	$arrCaseUUID =  array();
	if (count($cases)==0) {
		die("done");
	}
	foreach($cases as $key=>$case){
		//die(print_r($case));
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$row_start_time = $time;
		
		$case_id = $case->case_id;
		$cpointer = $case->cpointer;
		$apointer = $case->apointer;
		$proppnt = $case->proppnt;
		
		echo "Processing -> " . $key. " == " . $cpointer . " [" . $apointer . "]" . "<br /><br />\r\n\r\n";
		if (in_array($cpointer, $arrCaseUUID)) {
			//one time per pointer
			continue;
		} 
		$arrCaseUUID[] = $cpointer;
		
		//get the data
		$sql = "SELECT *
		FROM `" . $data_source . "`.accident
		WHERE `apointer` = '" . $apointer . "'";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$acci = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		//now for look for any vehicles
		$sql = "SELECT * 
		FROM `" . $data_source . "`.property
		WHERE ownerpoint = '" . $proppnt . "'
		AND (pmake != '' OR vehid != '' OR ownerdesc != '')
		ORDER BY recno ASC";
		
		echo $sql . "\r\n";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$props = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		$details = array();
		$rental_array = array();
		$repair_array = array();
		foreach($props as $prop) {
			//die(print_r($prop));
			$owner_type = $prop->ownertype;
			$prefix = "";
			$form = "vehicle_form";
			if ($owner_type=="D") {
				$prefix = "defendant_";
				$form = $prefix . $form;
			}
			$vehicle_array = array();
			$vehicle_array[] = array("name"=>$prefix . "makeInput", "value"=>$prop->pmake);
			$vehicle_array[] = array("name"=>$prefix . "yearInput", "value"=>$prop->pyear);
			$vehicle_array[] = array("name"=>$prefix . "modelInput", "value"=>$prop->pmodel);
			$vehicle_array[] = array("name"=>$prefix . "license_plateInput", "value"=>$prop->tags);
			$vehicle_type = "other";
			if ($prop->auto==1) {
				$vehicle_type = "car";
			}
			if ($prop->truck==1) {
				$vehicle_type = "truck";
			}
			if ($prop->bus==1) {
				$vehicle_type = "bus";
			}
			if ($prop->motorcycle==1) {
				$vehicle_type = "motorcycle";
			}
			if ($prop->bicycle==1) {
				$vehicle_type = "bike";
			}
			if ($prop->pedestrian==1) {
				$vehicle_type = "pedestrian";
			}
			$vehicle_array[] = array("name"=>$prefix . "vehicle_typeInput", "value"=>$vehicle_type);
			
			$details[] = array("form"=>$form, "data"=>$vehicle_array);
			
			if (count($repair_array)==0) {
				$repair_info = array("representing"=>"plaintiff");
				$repair_info["examined_byInput"] = $prop->estimby;
				$repair_info["requestedInput"] = $prop->estimreq;
				$repair_info["receivedInput"] = $prop->estimrcvd;
				$repair_info["amountInput"] = $prop->estimamt;
				$totalloss = "Y";
				if ($prop->totalloss==0) {
					$totalloss = "N";
				}
				$repair_info["totaledInput"] = $totalloss;
				
				$repair_info["paid_byInput"] = $prop->paidby;
				$repair_info["blue_bookInput"] = $prop->bluebook;
				$repair_info["amount_paidInput"] = $prop->amountpaid;
				$repair_info["deductibleInput"] = $prop->deductible;
				$repair_info["balanceInput"] = $prop->balance;
				
				$repair_array["plaintiff"] = $repair_info;
			}
			if (count($rental_array)==0) {
				$rental_info = array("representing"=>"plaintiff");
				//rental info
				$rentalcar = "Y";
				if ($prop->rentalcar==0) {
					$rentalcar = "N";
				}
				//$rental_array["plaintiff"][] = array("rentedInput"=>$rentalcar);
				$rental_info["rentedInput"] = $rentalcar;
				
				$rentalcomp = "Y";
				if ($prop->rentalcar==0) {
					$rentalcomp = "N";
				}
				$rental_info["completedInput"] = $rentalcomp;
				$rental_info["agencyInput"] = $prop->rentagency;
				$rental_info["paid_byInput"] = $prop->rentpdby;
				$rental_info["amount_billedInput"] = $prop->rentbill;
				$rental_info["rental_paymentInput"] = $prop->rentpdamt;
				
				$rental_array["plaintiff"] = $rental_info;
			}
		}
		$rental_data = "";
		if (count($rental_array) > 0) {
			$rental_data = json_encode($rental_array);
		}
		$repair_data = "";
		if (count($repair_array) > 0) {
			$repair_data = json_encode($repair_array);
		}
		
		$acci_dateandtime = date("Y-m-d", strtotime($acci->accidate));
		$acci->accitime = str_replace(" ", "", $acci->accitime);
		
		if (strlen($acci->accitime) == 5) {
			$acci_dateandtime .= " " . $acci->accitime . ":00";
		} else {
			$acci_dateandtime .= " 00:00:00";
		}
		
		$statute_limitation = date('Y-m-d', strtotime('+2 years', strtotime($acci_dateandtime)));
		$acci_address_1 = $acci->acciadd1;
		if ($acci_address_1!="") {
			if ($acci->accicity!="") {
				$acci_address_1 .= "," . $acci->accicity;
			}
			if ($acci->accist!="") {
				$acci_address_1 .= "," . $acci->accist;
			}
			if ($acci->accizip!="") {
				$acci_address_1 .= " " . $acci->accizip;
			}
		}
		$acci_address_2 = $acci->acciadd2;
		if ($acci_address_2!="") {
			if ($acci->accicity!="") {
				$acci_address_2 .= "," . $acci->accicity;
			}
			if ($acci->accist!="") {
				$acci_address_2 .= "," . $acci->accist;
			}
			if ($acci->accizip!="") {
				$acci_address_2 .= " " . $acci->accizip;
			}
		}
		//need to put together a json with all the data
		$info = array();
		$info[] = array("name"=>"personal_injury_dateInput", "value"=>date("m/d/Y G:ia", strtotime($acci_dateandtime)));
		$info[] = array("name"=>"statute_limitationInput", "value"=>date("m/d/Y", strtotime($statute_limitation)));
		$info[] = array("name"=>"statute_intervalInput", "value"=>"2");
		$info[] = array("name"=>"personal_injury_dayInput", "value"=>date("N", strtotime($acci_dateandtime)));
		$info[] = array("name"=>"personal_injury_timeInput", "value"=>date("G:ia", strtotime($acci_dateandtime)));
		$info[] = array("name"=>"personal_injury_locationInput", "value"=>$acci_address_1);
		$info[] = array("name"=>"personal_injury2_locationInput", "value"=>$acci_address_2);
		$info[] = array("name"=>"personal_injury_accident_descriptionInput", "value"=>$acci->accidesc);
		$info[] = array("name"=>"personal_injury_other_detailsInput", "value"=>$acci->otherdet);
		
		addToArray($info, "premises_condition", $acci->conditions);
		addToArray($info, "controls", $acci->controls);
		addToArray($info, "weather", $acci->weather);
		addToArray($info, "nature", $acci->nature);
		addToArray($info, "county", $acci->county);
		
		//car accident?
		//just store it all
		//"form":"personal_injury_other_form"
		//$other_form = array("form"=>"personal_injury_other_form");
		$other_array = array();
		$other_array[] = array("name"=>"client_streetInput", "value"=>$acci->clstreet);
		$other_array[] = array("name"=>"client_directionInput", "value"=>$acci->cldirect);
		$other_array[] = array("name"=>"client_speedInput", "value"=>$acci->clspeed);
		$other_array[] = array("name"=>"client_laneInput", "value"=>$acci->cllane);
		
		$other_array[] = array("name"=>"defendant_streetInput", "value"=>$acci->dstreet);
		$other_array[] = array("name"=>"defendant_directionInput", "value"=>$acci->ddirect);
		$other_array[] = array("name"=>"defendant_speedInput", "value"=>$acci->dspeed);
		$other_array[] = array("name"=>"defendant_laneInput", "value"=>$acci->dlane);
		
		$details[] = array("form"=>"personal_injury_other_form", "data"=>$other_array);
		
		$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_personal_injury` 
		(`personal_injury_uuid`, `case_id`, `personal_injury_date`, `statute_limitation`, `statute_interval`, `personal_injury_description`, `personal_injury_other_details`, 
		`personal_injury_info`, `personal_injury_details`, 
		`rental_info`, `repair_info`,
		`customer_id`)
		VALUES ('" . $acci->apointer . "', '" . $case_id . "', '" . $acci_dateandtime . "', '" . $statute_limitation . "', '2', 
		'" . addslashes($acci->accidesc) . "','" . addslashes($acci->otherdet) . "', 
		'" . addslashes(json_encode($info)) . "', '" . addslashes(json_encode($details)) . "',
		'" . addslashes($rental_data) . "','" . addslashes($repair_data) . "',
		'" . $customer_id . "')";
		
		echo $sql . "<br /><br />\r\n\r\n";
		//die();
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$stmt = null; $db = null;
		
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $row_start_time), 4);
		echo " => Time spent:" . $total_time . "<br />
<br />
";
	}
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	//$success = array("success"=> array("text"=>"done", "duration"=>$total_time));
	//echo json_encode($success);
	//completeds
	$sql = "SELECT COUNT(`apointer`) `case_count`
	FROM `" . $data_source . "`.`accident` gcase
	WHERE 1";
	echo $sql . "\r\n<br>";
	/*
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$cases = $stmt->fetchObject();
	$stmt->closeCursor(); $stmt = null; $db = null;
	
	$case_count = $cases->case_count;
	*/
	$case_count = 5543;
	//completeds
	$sql = "SELECT COUNT(DISTINCT case_id) case_count
	FROM `" . $data_source . "`.`" . $data_source . "_personal_injury` ggc
	WHERE 1";
	echo $sql . "\r\n<br>";
	//die();
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->execute();
	$cases = $stmt->fetchObject();
	$stmt->closeCursor(); $stmt = null; $db = null;
	
	$completed_count = $cases->case_count;
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	echo "Time spent:" . $total_time . "<br />
<br />
";

	$success = array("success"=> array("text"=>"done", "duration"=>$total_time, "completed_count"=>$completed_count, "case_count"=>$case_count));
	
	//print_r($success);
	
	if (count($cases) > 0) {
		//die("script language='javascript'>parent.runMain(" . $completed_count . "," . $case_count . ")</script");
		echo "<script language='javascript'>parent.runAccidents(" . $completed_count . "," . $case_count . ")</script>";
	}
	
	$db = null;
} catch(PDOException $e) {
	echo $sql . "<br />";
	$error = array("error"=> array("text"=>$e->getMessage()));
	echo json_encode($error);
	die();
}	
?>