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
	//die(print_r($customer));
	$data_source = $customer->data_source;
	
	$sql = "SELECT cli . * , SUBSTR( dob, 7, 4 ) year,  SUBSTR( dob, 1, 2 ) month ,  
	SUBSTR( dob, 4, 2 ) day,
	CONCAT( SUBSTR( dob, 7, 4 ) ,  '-', SUBSTR( dob, 1, 2 ) ,  '-', SUBSTR( dob, 4, 2 ) ) dob
	FROM  `" . $data_source . "`.`client` cli
	INNER JOIN `" . $data_source . "`.`contacts` con ON cli.cpointer = con.cpointer
	WHERE SUBSTR( dob, 1, 2 ) !=  ''
	AND 1
	ORDER BY cli.cpointer";
	
	//WHERE cli.fileno = 1061
	//AND con.cpointer = 201304
	echo $sql . "\r\n\r\n";
	//die();

	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	$injuries = $stmt->fetchAll(PDO::FETCH_OBJ);
	foreach($injuries as $key=>$injury){
		if ($key==0) {
		//	continue;
		}
		echo "Processing -> " . $key. " == " . $injury->cpointer . "<br>";
		
		//die(print_r($injury));
		//look up the person uuid
		$sql_person = "SELECT cse.case_uuid, cp.person_uuid
		FROM  `" . $data_source . "`.`" . $data_source . "_case` cse,  
		`" . $data_source . "`.`" . $data_source . "_case_person` csp,  
		`" . $data_source . "`.`" . $data_source . "_person` cp
		WHERE cp.dob = ''
		AND cse.case_uuid = csp.case_uuid
		AND csp.person_uuid = cp.person_uuid
		AND cse.cpointer =" . $injury->cpointer;
		echo $sql_person . "\r\n\r\n";
		$stmt = $db->prepare($sql_person);
		//die($sql_person);
		$stmt->execute();
		$person = $stmt->fetchObject();
		if (!is_object($person)) {
			//it might already have a dob
			continue;
		}
		
		$dob = $injury->year . "-". trim($injury->month) . "-". trim($injury->day);
		
		//echo $dob . " = " . date("Y-m-d", strtotime($dob)) . "<br />";
		$sql_applicant = "UPDATE `" . $data_source . "`.`" . $data_source . "_person`
		SET `dob` = '" . $dob . "'
		WHERE person_uuid = '" . $person->person_uuid . "'
		AND `dob` = ''";
		echo $sql_applicant . "\r\n\r\n";
		$stmt = $db->prepare($sql_applicant);  
		$stmt->execute();
		
		/*
		$sql_applicant = "UPDATE `" . $data_source . "`.`cse_person` csp, `" . $data_source . "`.`" . $data_source . "_person` dp
		SET csp.`dob` = dp.`dob`
		WHERE csp.person_uuid = dp.person_uuid
		AND csp.person_uuid = '" . $person->person_uuid . "'";
		
		$stmt = $db->prepare($sql_applicant);  
		//echo $sql_applicant . "\r\n\r\n"; 
		$stmt->execute();
		*/
		//die("done");
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
}
?>