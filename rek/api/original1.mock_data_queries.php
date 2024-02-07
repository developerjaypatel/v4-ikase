<?php
$app->get('/mock/create', authorize('user'), 'createMock');
$app->get('/mock/update', authorize('user'), 'updateMock');
$app->get('/mock/return', authorize('user'), 'returnMock');
// $app->get("mock/create", authorize("user"), "createMock");



function createMock() {
    //set the variables
    
    $batch_uuid = "DR56c3afbfbae4e";
    $drip_uuid = "DR56c4d89f56770";
    $drop_number = 1;
    $authorization_code = "approved";
    $arrReturn_JSON = "mock data entry";
    $customer_id = $_SESSION["user_customer_id"];
    $message = "Mock entry attempt";
    $arrDebtorUuid = array("DR55e84aa4ac759", "DR55cfd923cad7b", "DR55947ccf71b20", "DR56293629c7b57");
    $arrEmail = array("nick@kustomweb.com", "neal@bapats.com", "neal.bapat@gmail.com", "chuchuta23@gmial.com");
    $arrCellphone = array("8184862869", "8054685888", "5550348342", "8055559393");
    $attempt_status = "test";
    $attempt_date = date("Y-m-d H:i:s");

    $count = 35; // This spot determines the number of entries to add
    
    for ($i=1; $i < $count; $i++) { 
        $incoming_uuid = uniqid("IN", false);
        $random_method = rand(1, 3);
        $random_debtor = rand(0, 3);
        $random_coin_flip = rand(1, 6);
        $random_caller_input = rand(1, 3);
        $transaction_id = $i;
        $batch_drop_id = $random_method;
        $ping_number = $random_method;  
        $debtor_uuid = $arrDebtorUuid[$random_debtor];
        $cellphone = $arrCellphone[$random_debtor];
        $email = $arrEmail[$random_debtor];
        $machine = 'N';
        $recipient_response = '-1';
        switch ($random_method) {
            case '1':
                # sms
                $drop_uuid = "DR55e1feb53a21d";
                insertAttempt($batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number, $attempt_date, "sms", $cellphone, $attempt_status, $customer_id, $message);
                if($random_coin_flip == 1){
                    //opend the link
                    insertIncoming($incoming_uuid, $batch_drop_id, $batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number, $transaction_id, "zip_sms", $authorization_code, $cellphone, $machine, $arrReturn_JSON, $recipient_response, $customer_id);
                } else if ($random_coin_flip == 2){
                    //Opened the link and verified the zip_close
                    insertIncoming($incoming_uuid, $batch_drop_id, $batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number, $transaction_id, "zip_sms", $authorization_code, $cellphone, $machine, $arrReturn_JSON, $recipient_response, $customer_id);
                    insertIncoming($incoming_uuid, $batch_drop_id, $batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number, $transaction_id, "payment_form_opened_sms", $authorization_code, $cellphone, $machine, $arrReturn_JSON, $recipient_response, $customer_id);
                } else if ($random_coin_flip == 3){
                    //Opened the link and verified the zip_close and made a payment
                    insertIncoming($incoming_uuid, $batch_drop_id, $batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number, $transaction_id, "zip_sms", $authorization_code, $cellphone, $machine, $arrReturn_JSON, $recipient_response, $customer_id);
                    insertIncoming($incoming_uuid, $batch_drop_id, $batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number, $transaction_id, "payment_form_opened_sms", $authorization_code, $cellphone, $machine, $arrReturn_JSON, $recipient_response, $customer_id);           
                    insertIncoming($incoming_uuid, $batch_drop_id, $batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number, $transaction_id, "authorize_capture_sms", $authorization_code, $cellphone, $machine, $arrReturn_JSON, $recipient_response, $customer_id);
                    insertAuthorizeCapture($batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number);
                } else if ($random_coin_flip == 4){
                    //Opened the link and verified the zip_close and chose plan option
                    insertIncoming($incoming_uuid, $batch_drop_id, $batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number, $transaction_id, "zip_sms", $authorization_code, $cellphone, $machine, $arrReturn_JSON, $recipient_response, $customer_id);
                    insertIncoming($incoming_uuid, $batch_drop_id, $batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number, $transaction_id, "payment_form_opened_sms", $authorization_code, $cellphone, $machine, $arrReturn_JSON, $recipient_response, $customer_id);  
                    insertIncoming($incoming_uuid, $batch_drop_id, $batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number, $transaction_id, "payment_plan_form_opened_sms", $authorization_code, $cellphone, $machine, $arrReturn_JSON, $recipient_response, $customer_id);
                } else if ($random_coin_flip == 5){
                    //Opened the link and verified the zip_close and chose plan option and paid
                    insertIncoming($incoming_uuid, $batch_drop_id, $batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number, $transaction_id, "zip_sms", $authorization_code, $cellphone, $machine, $arrReturn_JSON, $recipient_response, $customer_id);
                    insertIncoming($incoming_uuid, $batch_drop_id, $batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number, $transaction_id, "payment_form_opened_sms", $authorization_code, $cellphone, $machine, $arrReturn_JSON, $recipient_response, $customer_id);  
                    insertIncoming($incoming_uuid, $batch_drop_id, $batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number, $transaction_id, "payment_plan_form_opened_sms", $authorization_code, $cellphone, $machine, $arrReturn_JSON, $recipient_response, $customer_id);
                    insertIncoming($incoming_uuid, $batch_drop_id, $batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number, $transaction_id, "recurr_sms", $authorization_code, $cellphone, $machine, $arrReturn_JSON, $recipient_response, $customer_id);  
                    insertRecurr($batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number);
                } else if ($random_coin_flip == 6){
                    //Opened the link and verified the zip_close and made a payment
                    insertIncoming($incoming_uuid, $batch_drop_id, $batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number, $transaction_id, "zip_sms", $authorization_code, $cellphone, $machine, $arrReturn_JSON, $recipient_response, $customer_id);
                    insertIncoming($incoming_uuid, $batch_drop_id, $batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number, $transaction_id, "payment_form_opened_sms", $authorization_code, $cellphone, $machine, $arrReturn_JSON, $recipient_response, $customer_id);           
                    insertIncoming($incoming_uuid, $batch_drop_id, $batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number, $transaction_id, "unsubscribe_sms", $authorization_code, $cellphone, $machine, $arrReturn_JSON, $recipient_response, $customer_id);
                }
                break;
            case '2':
                # email
                $drop_uuid = "DR55e32ad991ce5";
                insertAttempt($batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number, $attempt_date, "email", $email, $attempt_status, $customer_id, $message);
                if($random_coin_flip == 1){
                    //opend the link
                    
                    insertIncoming($incoming_uuid, $batch_drop_id, $batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number, $transaction_id, "zip_verification", $authorization_code, $email, $machine, $arrReturn_JSON, $recipient_response, $customer_id);
                } else if ($random_coin_flip == 2){
                    //Opened the link and verified the zip_close
                    insertIncoming($incoming_uuid, $batch_drop_id, $batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number, $transaction_id, "zip_verification", $authorization_code, $email, $machine, $arrReturn_JSON, $recipient_response, $customer_id);
                    insertIncoming($incoming_uuid, $batch_drop_id, $batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number, $transaction_id, "payment_form_opened", $authorization_code, $email, $machine, $arrReturn_JSON, $recipient_response, $customer_id);
                } else if ($random_coin_flip == 3){
                    //Opened the link and verified the zip_close and made a payment
                    insertIncoming($incoming_uuid, $batch_drop_id, $batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number, $transaction_id, "zip_verification", $authorization_code, $email, $machine, $arrReturn_JSON, $recipient_response, $customer_id);
                    insertIncoming($incoming_uuid, $batch_drop_id, $batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number, $transaction_id, "payment_form_opened", $authorization_code, $email, $machine, $arrReturn_JSON, $recipient_response, $customer_id);           
                    insertIncoming($incoming_uuid, $batch_drop_id, $batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number, $transaction_id, "authorize_capture", $authorization_code, $email, $machine, $arrReturn_JSON, $recipient_response, $customer_id);
                    insertAuthorizeCapture($batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number);
                } else if ($random_coin_flip == 4){
                    //Opened the link and verified the zip_close and chose plan option
                    insertIncoming($incoming_uuid, $batch_drop_id, $batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number, $transaction_id, "zip_verification", $authorization_code, $email, $machine, $arrReturn_JSON, $recipient_response, $customer_id);
                    insertIncoming($incoming_uuid, $batch_drop_id, $batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number, $transaction_id, "payment_form_opened", $authorization_code, $email, $machine, $arrReturn_JSON, $recipient_response, $customer_id);  
                    insertIncoming($incoming_uuid, $batch_drop_id, $batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number, $transaction_id, "payment_plan_form_opened", $authorization_code, $email, $machine, $arrReturn_JSON, $recipient_response, $customer_id);
                } else if ($random_coin_flip == 5){
                    //Opened the link and verified the zip_close and chose plan option and paid
                    insertIncoming($incoming_uuid, $batch_drop_id, $batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number, $transaction_id, "zip_verification", $authorization_code, $email, $machine, $arrReturn_JSON, $recipient_response, $customer_id);
                    insertIncoming($incoming_uuid, $batch_drop_id, $batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number, $transaction_id, "payment_form_opened", $authorization_code, $email, $machine, $arrReturn_JSON, $recipient_response, $customer_id);  
                    insertIncoming($incoming_uuid, $batch_drop_id, $batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number, $transaction_id, "payment_plan_form_opened", $authorization_code, $email, $machine, $arrReturn_JSON, $recipient_response, $customer_id);
                    insertIncoming($incoming_uuid, $batch_drop_id, $batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number, $transaction_id, "recurr", $authorization_code, $email, $machine, $arrReturn_JSON, $recipient_response, $customer_id);  
                    insertRecurr($batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number);
                } else if ($random_coin_flip == 6){
                    //Opened the link and verified the zip_close and made a payment
                    insertIncoming($incoming_uuid, $batch_drop_id, $batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number, $transaction_id, "zip_verification", $authorization_code, $cellphone, $machine, $arrReturn_JSON, $recipient_response, $customer_id);
                    insertIncoming($incoming_uuid, $batch_drop_id, $batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number, $transaction_id, "payment_form_opened", $authorization_code, $cellphone, $machine, $arrReturn_JSON, $recipient_response, $customer_id);           
                    insertIncoming($incoming_uuid, $batch_drop_id, $batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number, $transaction_id, "unsubscribe_email", $authorization_code, $cellphone, $machine, $arrReturn_JSON, $recipient_response, $customer_id);
                }
                break;
            case '3':
                # cellphone
                $drop_uuid = "DR560b3bb874ce0";
                insertAttempt($batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number, $attempt_date, "cellphone", $cellphone, $attempt_status, $customer_id, $message);
                $recipient_response = $random_caller_input;
                insertIncoming($incoming_uuid, $batch_drop_id, $batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number, $transaction_id, "gather", $authorization_code, $cellphone, $machine, $arrReturn_JSON, $recipient_response, $customer_id);
                $recipient_response = "";
                $machine = "Y";
                insertIncoming($incoming_uuid, $batch_drop_id, $batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number, $transaction_id, "hangup", $authorization_code, $cellphone, $machine, $arrReturn_JSON, $recipient_response, $customer_id);
                break;                    
        }
        echo "Mock Data Entry Number: " . $i;
    }
}
function insertAttempt($batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number, $attempt_date, $method, $cellphone, $attempt_status, $customer_id, $message) {
    $attempt_uuid = uniqid("DR", false);
    $sql = "INSERT INTO `tbl_batch_debtor_attempt` (`batch_debtor_attempt_uuid`, `batch_uuid`, `debtor_uuid`, `drip_uuid`, `drop_uuid`, `drop_number`, `ping_number`, `attempt_date`, `method`, `attempt_destination`, `attempt_status`, `customer_id`, `message`, `deleted`) 
            VALUES('" . $attempt_uuid . "', '" . $batch_uuid . "', '" . $debtor_uuid . "', '" . $drip_uuid . "', '" . $drop_uuid . "', '" . $drop_number . "', '" . $ping_number . "', '" . $attempt_date . "', '" . $method . "', '" . $cellphone ."', '" . $attempt_status ."', '" . $customer_id . "', '" . $message ."', 'N')";
// die($sql);            
    try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$db = null;
		// echo json_encode($batch);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}    
}
function insertIncoming($incoming_uuid, $batch_drop_id, $batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number, $transaction_id, $script_name, $authorization_code, $cellphone, $machine, $arrReturn_JSON, $recipient_response, $customer_id){
    $sql = "INSERT INTO `tbl_incoming` (`incoming_uuid`, `batch_drop_id`, `batch_uuid`, `debtor_uuid`, `drip_uuid`, `drop_uuid`, `drop_number`, `ping_number`, `request_uuid`, `file_name`, `call_uuid`, `number_called`, `machine`, `content`, `recipient_response`, `customer_id`) 
            VALUES('" . $incoming_uuid . "', '" . $batch_drop_id . "', '" . $batch_uuid . "', '" . $debtor_uuid . "', '" . $drip_uuid . "', '" . $drop_uuid . "', '" . $drop_number . "', '" . $ping_number . "', '" . $transaction_id . "', '" . $script_name . "', '" . $authorization_code . "', '". $cellphone ."', '" . $machine ."', '". $arrReturn_JSON . "', '" . $recipient_response . "', '" . $customer_id . "')";
// die($sql);            
    try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$db = null;
		// echo json_encode($batch);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function insertAuthorizeCapture($batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number){
    $payment_uuid = uniqid("PA", false);
    $amount = rand(0, 100);
    
    $sql_payment = "INSERT INTO `tbl_payment`(`payment_uuid`, `debtor_uuid`, `payment_amount`, `batch_uuid`, `drip_uuid`, `drop_uuid`, `drop_number`, `ping_number`)
                        VALUES ('" . $payment_uuid . "', '" . $debtor_uuid . "', " . $amount . ", '" . $batch_uuid . "', '" . $drip_uuid . "', '" . $drop_uuid . "', '" . $drop_number . "', '" . $ping_number . "')";
    // die($sql_payment);
    
    $sql_debtor = "UPDATE `tbl_debtor`
                SET `total_payments` = `total_payments` + " . $amount . ",
                `payment_type` = 'payment'
                WHERE `tbl_debtor`.`debtor_uuid` = '" . $debtor_uuid . "'";
    // die($sql_debtor);

    try {
		$db = getConnection();
       
        $stmt_payment = $db->prepare($sql_payment);
        $stmt_payment->execute();
		
        $stmt_debtor = $db->prepare($sql_debtor);
        $stmt_debtor->execute();
    
		$db = null;
		// echo json_encode($batch);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function insertRecurr($batch_uuid, $debtor_uuid, $drip_uuid, $drop_uuid, $drop_number, $ping_number){
    $payment_uuid = uniqid("PP", false);
    $amount = rand(0, 100);
    $totalOccurrences = 5;
    $intervalLength = 1;
    $intervalUnit = "Months";
    $startDate = "01/01/2016";
    
    $sql_plan = "INSERT INTO `tbl_payment_plan`(`payment_plan_uuid`, `debtor_uuid`, `payment_plan_amount`, `installments`, `interval_length`, `interval_unit`, `batch_uuid`, `drip_uuid`, `drop_uuid`, `drop_number`, `ping_number`, `start_date`)
            VALUES ('" . $payment_uuid . "', '" . $debtor_uuid . "', '" . $amount ."', '" . $totalOccurrences . "', '" . $intervalLength . "', '" . $intervalUnit ."', '" . $batch_uuid . "', '" . $drip_uuid . "', '" . $drop_uuid . "', '" . $drop_number ."', '" .  $ping_number . "', '" . date("Y-m-d", strtotime($startDate)) . "')"; 
    // echo $sql_plan;
    
    $sql_debtor = "UPDATE `tbl_debtor`
                SET `payment_type` = 'planned'
                WHERE `tbl_debtor`.`debtor_uuid` = '" . $debtor_uuid . "'";
    // die($sql_debtor);

    try {
		$db = getConnection();

        $stmt_plan = $db->prepare($sql_plan);
        $stmt_plan->execute();

        $stmt_debtor = $db->prepare($sql_debtor);
        $stmt_debtor->execute();
        
		$db = null;
		// echo json_encode($batch);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
?>