<?php
require_once('../shared/legacy_session.php');
set_time_limit(3000);

$time = microtime();
$time = explode(' ', $time);
$time = $time[1] + $time[0];
$header_start_time = $time;

include("connection.php");

function getNickConnection() {
	//$dbhost="54.149.211.191";
	$dbhost="ikase.org";
	$dbuser="root";
	$dbpass="admin527#";
	$dbname="ikase";
	
	$dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);            
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbh;
}

//WHERE cli.fileno = 1061
//die($sql);
try {
	$db = getNickConnection();
	
	include("customer_lookup.php");
	
	$sql = "SELECT gcase.* 
	FROM `" . $data_source . "`.`baddeductions` gcase
	WHERE processed = 'N'
	#AND cpointer = '2066574'
	#ORDER BY baddeductions_id ASC
	LIMIT 0, 1";

	$cases = DB::select($sql);
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
		//echo " => QUERY completed in " . $total_time . "<br /><br />";
	//die(print_r($cases));
	$found = count($cases);
	
	foreach($cases as $case_key=>$case){
		$case_uuid = $case->case_uuid;
		echo "Processing " . $case->cpointer . "\r\n<br />";
		
		$sql_deduction = "SELECT * FROM goldberg2.specials
		WHERE specpnt = '" . $case->cpointer . "'
		ORDER BY `specials`.`date` ASC";
		
		$stmt = $db->prepare($sql_deduction);
		//echo $sql_deduction . "\r\n\r\n<br><br>";  
		//die();
		$stmt = $db->query($sql_deduction);
		$deductions = $stmt->fetchAll(PDO::FETCH_OBJ);
		//die(print_r($deductions));			
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish_time = $time;
		$total_time = round(($finish_time - $header_start_time), 4);
		//echo " => QUERY completed in " . $total_time . "<br /><br />";
		
		$last_updated_date = date("Y-m-d H:i:s");
		$case_uuid = $case->case_uuid;
		
		foreach($deductions as $deduction) {	
			$deduction_uuid = uniqid("DE", false);
			$deductiondate = $deduction->date;
			if ($deductiondate=="") {
				$deductiondate = "0000-00-00";
			} else {
				$deductiondate = date("Y-m-d");
				if (date("Y", strtotime($deductiondate)) < 1970) {
					$deductiondate = "0000-00-00";
				}
			}
			
			$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_deduction`
(`deduction_uuid`, `deduction_date`, `tracking_number`, `deduction_description`, `amount`, `payment`, `adjustment`, `balance`, `customer_id`)
VALUES( '" . $deduction_uuid . "', '" . $deductiondate . "', '" . addslashes($deduction->checkno) . "', '" . addslashes($deduction->descriptio) . "', '" . $deduction->amount . "', '" . $deduction->payment . "', '" . $deduction->adjustment . "', '" . $deduction->balance . "', " . $customer_id . ");";
			$stmt = $db->prepare($sql); 
			echo $sql . "\r\n\r\n<br><br>";  
			//die();
			$stmt->execute();
						
			$time = microtime();
			$time = explode(' ', $time);
			$time = $time[1] + $time[0];
			$finish_time = $time;
			$total_time = round(($finish_time - $header_start_time), 4);
			//echo " => first insert completed in " . $total_time . "<br /><br />";
			
			$case_table_uuid = uniqid("CD", false);
			$last_updated_date = date("Y-m-d H:i:s");
			//now we have to attach the doctor to the case 
			$sql = "INSERT INTO `" . $data_source . "`.`" . $data_source . "_case_deduction` (`case_deduction_uuid`, `case_uuid`, `deduction_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_table_uuid  ."', '" . $case_uuid ."', '" . $deduction_uuid . "', 'main', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
					
			$stmt = $db->prepare($sql); 
			//echo $sql . "\r\n\r\n<br><br>";  
			$stmt->execute();
			
			$time = microtime();
			$time = explode(' ', $time);
			$time = $time[1] + $time[0];
			$finish_time = $time;
			$total_time = round(($finish_time - $header_start_time), 4);
			//echo " => 2nd query completed in " . $total_time . "<br /><br />";
		}
	}
	$db = getNickConnection();
	
	$sql = "UPDATE `" . $data_source . "`.`baddeductions` 
	SET processed = 'Y'
	WHERE cpointer = '" . $case->cpointer . "'";
	echo $sql . "\r\n\r\n<br><br>";
	$stmt = DB::run($sql);
	//die("done");
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	//$success = array("success"=> array("text"=>"done", "duration"=>$total_time));
	//completeds
	$sql = "SELECT COUNT(*) case_count
	FROM `" . $data_source . "`.`baddeductions` gcase
	WHERE 1";
	//echo $sql . "\r\n<br>";
	//die();
	$stmt = DB::run($sql);
	$cases = $stmt->fetchObject();
	
	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	echo " => QUERY completed in " . $total_time . "<br /><br />"; 
	
	$case_count = $cases->case_count;
	
	//completeds
	$sql = "SELECT COUNT(cpointer) case_count
	FROM `" . $data_source . "`.`baddeductions` ggc
	WHERE processed = 'Y'";
	//echo $sql . "\r\n<br>";
	//die();
	$stmt = DB::run($sql);
	$cases = $stmt->fetchObject();
	
	$completed_count = $cases->case_count;

	$time = microtime();
	$time = explode(' ', $time);
	$time = $time[1] + $time[0];
	$finish_time = $time;
	$total_time = round(($finish_time - $header_start_time), 4);
	
	$success = array("success"=> array("text"=>"done", "duration"=>$total_time, "completed_count"=>$completed_count, "case_count"=>$case_count));
	
	echo $total_time . "<br />";
	//echo json_encode($success);
	if ($total_time > 5) {
		//die("too long");
	}
	if (count($cases) > 0) {
		//die("script language='javascript'>parent.runMain(" . $completed_count . "," . $case_count . ")</script");
		echo "<script language='javascript'>parent.runDeductions(" . $completed_count . "," . $case_count . ")</script>";
	}
} catch(PDOException $e) {
	$error = array("error"=> array("text"=>$e->getMessage(), "sql"=>$sql));
	echo json_encode($error);
}

//include("cls_logging.php");

