<?php
include("../api/cls_logging.php"); 
include("../api/connection.php"); 
// $arrOutput = array();
// foreach($_POST as $index=>$post) {
// 	$arrOutput[] = $index . ":" . $post;
// }
//$response = implode(" | ", $arrOutput);

$response = json_encode($_POST);

$log = new Logging();
$filename = $_SERVER['DOCUMENT_ROOT'] . "/developer/api/rcslog.txt";

//die($filename);
// set path and name of log file (optional)
$log->lfile($filename);
// $log->lwrite($response);	

// the file name for the plivo event
$script_name = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME);
// echo print_r($repsonse);
// encode the $_POST in a JSON
$JSON_post = json_encode($_POST);
$ip_address = $_SERVER["REMOTE_ADDR"];
//added to log the time on ivr
include("../cls_logging.php"); 

//refresh the log file everytime
$filename = $_SERVER['DOCUMENT_ROOT'] . "/developer/api/rcslog.txt";
$fp = fopen($filename, 'w');
fwrite($fp, "");
fclose($fp);

$log = new Logging();

$log->lfile($filename);

$log->lwrite(print_r($_POST) . " in plivo folder.");
if($_POST["Text"] == "N" || $_POST["Text"] == "Unsubscribe" || $_POST["Text"] == "No" || $_POST["Text"] == "STOP"){
	$log->lwrite("got through the if in plivo folder.");
    //remove first character "1" from returned phone number
	$temp_phone = $_POST["From"];
	$phone_number = 0;
	if($temp_phone[0] == 1){
		$phone_number = substr($temp_phone, 2);
	} else {
		$phone_number = $temp_phone;
	}
	// $msg = "if condition worked";
	$sql = "UPDATE tbl_debtor
			SET deleted = 'Y',
			subscribe = 'N'
			WHERE REPLACE(REPLACE(REPLACE(REPLACE(phone, '-', ''), '(', ''), ')', ''), ' ' , '') = '" . $phone_number . "'";
		
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$log->lwrite("unsubscribed in plivo folder");
		// get the debtor_uuid (Note currently hard coded to take the first debtor)
		$sql1 = "SELECT DISTINCT `debtor_uuid` FROM tbl_debtor WHERE 1 AND REPLACE(REPLACE(REPLACE(REPLACE(phone, '-', ''), '(', ''), ')', ''), ' ' , '') = '" . $phone_number . "' AND deleted = 'N'";
		
		$stmt1 = $db->prepare($sql1);
		$stmt1->execute();
		$debtors = $stmt1->fetchAll(PDO::FETCH_OBJ);
		
		$log->lwrite("processed in plivo folder.");
		
		// create incoming uuid
		$incoming_uuid = uniqid("IN", false);
		
		// assign the value based on the boolean return
		$machine = "";
		if($_POST["Machine"] = "true") {
			$machine = "Y";
		} else {
			$machine = "N";
		}		
		// Insert into the table incoming.
		$sql2 = "INSERT INTO `tbl_incoming` (`incoming_uuid`, `batch_uuid`, `debtor_uuid`, `drip_uuid`, `drop_uuid`, `drop_number`, `request_uuid`, `file_name`, `call_uuid`, `number_called`, `machine`, `content`, `recipient_response`, `customer_id`, `ip_address`) 
	VALUES('" . $incoming_uuid . "', '" . $batch_uuid . "', '" . $debtor[0]->debtor_uuid . "', '" . $drip_uuid . "', '" . $drop_uuid . "', '" . $drop_id . "', '". $_POST["RequestUUID"] ."', ''". $script_name . "', '" . $_POST["CallUUID"] . "', '". $phone_number ."', '". $machine ."', '". $JSON_post . "', '-1', '" . $customer_id . "', '" . $ip_address . "')"; 
        
        // "INSERT INTO `tbl_incoming` (`incoming_uuid`, `debtor_uuid`, `request_uuid`, `file_name`, `call_uuid`, `number_called`, `machine`, `content`)
		// 					values('" . $incoming_uuid . "', '". $debtors[0]->debtor_uuid ."', '". $_POST["RequestUUID"] ."', '". $script_name . "', '" . $_POST["CallUUID"] . "', '". $phone_number ."', '". $machine ."', '". $JSON_post . "')";
		// $log->lwrite($sql2);
	
			
		$stmt2 = $db->prepare($sql2);
		$stmt2->execute();
		// $log->lwrite("Hi");
		$db = null;
		$log->lwrite("Debtor has successfully unsubscribe see the entry below for more information");
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
			
}
	
	$log->lwrite($response);	
	
?>