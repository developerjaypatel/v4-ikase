<?php 
$app->post('/feedback/invalidateNumber', 'invalidateNumber');
$app->post('/feedback/unsubscribeDebtor', 'unsubscribeDebtor');
$app->post('/feedback/updateIncoming', 'updateIncoming');

function invalidateNumber() {
	$drop_methods = passed_var("drop_methods", "post");
	$debtor_id = passed_var("debtor_id", "post");
	$customer_id = passed_var("customer_id", "post");
	
    $sql = "SELECT `" . $drop_methods . "` `drop_method` 
		FROM `tbl_debtor`
		WHERE `debtor_id` = '" . $debtor_id . "'
		AND `customer_id` = '" . $customer_id . "'";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$phone_number = $stmt->fetchObject();
		
		$temp_phone = $phone_number->drop_method;
		$invalidatedPhoneNumber = "00" . $temp_phone;
		
		$sql = "UPDATE `tbl_debtor`
		  SET `" . $drop_methods . "` = '" . addslashes($invalidatedPhoneNumber) . "'
		  WHERE `debtor_id` = '" . $debtor_id . "'
		  AND `customer_id` = '" . $customer_id . "'";
		
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$db = null;
		// $log->lwrite("Debtor " . $debtor_id . "'s " . $drop_methods . " number(" . $phone_number->drop_method . ") has been successfully marked as invalid.");
	} catch(PDOException $e) {
		$error = array("error"=> array("sql"=>$sql, "text"=>$e->getMessage()));
		// $log->lwrite(json_encode($error));
		echo json_encode($error);
	}
}

function unsubscribeDebtor() {
	// die(print_r($_POST));
	$debtor_id = passed_var("debtor_id", "post");
	$customer_id = passed_var("customer_id", "post");
     $sql = "UPDATE `tbl_debtor`
              SET `subscribe` = 'N'
              WHERE `debtor_id` = '" . $debtor_id . "'
              AND `customer_id` = '" . $customer_id . "'";
      try {
            $db = getConnection();
            $stmt = $db->prepare($sql);
            $stmt->execute();
            $db = null;
            // $log->lwrite("Debtor " . $debtor_id . " has been successfully unsubscribed.");
      } catch(PDOException $e) {
            $error = array("error"=> array("sql"=>$sql, "text"=>$e->getMessage()));
			// $log->lwrite(json_encode($error));
            echo json_encode($error);
      }
}

function updateIncoming() {
	// die(print_r($_POST));
	$batch_uuid = passed_var("batch_uuid", "post");
	$drip_uuid = passed_var("drip_uuid", "post");
	$drop_uuid = passed_var("drop_uuid", "post");
	$drop_id = passed_var("drop_id", "post");
	$debtor_id = passed_var("debtor_id", "post");
	$customer_id = passed_var("customer_id", "post");
	$batch_drop_id = passed_var("batch_drop_id", "post");
	$drop_methods = passed_var("drop_methods", "post");
	$digits = passed_var("digits", "post");
	$ip_address = passed_var("ip_address", "post");
	$request_JSON = passed_var("request_json", "post");
	$phone_number = passed_var("phone_number", "post");
	$request_uuid = passed_var("request_uuid", "post");
	$call_uuid = passed_var("call_uuid", "post");
	$script_name = passed_var("script_name", "post");
	$incoming_uuid = uniqid("IN", false);
	//   die($script_name);
	// remove the first 1 if necessary
	if($phone_number[0] == 1){
		$phone_number = substr($phone_number, 1);
	}
	
	$sql = "SELECT DISTINCT `debtor_uuid` 
			  FROM `tbl_debtor` 
			  WHERE `debtor_id` = " . $debtor_id . "
			  AND `customer_id` = " . $customer_id;
	try {
		// die($sql1);
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$debtors = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		// insert into the incoming. Note: machine is set to 'N' because no machine response will not happen if answered.      
		$sql = "INSERT INTO `tbl_incoming` (`incoming_uuid`, `batch_drop_id`, `batch_uuid`, `debtor_uuid`, `drip_uuid`, `drop_uuid`, `drop_number`, `request_uuid`, `file_name`, `call_uuid`, `number_called`, `machine`, `content`, `recipient_response`, `customer_id`, `ip_address`) 
		VALUES('" . $incoming_uuid . "', '" . $batch_drop_id . "', '" . $batch_uuid . "', '" . $debtors[0]->debtor_uuid . "', '" . $drip_uuid . "', '" . $drop_uuid . "', '" . $drop_id . "', '". $request_uuid ."', '". $script_name . "', '" . $call_uuid . "', '". $phone_number ."', 'N', '". $request_JSON . "', '" . $digits . "', '" . $customer_id . "', '" . $ip_address . "')";
		//  die($sql);
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$db = null;
		echo json_encode(array("success"=>"true"));
		//$log->lwrite("Debtor " . $debtor_id . " at phone number:" . $phone_number . " has been contacted and sent a response noted in the DB.");
	} catch(PDOException $e) {
		$error = array("error"=> array("sql"=>$sql, "text"=>$e->getMessage()));
		// $log->lwrite(json_encode($error));
		echo json_encode($error);
	}
}

?>