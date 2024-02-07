<?php
error_reporting(E_ALL);
// ^ E_DEPRECATED ^ E_WARNING  ^ E_NOTICE
ini_set('display_errors', '1');
include("../api/connection.php");
require_once(APILIB_PATH.'legacy_session.php');
session_write_close();

if (!isset($_SESSION['user_customer_id'])) {
	die("no no go");
}
/*
This is if there is no session.

$sql_customer = "SELECT data_source
FROM  `cse_customer` 
WHERE customer_id = :customer_id";

$customer_id = $_SESSION['user_customer_id'];
$stmt = $db->prepare($sql_customer);
$stmt->bindParam("customer_id", $customer_id);
$stmt->execute();
$customer = $stmt->fetchObject();
//die(print_r($customer));
$data_source = $customer->data_source;
*/
// die(print_r($_POST));



if(isset($_POST["event_id"])){
    $arrPerson = updateReminders();
} else {
    die(json_encode(array("success"=>"false", "message"=>"no event id set")));
}
// die(print_r($arrPerson));
$data = json_encode($arrPerson);
die(json_encode(array("success"=>"true", "data"=>$data)));
/*
function insertReminders() {
    $arrPerson = array();
    $arrWorkerID = array();
    $arrCaseWorker = array();
    $arrReminderNumber = array();
    $arrReminderType = array();
    $arrReminderInterval = array();
    $arrReminderSpan = array();
    $arrReminderDateTime = array();
    $arrSameForWorker = array();
    $arrMessageWorker = array();
    $customer_id = $_SESSION['user_customer_id'];
    $customer_id = '1033';
    // die("customer id = " . $customer_id);
    $sender_uuid = $_SESSION["user_id"];
    // die("sender_uuid id = " . $sender_uuid);
        
    foreach ($_POST as $fieldname => $value) {
        
        $strpos = strpos($fieldname, "case_worker_");
        if ($strpos !== false) {
            $arrCaseWorker[] = $value;
        }   
        
        $strpos = strpos($fieldname, "case_worker_id_");
        if ($strpos !== false) {
            $arrWorkerID[] = $value;
        }

        $strpos = strpos($fieldname, "reminder_number_");
        if ($strpos !== false) {
            $arrReminderNumber[] = $value;
        }   
        
        $strpos = strpos($fieldname, "reminder_type_");
        if ($strpos !== false) {
            $arrReminderType[] = $value;
        }

        $strpos = strpos($fieldname, "reminder_interval_");
        if ($strpos !== false) {
            $arrReminderInterval[] = $value;
        }   
        
        $strpos = strpos($fieldname, "reminder_span_");
        if ($strpos !== false) {
            $arrReminderSpan[] = $value;
        }

        $strpos = strpos($fieldname, "reminder_datetime_");
        if ($strpos !== false) {
            $arrReminderDateTime[] = $value;
        }

        $strpos = strpos($fieldname, "same_for_worker_");
        if ($strpos !== false) {
            $arrSameForWorker[] = $value;
        }

        $strpos = strpos($fieldname, "message_worker_");
        if ($strpos !== false) {
            $arrMessageWorker[] = $value;
        }
    }
    // die(print_r($_POST));
    $event_date = date("Y-m-d H:i:s", strtotime($_POST["event_date"]));

    // die(print_r($arrSameForWorker));
    // die(print_r($arrWorkerID));
    
    $total_count = count($arrWorkerID);
    for ($i=0; $i < $total_count; $i++) { 
        $user_id = $arrWorkerID[$i];
        $get_phone_query = "SELECT `user_uuid`, `user_name`, `user_cell` FROM cse_user WHERE `user_id` = '" . $user_id . "'";
        // die($get_phone_query);
        try {
            $stmt = DB::run($get_phone_query);
            // $message_db = $stmt->fetchAll(PDO::FETCH_OBJ);
            $user = $stmt->fetchObject();

            // die(print_r($message_db));
        } catch(PDOException $e) {
            $error = array("error"=> array("text"=>$e->getMessage()));
            echo json_encode($error);
        }
        //if same for all checked then just use the first
        if(count($arrSameForWorker) > 0){
            $message = $arrMessageWorker[0];
        } else {
            $message = $arrMessageWorker[$i];
        }
        
        //NOTE schema ikase may need to be removed
        $message_uuid = uniqid("KS", false);
        $sql = "INSERT INTO cse_message (`message_uuid`, `message_type`, `from`, `message_to`, `message`, `callback_date`, `customer_id`)
                VALUES ('" . $message_uuid . "', 'reminder', 'system', '" . $user->user_cell . "', '" . $message . "', '0000-00-00 00:00:00', '" . $customer_id . "')";    
        
        //if the recipient is in fact a user, could be that he is a partie
        
        $message_user_uuid = uniqid("TD", false);
        $str_query = "INSERT INTO cse_message_user (`message_user_uuid`, `message_uuid`, `user_uuid`, `thread_uuid`, `type`, `read_date`, `action`, `last_updated_date`, `last_update_user`, `customer_id`)
                VALUES ('" . $message_user_uuid . "', '" . $message_uuid . "', '" . $user->user_uuid . "', '', 'to', '0000-00-00 00:00:00', 'reminder', '0000-00-00 00:00:00', '" . $sender_uuid . "', '" . $customer_id . "')";
    /*
        $str_SQL = "INSERT INTO cse_buffer (`message_uuid`, `from`, `subject`, `recipients`, `message`, `customer_id`)
                VALUES ('" . $message_uuid . "', '" . $sender_uuid . "', '', '" . $user->user_cell . "', '" . $message . "', '" . $customer_id . "')";

    *&/
        // echo $sql . ";\r\n" . $str_query . ";\r\n";
        // die();
        try {
            
            //cse_message
            DB::run($sql);
 $message_id = DB::lastInsertId();
            
            //cse_message_user
            $stmt = DB::run($str_query);
            
            // //cse_buffer
            // $db = getConnection();
            // $stmt = $db->prepare($str_SQL);
            // $stmt->execute();
        
            $arrPerson[] = array("user_id"=>$user_id, "user_cell"=>$user->user_cell, "message_id"=>$message_id, "reminder_number"=>$arrReminderNumber[$i], "reminder_type"=>$arrReminderType[$i], "reminder_interval"=>$arrReminderInterval[$i], "reminder_span"=>$arrReminderSpan[$i], "reminder_datetime"=>$arrReminderDateTime[$i]);

        } catch(PDOException $e) {
            $error = array("error"=> array("text"=>$e->getMessage()));
            echo json_encode($error);
        }
    }
    return $arrPerson;
}
*/
function updateReminders() {
    $trackname = "sending.txt";
    $event_id = passed_var("event_id", "post");
    // die(print_r($_POST));

    $get_event_uuid = "SELECT ce.event_id, ce.event_uuid, ce.event_title, ce.event_dateandtime, ce.full_address, 
                              CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, cc.case_number, cc.file_number, cc.case_name case_stored_name,
                              cr.reminder_id, cr.reminder_uuid, cm.message_id, cm.message_uuid 
                    FROM cse_event ce
                    LEFT OUTER JOIN cse_case_event cce
                    ON ce.event_uuid = cce.event_uuid
                    LEFT OUTER JOIN cse_case cc
                    ON cce.case_uuid = cc.case_uuid
                    LEFT OUTER JOIN `cse_case_corporation` ccorp
                    ON (cc.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
                    LEFT OUTER JOIN `cse_corporation` employer
                    ON ccorp.corporation_uuid = employer.corporation_uuid
                    LEFT OUTER JOIN cse_case_person ccapp 
                    ON cc.case_uuid = ccapp.case_uuid
                    LEFT OUTER JOIN ";
    if ($_SESSION['user_customer_id']==1033) {
        $get_event_uuid .= "(" . SQL_PERSONX . ")";
    } else {
        $get_event_uuid .= "cse_person";
    }
    $get_event_uuid .= " app 
                    ON ccapp.person_uuid = app.person_uuid
                    LEFT OUTER JOIN cse_event_reminder cer
                    ON ce.event_uuid = cer.event_uuid
                    LEFT OUTER JOIN cse_reminder cr
                    ON cer.reminder_uuid = cr.reminder_uuid
                    LEFT OUTER JOIN cse_reminder_message crm
                    ON cr.reminder_uuid = crm.reminder_uuid
                    LEFT OUTER JOIN cse_message cm
                    ON crm.message_uuid = cm.message_uuid
                    WHERE 1
                    AND ce.event_id = '" . $event_id . "' AND ce.customer_id = " . $_SESSION["user_customer_id"];
    
    $get_event_uuid = str_replace("-1", "1033", $get_event_uuid);
    // die($get_event_uuid);
    try {
        $event_object = DB::select($get_event_uuid);
        // $event_object = $stmt->fetchObject();
        // die(print_r($event_object));
        
        // $fp = fopen($trackname, "a+");
        // fwrite($fp, "got the event object.\r\n");
        // fclose($fp); 

        foreach ($event_object as $key => $row) {
            $id_reminder = $row->reminder_id;         
            $sql = "UPDATE `cse_reminder` 
                    SET `deleted` = 'Y' 
                    WHERE `reminder_id` = '" . $id_reminder . "' AND customer_id = " . $_SESSION["user_customer_id"];  
            // echo $sql . ";\r\n";
            
            $stmt = DB::run($sql);
            $id_message = $row->message_id;
            $sql = "UPDATE `cse_message` 
                    SET `deleted` = 'Y' 
                    WHERE `message_id` = '" . $id_message . "' AND customer_id = " . $_SESSION["user_customer_id"];
            // echo $sql . ";\r\n";
           
            $stmt = DB::run($sql);
        }
        
    } catch(PDOException $e) {
        $error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
    }
            
    // $fp = fopen($trackname, "a+");
    // fwrite($fp, "deleted the existing reminders and messages.\r\n");
    // fclose($fp); 

    // die("all are deleted");
    $arrPerson = array();
    //header information is from the first row since all same event
    $event_uuid = $event_object[0]->event_uuid;
    $event_title = $event_object[0]->event_title;
    $event_dateandtime = $event_object[0]->event_dateandtime;
    $event_location = $event_object[0]->full_address;
    $case_name = $event_object[0]->case_name;
	if ($case_name!="") {
		//case name might be blank
		$case_name = "\n" . $case_name;
	} else {
        $case_name = "\n" . $event_object[0]->case_stored_name;
    }
    // die("case name: " . $case_name);

    // $fp = fopen($trackname, "a+");
    // fwrite($fp, "event constants and name are set.\r\n");
    // fclose($fp);

    $arrWorkerID = array();
    $arrCaseWorker = array();
    $arrCaseWorkerTable = array();

    $arrReminderIdFirst = array();
    $arrReminderNumberFirst = array();
    $arrReminderTypeFirst = array();
    $arrReminderIntervalFirst = array();
    $arrReminderSpanFirst = array();
    $arrReminderDateTimeFirst = array();

    $arrReminderIdSecond = array();
    $arrReminderNumberSecond = array();
    $arrReminderTypeSecond = array();
    $arrReminderIntervalSecond = array();
    $arrReminderSpanSecond = array();
    $arrReminderDateTimeSecond = array();

    $arrMessageWorkerFirst = array();
    $arrMessageWorkerSecond = array();

    $arrSameForWorker = array();
    $arrMessageId = array();
    $sender_uuid = $_SESSION["user_id"];
    $customer_id = $_SESSION['user_customer_id'];
    // $customer_id = '1033';
    // die("number of posts: " . count($_POST));
    foreach ($_POST as $fieldname => $value) {
        
        $strpos = strpos($fieldname, "case_worker_id_");
        if ($strpos !== false) {
            $arrWorkerID[] = $value;
            // echo "case_worker_id: " . $value . "\r\n";
        }

        $strpos = strpos($fieldname, "case_worker_table_");
        if ($strpos !== false) {
            $arrCaseWorkerTable[] = $value;
            // echo "case_worker_table: " . $value . "\r\n";
        }  

        $strpos = strpos($fieldname, "case_worker_");
        if ($strpos !== false) {
            if(strpos($fieldname, "case_worker_id_") !== false || strpos($fieldname, "case_worker_table_") !== false){
                continue;
            } else{
                $arrCaseWorker[] = $value;
                // echo "case_worker: " . $value . "\r\n";
            }
        }   

        $strpos = strpos($fieldname, "reminder_id_");
        if ($strpos !== false && strpos($fieldname, "_first") !== false) {
            $arrReminderId[] = $value;
            // echo "reminder_id: " . $value . ";first \r\n";
        }
        
        $strpos = strpos($fieldname, "reminder_id_");
        if ($strpos !== false && strpos($fieldname, "_second") !== false) {
            $arrReminderIdFirst[] = $value;
            // echo "reminder_id: " . $value . ";second \r\n";
        }

        $strpos = strpos($fieldname, "message_id_");
        if ($strpos !== false) {
            $arrMessageIdSecond[] = $value;
            // echo "message_id: " . $value . "\r\n";
        }


        //pass data into arrays based on first or second Reminder

        $strpos = strpos($fieldname, "reminder_number_");
        if ($strpos !== false && strpos($fieldname, "_first") !== false) {
            if($value == ""){
                $value = "1";
            }  
            $arrReminderNumberFirst[] = $value;
            // echo "reminder_number: " . $value . ";first \r\n";
        } 
        $strpos = strpos($fieldname, "reminder_number_");
        if ($strpos !== false && strpos($fieldname, "_second") !== false) {
            if($value == ""){
                $value = "1";
            }  
            $arrReminderNumberSecond[] = $value;
            // echo "reminder_number: " . $value . ";second \r\n";
        }   

        $strpos = strpos($fieldname, "reminder_type_");
        if ($strpos !== false && strpos($fieldname, "_first") !== false) {
            $arrReminderTypeFirst[] = $value;
            // echo "reminder_type: " . $value . ";first \r\n";
        }
        $strpos = strpos($fieldname, "reminder_type_");
        if ($strpos !== false && strpos($fieldname, "_second") !== false) {
            $arrReminderTypeSecond[] = $value;
            // echo "reminder_type: " . $value . ";second \r\n";
        }

        $strpos = strpos($fieldname, "reminder_interval_");
        if ($strpos !== false && strpos($fieldname, "_first") !== false) {
            $arrReminderIntervalFirst[] = $value;
            // echo "reminder_interval: " . $value . ";first \r\n";
        }   
        $strpos = strpos($fieldname, "reminder_interval_");
        if ($strpos !== false && strpos($fieldname, "_second") !== false) {
            $arrReminderIntervalSecond[] = $value;
            // echo "reminder_interval: " . $value . ";second \r\n";
        }

        $strpos = strpos($fieldname, "reminder_datetime_");
        if ($strpos !== false && strpos($fieldname, "_first") !== false) {
            $arrReminderDateTimeFirst[] = $value;
            // echo "reminder_datetime: " . $value . ";first \r\n";
        }
        $strpos = strpos($fieldname, "reminder_datetime_");
        if ($strpos !== false && strpos($fieldname, "_second") !== false) {
            $arrReminderDateTimeSecond[] = $value;
            // echo "reminder_datetime: " . $value . ";second \r\n";
        }
        
        $strpos = strpos($fieldname, "same_for_worker_");
        if ($strpos !== false) {
            $arrSameForWorker[] = $value;
            // echo "same_for_worker: " . $value . "\r\n";
        }

        $strpos = strpos($fieldname, "message_worker_");
        if ($strpos !== false && strpos($fieldname, "_first") !== false) {
            if($value == ""){
                $value = "empty";
            }
            $arrMessageWorkerFirst[] = $value;
            // echo "message_worker: " . $value . ";first \r\n";
        }
        $strpos = strpos($fieldname, "message_worker_");
        if ($strpos !== false && strpos($fieldname, "_second") !== false) {
            if($value == ""){
                $value = "empty";
            }            
            $arrMessageWorkerSecond[] = $value;
            // echo "message_worker: " . $value . ";second \r\n";
        }       
    }
    // die(print_r($arrWorkerID) . "\r\n" . print_r($arrCaseWorker) . "\r\n" . print_r($arrReminderNumberFirst) . "\r\n" . print_r($arrReminderTypeFirst) . "\r\n" . print_r($arrReminderIntervalFirst) . "\r\n" . print_r($arrMessageWorkerFirst) . "\r\n" . print_r($arrReminderDateTimeFirst) . "\r\n"  . print_r($arrReminderNumberSecond) . "\r\n" . print_r($arrReminderTypeSecond) . "\r\n" . print_r($arrReminderIntervalSecond) . "\r\n" . print_r($arrMessageWorkerSecond) . "\r\n" . print_r($arrReminderDateTimeSecond));

    // $fp = fopen($trackname, "a+");
    // fwrite($fp, "POST data sorted.\r\n");
    // fclose($fp);

    $total_count = count($arrWorkerID);
    for ($i=0; $i < $total_count; $i++) { 
        $table_name = $arrCaseWorkerTable[$i];
        $user_id = $arrWorkerID[$i];
        $get_phone_query = "";
        // die("the table name is " . $table_name . " the user id is " . $user_id);
        if($table_name == "user"){
            $get_phone_query = "SELECT `user_uuid`, `user_name`, `user_cell`, `nickname`, `user_email` 
                                FROM `ikase`.cse_user 
                                WHERE `user_id` = '" . $user_id . "' AND customer_id = " . $_SESSION["user_customer_id"];
        }
        if($table_name == "person"){
            $get_phone_query = "SELECT `person_uuid` `user_uuid`,  CONCAT(first_name,' ',last_name) `user_name`, `cell_phone` `user_cell`, `aka` `nickname`, `email` `user_email` 
                                FROM cse_person 
                                WHERE `person_id` = '" . $user_id . "' AND customer_id = " . $_SESSION["user_customer_id"];
        }

        if($table_name == "corporation"){
            $get_phone_query = "SELECT `corporation_uuid` `user_uuid`,  IF(`company_name` IS NULL or `company_name` = '', `full_name`, `company_name`) as `user_name`, `phone` `user_cell`, `aka` `nickname`, `email` `user_email` 
                                FROM cse_corporation 
                                WHERE `corporation_id` = '" . $user_id . "' AND customer_id = " . $_SESSION["user_customer_id"];
        }
        // die($get_phone_query);
        try {
            $stmt = DB::run($get_phone_query);
            // $message_db = $stmt->fetchAll(PDO::FETCH_OBJ);
            $user = $stmt->fetchObject();
            // die(print_r($user));
        } catch(PDOException $e) {
            $error = array("error"=> array("text"=>$e->getMessage()));
            echo json_encode($error);
        }
        
        // $fp = fopen($trackname, "a+");
        // fwrite($fp, "worker " . $i . "information recieved.\r\n");
        // fclose($fp);

        $last_updated_date = date("Y-m-d H:i:s");
        $reminder_id_first = "";
        $message_id_first = "";
        $reminder_id_second = "";
        $message_id_second = "";
        $message_first = "";                                              
        $message_second = ""; 
        try{           
            //FIRST REMINDER
            // Check to make sure there is a reminder date
            // echo "ReminderDateTimeFirst; " . $arrReminderDateTimeFirst[$i] . ";" . $i . " row;\r\n"; 
            
            if($arrReminderTypeFirst[$i] == "popup"){
                $message_to = $user->nickname;
            } else if($arrReminderTypeFirst[$i] == "email"){
                $message_to = $user->user_email;
            } else if($arrReminderTypeFirst[$i] == "text"){
                $message_to = str_replace("-", "", $user->user_cell);
            } else if($arrReminderTypeFirst[$i] == "voice"){
                $message_to = str_replace("-", "", $user->user_cell);
            } else if($arrReminderTypeFirst[$i] == "interoffice"){
                $message_to = $user->nickname;
            }

            if($arrMessageWorkerFirst[$i] == "empty"){
                $message = "";
                $message_for_voice_first = "";
            } else {
                $message = $arrMessageWorkerFirst[$i];
                $message_for_voice_first = $arrMessageWorkerFirst[$i];
            }
            $message_first = $message . $case_name . "\n" . $event_title . "\n" . date("m/d/y h:iA", strtotime($event_dateandtime)) . "\n" . $event_location;

            $reminder_uuid_first = uniqid("RM", false);   
            $reminder_message_uuid_first = uniqid("RM", false);
            $case_table_uuid_first = uniqid("ER", false);
            $message_uuid_first = uniqid("KS", false);
            $message_user_uuid_first = uniqid("TD", false);        
            // add passed_var when possible                        
            $reminder_datetime_first = date("Y-m-d H:i:s", strtotime($arrReminderDateTimeFirst[$i]));         

            $sql_first = "INSERT INTO cse_message (`message_uuid`, `message_type`, `dateandtime`, `from`, `message_to`, `message`, `callback_date`, `customer_id`)
                    VALUES ('" . $message_uuid_first . "', 'reminder', '" . date("Y-m-d H:i:s") . "', 'system', '" . $message_to . "', '" . addslashes($message_first) . "', '0000-00-00 00:00:00', '" . $customer_id . "')";    
            
            $str_query_first = "INSERT INTO cse_message_user (`message_user_uuid`, `message_uuid`, `user_uuid`, `thread_uuid`, `type`, `read_date`, `action`, `last_updated_date`, `last_update_user`, `customer_id`, `user_type`)
                        VALUES ('" . $message_user_uuid_first . "', '" . $message_uuid_first . "', '" . $user->user_uuid . "', '', 'to', '0000-00-00 00:00:00', 'reminder', '0000-00-00 00:00:00', '" . $sender_uuid . "', '" . $customer_id . "', '" . $table_name . "')";
                
            $strSQL_first = "INSERT INTO cse_reminder 
                    (`reminder_uuid`, `reminder_number`, `reminder_type`, `reminder_interval`, `reminder_span`, `reminder_datetime`, `buffered`, `customer_id`) 
                    VALUES ('" . $reminder_uuid_first . "', '" . $arrReminderNumberFirst[$i] . "', '" . $arrReminderTypeFirst[$i] . "', " . $arrReminderIntervalFirst[$i] . ", 'minutes', '" . $reminder_datetime_first . "', 'N', '" . $_SESSION['user_customer_id'] . "')";

            $query_first = "INSERT INTO cse_reminder_message 
                    (`reminder_message_uuid`, `reminder_uuid`, `message_uuid`, `attribute`, `last_update_user`, `customer_id`)
                    VALUES ('" . $reminder_message_uuid_first . "', '" . $reminder_uuid_first . "', '" . $message_uuid_first . "', 'main', '" . $sender_uuid . "', '" . $_SESSION['user_customer_id'] . "')";

                //attach each one to the event
            $str_SQL_first = "INSERT INTO cse_event_reminder 
                    (`event_reminder_uuid`, `event_uuid`, `reminder_uuid`, `attribute_1`, `last_updated_date`, `last_update_user`, `customer_id`)
                    VALUES ('" . $case_table_uuid_first  ."', '" . $event_uuid . "', '" . $reminder_uuid_first . "', '" . $arrReminderNumberFirst[$i] . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";

            // echo $sql_first . ";\r\n" . $str_query_first . ";\r\n" . $strSQL_first . ";\r\n" . $query_first . ";\r\n" . $str_SQL_first . ";\r\n";  
            
            //FIRST
            //message
            DB::run($sql_first);
 $message_id_first = DB::lastInsertId();
            //mesage_user
            $stmt = DB::run($str_query_first);
            //reminder
            DB::run($strSQL_first);
 $reminder_id_first = DB::lastInsertId();
            //reminder_message
            $stmt = DB::run($query_first);
            //event_reminder
            $stmt = DB::run($str_SQL_first);

            if($arrReminderTypeFirst[$i] == "voice"){
                $url = "http://kustomweb.xyz/ikase_voice/make_mp3.php?folder=ikase&customer_id=" . $customer_id . "&reminder_id=" . $reminder_id_first . "&message_id=" . $message_id_first . "&message=" . urlencode($message_for_voice_first);
                // die($url);
                $fields_string = array();

                //open connection
                $ch = curl_init();
                //set the url, number of POST vars, POST data
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, false); 
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_COOKIEFILE, "cookies.txt");
                curl_setopt($ch, CURLOPT_POST, count($fields_string));
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
                $result = curl_exec($ch);
            }
            // $fp = fopen($trackname, "a+");
            // fwrite($fp, "first reminder " . $i . "proccessed.\r\n");
            // fclose($fp);            
            

            //SECOND REMINDER
            if($arrReminderTypeSecond[$i] == "popup"){
                $message_to = $user->nickname;
            } else if($arrReminderTypeSecond[$i] == "email"){
                $message_to = $user->user_email;
            } else if($arrReminderTypeSecond[$i] == "text"){
                $message_to = str_replace("-", "", $user->user_cell);
            } else if($arrReminderTypeSecond[$i] == "voice"){
                $message_to = str_replace("-", "", $user->user_cell);
            } else if($arrReminderTypeSecond[$i] == "interoffice"){
                $message_to = $user->nickname;
            }
            // echo "ReminderDateTimeSecond; " . $arrReminderDateTimeSecond[$i] . ";" . $i . " row;\r\n"; 

            if($arrMessageWorkerSecond[$i] == "empty"){
                $message = "";
                 $message_for_voice_second = "";
            } else {
                $message = $arrMessageWorkerSecond[$i];
                $message_for_voice_second = $arrMessageWorkerSecond[$i];
            }

            $message_second = $message . $case_name . "\n" . $event_title . "\n" . date("m/d/y h:iA", strtotime($event_dateandtime)) . "\n" . $event_location;
                    
            $reminder_uuid_second = uniqid("RM", false);   
            $reminder_message_uuid_second = uniqid("RM", false);
            $case_table_uuid_second = uniqid("ER", false);
            $message_uuid_second = uniqid("KS", false);
            $message_user_uuid_second = uniqid("TD", false);        
            $reminder_datetime_second = date("Y-m-d H:i:s", strtotime($arrReminderDateTimeSecond[$i]));

            $sql_second = "INSERT INTO cse_message (`message_uuid`, `message_type`, `dateandtime`, `from`, `message_to`, `message`, `callback_date`, `customer_id`)
                    VALUES ('" . $message_uuid_second . "', 'reminder', '" . date("Y-m-d H:i:s") . "', 'system', '" . $message_to . "', '" . addslashes($message_second) . "', '0000-00-00 00:00:00', '" . $customer_id . "')";    
            
            $str_query_second = "INSERT INTO cse_message_user (`message_user_uuid`, `message_uuid`, `user_uuid`, `thread_uuid`, `type`, `read_date`, `action`, `last_updated_date`, `last_update_user`, `customer_id`, `user_type`)
                        VALUES ('" . $message_user_uuid_second . "', '" . $message_uuid_second . "', '" . $user->user_uuid . "', '', 'to', '0000-00-00 00:00:00', 'reminder', '0000-00-00 00:00:00', '" . $sender_uuid . "', '" . $customer_id . "', '" . $table_name . "')";
                
            $strSQL_second = "INSERT INTO cse_reminder 
                    (`reminder_uuid`, `reminder_number`, `reminder_type`, `reminder_interval`, `reminder_span`, `reminder_datetime`, `buffered`, `customer_id`) 
                    VALUES ('" . $reminder_uuid_second . "', '" . $arrReminderNumberSecond[$i] . "', '" . $arrReminderTypeSecond[$i] . "', " . $arrReminderIntervalSecond[$i] . ", 'minutes', '" . $reminder_datetime_second . "', 'N', '" . $_SESSION['user_customer_id'] . "')";

            $query_second = "INSERT INTO cse_reminder_message 
                    (`reminder_message_uuid`, `reminder_uuid`, `message_uuid`, `attribute`, `last_update_user`, `customer_id`)
                    VALUES ('" . $reminder_message_uuid_second . "', '" . $reminder_uuid_second . "', '" . $message_uuid_second . "', 'main', '" . $sender_uuid . "', '" . $_SESSION['user_customer_id'] . "')";

                //attach each one to the event
            $str_SQL_second = "INSERT INTO cse_event_reminder 
                    (`event_reminder_uuid`, `event_uuid`, `reminder_uuid`, `attribute_1`, `last_updated_date`, `last_update_user`, `customer_id`)
                    VALUES ('" . $case_table_uuid_second  ."', '" . $event_uuid . "', '" . $reminder_uuid_second . "', '" . $arrReminderNumberSecond[$i] . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";        
                
            // echo $sql_second . ";\r\n" . $str_query_second . ";\r\n" . $strSQL_second . ";\r\n" . $query_second . ";\r\n" . $str_SQL_second . ";\r\n";
            // die();        

            //SECOND
            //message
            DB::run($sql_second);
 $message_id_second = DB::lastInsertId();
            //mesage_user
            $stmt = DB::run($str_query_second);
            //reminder
            DB::run($strSQL_second);
 $reminder_id_second = DB::lastInsertId();
            //reminder_message
            $stmt = DB::run($query_second);
            //event_reminder
            $stmt = DB::run($str_SQL_second);
            
            if($arrReminderTypeSecond[$i] == "voice"){
                $url = "http://kustomweb.xyz/ikase_voice/make_mp3.php?folder=ikase&customer_id=" . $customer_id . "&reminder_id=" . $reminder_id_second . "&message_id=" . $message_id_second . "&message=" . urlencode($message_for_voice_second);
                // die($url);
                $fields_string = array();

                //open connection
                $ch = curl_init();
                //set the url, number of POST vars, POST data
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, false); 
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($ch, CURLOPT_COOKIEFILE, "cookies.txt");
                curl_setopt($ch, CURLOPT_POST, count($fields_string));
                curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
                $result = curl_exec($ch);
            }             
            // $fp = fopen($trackname, "a+");
            // fwrite($fp, "second reminder " . $i . "proccessed.\r\n");
            // fclose($fp);            
            
            $arrPerson[] = array("user_id"=>$user_id, "reminder_id_first"=>$reminder_id_first, "message_id_first"=>$message_id_first, "reminder_id_second"=>$reminder_id_second, "message_id_second"=>$message_id_second, "message_first"=>addslashes($message_first), "message_second"=>addslashes($message_second));                                              
        } catch(PDOException $e) {
            $error = array("error"=> array("text"=>$e->getMessage()));
            echo json_encode($error);
        }
    }
    return $arrPerson;
}
?>
