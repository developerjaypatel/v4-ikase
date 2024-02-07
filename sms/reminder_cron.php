<?php
include("../api/connection.php");

$from = "system";
if (isset($_GET["from"])) {
	$from = $_GET["from"];
}
$trackname = "tracking.txt";
$fp = fopen($trackname, "a+");
fwrite($fp, "cron called at " . date("m/d/Y H:i:s") . " from " . $from . "\r\n");
fclose($fp);
//RUN SCHEMA QUERY FIRST
//LOOK TO GET SCHEMANAME
$query_date_now = date("Y-m-d H:i");
$query_date_before = date("Y-m-d H:i", strtotime("-15 minutes"));
/*
$query_date_now = '2017-03-06 14:50';
$query_date_before = '2017-03-06 14:45';
*/
$sql = "SELECT `schema_name`
FROM `information_schema`.schemata 
WHERE schema_name LIKE 'ikase%'";

try {
	$schemas = DB::select($sql);
} catch(PDOException $e) {
    $error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
}	
	//die(print_r($schemas));
	
foreach($schemas as $schema) {
    //skip
    if ($schema->schema_name=="ikase_glauber" || $schema->schema_name=="ikase_glauber2") {
        continue;
    }
	echo "trying " . $schema->schema_name . "<br />\r\n";
	//get the customer id based on the schema name
	/*
	$sql = "SELECT * 
	FROM ikase.cse_customer
	WHERE data_source = '" . str_replace("ikase_", $schema->schema_name) . "'";
	$stmt = DB::run($sql);
	$customer = $stmt->fetchObject();
	*/
	
    $sql = "SELECT cr.*, crm.`message_uuid`, cm.`message_to`, cm.`message`, ce.`event_title`
            FROM `" . $schema->schema_name . "`.`cse_reminder` cr
            LEFT OUTER JOIN `" . $schema->schema_name . "`.`cse_reminder_message` crm
            ON cr.`reminder_uuid` = crm.`reminder_uuid`
            LEFT OUTER JOIN `" . $schema->schema_name . "`.`cse_event_reminder` cer
            ON cr.`reminder_uuid` = cer.`reminder_uuid`
            LEFT OUTER JOIN `" . $schema->schema_name . "`.`cse_event` ce
            ON cer.`event_uuid` = ce.`event_uuid`
            LEFT OUTER JOIN `" . $schema->schema_name . "`.`cse_message` cm
            ON crm.`message_uuid` = cm.`message_uuid`
            LEFT OUTER JOIN `" . $schema->schema_name . "`.`cse_reminderbuffer` crb
            ON cr.`reminder_uuid` = crb.`reminder_uuid`
            WHERE 1 
            AND DATE_FORMAT(cr.reminder_datetime, '%Y-%m-%d %H:%i') between '" . $query_date_before . "' and '" . $query_date_now . "'
            AND cr.deleted = 'N'
			AND cr.buffered = 'N'
			AND cr.reminder_type != 'interoffice'
			AND cr.reminder_type != 'popup'
            AND crb.`reminderbuffer_id` IS NULL
			ORDER BY cr.reminder_datetime ASC";
        
    // die($sql);
    // echo $sql . "\r\n";
    // $fp = fopen($trackname, "a+");
    // fwrite($fp, $sql . "\r\n");
    // fclose($fp);
    try {
        $buffers = DB::select($sql);
		//die(print_r($buffers));
        // $buffer = $stmt->fetchObject();
		
        $numberbuffer = count($buffers);
/*
        $fp = fopen($trackname, "a+");
        fwrite($fp, "got reminders count - " . $numberbuffer . " - " . date("m/d/Y H:i:s") . "\r\n");
        fclose($fp);
*/
		if (count($buffers) > 0) {
			//  $fp = fopen($trackname, "a+");
    		// fwrite($fp, "Buffer Query\r\n");
			// fwrite($fp, $sql . "\r\n\r\n");
    		// fclose($fp);
			// die(print_r($buffers));
		} else {
			continue;
		}
		
        foreach ($buffers as $key => $buffer) {
            // $fp = fopen($trackname, "a+");
            // fwrite($fp, "buffers not empty at " . date("m/d/Y H:i:s") . "\r\n");
            // fclose($fp);
            //fill the buffer
			$customer_id = $buffer->customer_id;
            $reminder_type = $buffer->reminder_type;

            $message = $buffer->message;
            $reminder_uuid = $buffer->reminder_uuid;
            $reminder_id = $buffer->reminder_id;
            $buffer_id = $buffer->buffer_id;    
            $message_uuid = $buffer->message_uuid;
            $event_title = $buffer->event_title;
            if($reminder_type == "text"){
                $cellphone = "1" . $buffer->message_to;
                $str_SQL = "INSERT INTO `" . $schema->schema_name . "`.`cse_reminderbuffer` (`message_uuid`, `reminder_uuid`, `from`, `from_address`, `recipients`, `subject`, `message`, `customer_id`) 
                            VALUES ('" . $message_uuid . "', '" . $reminder_uuid . "', 'system', '" . $cellphone . "', '" . $cellphone . "', '', '" . addslashes($message) . "', '" . $customer_id . "')";
            } elseif ($reminder_type == "email") {
                $cellphone = $buffer->message_to;
                $str_SQL = "INSERT INTO `" . $schema->schema_name . "`.`cse_reminderbuffer` (`message_uuid`, `reminder_uuid`, `from`, `from_address`, `recipients`, `to`, `subject`, `message`, `customer_id`) 
                            VALUES ('" . $message_uuid . "', '" . $reminder_uuid . "', 'iKase Reminders', '" . $cellphone . "', '" . $cellphone . "', '" . $cellphone . "', '" . $event_title . "', '" . addslashes($message) . "', '" . $customer_id . "')";
            } elseif($reminder_type == "voice"){
                $cellphone = "1" . $buffer->message_to;
                $str_SQL = "INSERT INTO `" . $schema->schema_name . "`.`cse_reminderbuffer` (`message_uuid`, `reminder_uuid`, `from`, `from_address`, `recipients`, `subject`, `message`, `customer_id`) 
                            VALUES ('" . $message_uuid . "', '" . $reminder_uuid . "', 'system', '" . $cellphone . "', '" . $cellphone . "', '', '" . addslashes($message) . "', '" . $customer_id . "')";
            } // elseif($reminder_type == "popup"){
            //     $cellphone = $buffer->message_to;
            //     $str_SQL = "INSERT INTO `" . $schema->schema_name . "`.`cse_reminderbuffer` (`message_uuid`, `reminder_uuid`, `from`, `from_address`, `recipients`, `subject`, `message`, `customer_id`) 
            //                 VALUES ('" . $message_uuid . "', '" . $reminder_uuid . "', 'system', '" . $cellphone . "', '" . $cellphone . "', '', '" . addslashes($message) . "', '" . $customer_id . "')";
            // }
            // echo $str_SQL . ";\r\n";
            DB::run($str_SQL);
 $reminderbuffer_id = DB::lastInsertId();
           
            // $fp = fopen($trackname, "a+");
            // fwrite($fp, "reminderbuffer is inserted - " . $reminderbuffer_id . " - " . date("m/d/Y H:i:s") . "\r\n");
            // fclose($fp);

            $strSQL = "UPDATE `" . $schema->schema_name . "`.`cse_reminder` 
			SET `buffered` = 'Y' 
			WHERE `reminder_id` = '" . $reminder_id . "'";
            $stmt = DB::run($strSQL);
            
            $fp = fopen($trackname, "a+");
            fwrite($fp, "reminder is buffered - " . $reminderbuffer_id . " - for schema(" . $schema->schema_name . ") - " . date("m/d/Y H:i:s") . "\r\n");
            fclose($fp);            
        }
    } catch(PDOException $e) {
        $error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
    }
    // die(json_encode(array("success"=>"true")));
	usleep(700);
}
// $fp = fopen($trackname, "a+");
// fwrite($fp, "do we end up before the async call to reminder_send\r\n");
// fclose($fp);  
$params = array();
curl_post_async("https://" . $_SERVER['HTTP_HOST'] . "/sms/reminder_send.php", $params);
?>
