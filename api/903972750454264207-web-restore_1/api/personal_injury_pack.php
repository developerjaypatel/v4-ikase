<?php

$app->get('/personalinjury', authorize('user'),	'getPersonalInjuries');
//$app->get('/personalinjury/:case_id', authorize('user'),	'getPersonalInjury');
$app->get('/personalinjury/:case_id', authorize('user'), 'getFullPersonalInjury');
$app->post('/personalinjury/add', authorize('user'), 'addFullPersonalInjury');
$app->post('/personalinjury/addrental', authorize('user'), 'addRental');
$app->post('/personalinjury/addrepair', authorize('user'), 'addRepair');
//$app->post('/personal_injury/update', authorize('user'), 'updatePersonalInjury');

$app->post('/personalinjuryweekly','weeklyReportNewPICases');

$app->post('/personalinjury/field/update', authorize('user'), 'updatePersonalInjuryField');

function getPersonalInjuries() {
    $sql = "SELECT pi.* 
			FROM `cse_personal_injury` pi 
			WHERE pi.deleted = 'N'
			AND pi.customer_id = " . $_SESSION['user_customer_id'] . "
			ORDER by pi.personal_injury_id";
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$personalinjuries = $stmt->fetchAll(PDO::FETCH_OBJ);
	
		$db = null;
        if (!isset($_GET['callback'])) {
            echo json_encode($personalinjuries);
        } else {
            echo $_GET['callback'] . '(' . json_encode($personalinjuries) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getFullPersonalInjury($case_id, $blnReturn = false) {
	session_write_close();
	
	$customer_id = $_SESSION['user_customer_id'];
	
    $sql = "SELECT cc.case_id caseid, IFNULL(`personal_injury_id`, '') personal_injury_id,
    IFNULL(`personal_injury_uuid`, '') personal_injury_uuid,
    IFNULL(`personal_injury_date`, '0000-00-00 00:00:00') personal_injury_date,
    IFNULL(pi.`statute_limitation`, '0000-00-00') statute_limitation,
    IFNULL(pi.`statute_interval`, '') statute_interval,
    IFNULL(`personal_injury_description`, '') personal_injury_description,
    IFNULL(`personal_injury_info`, '') personal_injury_info,
    IFNULL(`personal_injury_details`, '') personal_injury_details,
    IFNULL(`personal_injury_other_details`, '') personal_injury_other_details, 
	IFNULL(`rental_info`, '') rental_info,
	IFNULL(`repair_info`, '') repair_info,
	IFNULL(pers.person_id, -1) owner_id,
    IFNULL(defpers.person_id, -1) defendant_owner_id,
	wits.witness_count,
  	pi.personal_injury_id id, pi.personal_injury_uuid uuid

	FROM `cse_case` cc
	LEFT OUTER JOIN `cse_personal_injury` pi 
	ON cc.case_id = pi.case_id AND pi.deleted = 'N'
	
	LEFT OUTER JOIN cse_case_injury cci
    ON cc.case_uuid = cci.case_uuid
	
    LEFT OUTER JOIN cse_injury_person cip
    ON cci.injury_uuid = cip.injury_uuid
    LEFT OUTER JOIN cse_person pers
    ON cip.person_uuid = pers.person_uuid
	
    LEFT OUTER JOIN cse_injury_person defcip
    ON cci.injury_uuid = defcip.injury_uuid AND defcip.attribute_1 = 'owner' AND defcip.attribute_2 = 'defendant'
    LEFT OUTER JOIN cse_person defpers
    ON defcip.person_uuid = defpers.person_uuid

	LEFT OUTER JOIN (
		SELECT :case_id case_id, COUNT(corp.corporation_id) witness_count
		FROM cse_corporation corp
		INNER JOIN cse_case_corporation ccc
		ON corp.corporation_uuid = ccc.corporation_uuid
		INNER JOIN cse_case ccase
		ON ccc.case_uuid = ccase.case_uuid
		WHERE ccase.case_id = :case_id
		AND corp.customer_id = :customer_id
		AND type = 'witnesses'
    ) wits
	ON cc.case_id = wits.case_id
	
	WHERE cc.case_id=:case_id
	AND cc.customer_id = :customer_id";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$personalinjury = $stmt->fetchObject();
		
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		if (is_object($personalinjury)) {
			/*
			//make sure any other is deleted
			$sql = "UPDATE `cse_personal_injury`
			SET deleted = 'Y'
			WHERE case_id=:case_id
			AND personal_injury_id != :personal_injury_id
			AND customer_id = :customer_id";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("case_id", $case_id);
			$stmt->bindParam("personal_injury_id", $personalinjury->id);
			$stmt->bindParam("customer_id", $customer_id);
			$stmt->execute();
			*/
			$personalinjury->personal_injury_info = str_replace("\r\n", " ", $personalinjury->personal_injury_info);
			$personalinjury->personal_injury_info = str_replace("\n", " ", $personalinjury->personal_injury_info);
			$personalinjury->personal_injury_info = str_replace(chr(13), " ", $personalinjury->personal_injury_info);
			
			if ($blnReturn) {
				return $personalinjury;
			} else {
				echo json_encode($personalinjury);
			}
		}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getPersonalInjury($case_id) {
    $sql = "SELECT pi.*, pi.personal_injury_id id, pi.personal_injury_uuid uuid
			FROM `cse_personal_injury` pi 
			WHERE pi.case_id=:case_id
			AND pi.customer_id = " . $_SESSION['user_customer_id'] . "
			AND pi.deleted = 'N'";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->execute();
		$personalinjury = $stmt->fetchObject();
		$db = null;

        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($personalinjury);
        } else {
            echo $_GET['callback'] . '(' . json_encode($personalinjury) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getPersonalInjuryInfo($case_id) {
    $sql = "SELECT pi.*, pi.personal_injury_id id, pi.personal_injury_uuid uuid
			FROM `cse_personal_injury` pi 
			WHERE pi.case_id=:case_id
			AND pi.customer_id = " . $_SESSION['user_customer_id'] . "
			AND pi.deleted = 'N'";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->execute();
		$personalinjury = $stmt->fetchObject();
		$db = null;
        return $personalinjury;

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
/*
function getInjuryAccident($case_id) {
    $sql = "SELECT acc.*, acc.accident_id id, acc.accident_uuid uuid, inj.injury_id
			FROM `cse_accident` acc 
			INNER JOIN `cse_injury_accident` cia
			ON acc.accident_uuid = cia.accident_uuid AND cia.deleted = 'N'
			INNER JOIN cse_injury inj
			ON cia.injury_uuid = inj.injury_uuid
			INNER JOIN cse_case_injury cci
			ON inj.injury_uuid = cci.injury_uuid
			INNER JOIN cse_case ccase
			ON cci.case_uuid = ccase.case_uuid
			WHERE ccase.case_id=:case_id
			AND acc.customer_id = " . $_SESSION['user_customer_id'] . "
			AND acc.deleted = 'N'";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->execute();
		$accident = $stmt->fetchObject();
		$db = null;

		//die($accident->accident_details);
        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($accident);
        } else {
            echo $_GET['callback'] . '(' . json_encode($accident) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
*/
function addPersonalInjury() {
	//die(print_r($_POST));
	//if ($_POST[0]) {
	//}
	$request = Slim::getInstance()->request();
	$arrFields = array();
	$arrSet = array();
	$table_name = "personal_injury";
	$table_id = "";
	$info = "";
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="case_id"){
			$case_id = $value;
			continue;
		}
		if ($fieldname=="table_id") {
			continue;
		}
		if ($fieldname=="table_uuid") {
			continue;
		}
		if ($fieldname=="personal_injury_id") {
			continue;
		}
		
		//if ($fieldname=="dateandtime") {
		if (strpos($fieldname, "_date")!==false || strpos($fieldname, "date_")!==false) {
			if ($value!="") {
				$value = date("Y-m-d H:i:s", strtotime($value));
			} else {
				$value = "0000-00-00 00:00:00";
			}
		}
		
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "'" . str_replace("'", "\'", $value) . "'";
	}	
	
	$table_uuid = uniqid("RD", false);
	//insert the parent record first
	$sql = "INSERT INTO `cse_" . $table_name ."` (`" . $table_name . "_uuid`, `customer_id`, " . implode(",", $arrFields) . ") 
		VALUES('" . $table_uuid . "', '" . $_SESSION['user_customer_id'] . "', " . implode(",", $arrSet) . ")";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		$new_id = $db->lastInsertId();
		
		echo json_encode(array("success"=>true, "id"=>$new_id));			
		$db = null;
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}
function addFullPersonalInjury() {
	//die(print_r($_POST));
	session_write_close();
	$request = Slim::getInstance()->request();
	$arrFields = array();
	$arrSet = array();
	$case_id = 0;
	$table_name = "personal_injury";
	$table_id = passed_var("table_id", "post");
	$customer_id = $_SESSION["user_customer_id"];
	//$case_id = passed_var("case_id", "post");
	$blnUpdate = (is_numeric($table_id) && $table_id!="" && $table_id > 0);
	$info = "";
	$injury_uuid = "";
	$case_uuid = "";
	$arrPersonnel = array();
	$statute_limitation = "0000-00-00";
	$last_updated_date = date("Y-m-d H:i:s");
	$rental_info = "";
	$repair_info = "";
	if ($_SERVER['REMOTE_ADDR']=='47.153.56.2') {
		//die(print_r($_POST));
	}
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="billing_time") {
			continue;
		}
		if ($fieldname=="statute_limitation") {
			if ($value!="") {
				$value = date("Y-m-d", strtotime($value));
			} else {
				$value = "0000-00-00";
			}
			$statute_limitation = $value;
		}
		if ($fieldname=="loss_date") {
			/*
			if ($value!="") {
				$value = date("Y-m-d", strtotime($value));
			} else {
				$value = "0000-00-00";
			}
			*/
		}
		if ($fieldname=="billing_time_dropdown") {
			continue;
		}
		
		if ($fieldname=="case_id"){
			
			$case_id = $value;
			//die("case:" . $case_id);
			$injury = getInjuriesInfo($case_id);
			$kase = getKaseInfo($case_id);
			//die(print_r($kase));
			if($kase->attorney != ""){
				if (!is_numeric($kase->attorney)) {
					$worker = getUserByNickname($kase->attorney);
					if (is_object($worker)) {
						$arrPersonnel["attorney"] = $worker->user_id;
					}
				} else {
					$arrPersonnel["attorney"] = $kase->supervising_attorney;
				}
			}
			if($kase->supervising_attorney != ""){
				if (!is_numeric($kase->supervising_attorney)) {
					$worker = getUserByNickname($kase->supervising_attorney);
					if (is_object($worker)) {
						$arrPersonnel["supervising_attorney"] = $worker->user_id;
					}
				} else {
					$arrPersonnel["supervising_attorney"] = $kase->supervising_attorney;
				}
			}
			if($kase->worker != ""){
				if (!is_numeric($kase->worker)) {
					$worker = getUserByNickname($kase->worker);
					if (is_object($worker)) {
						$arrPersonnel["worker"] = $worker->user_id;
					}
				} else {
					$arrPersonnel["worker"] = $kase->worker;
				}
			}
			
			$case_uuid = $kase->uuid;
			$injury_uuid = $injury[0]->uuid;
			
			if ($kase->case_number=="") {
				$kase->case_number = $kase->file_number;
			}
			//die(print_r($injury));
			continue;
		}
		if ($fieldname=="table_id") {
			continue;
		}
		
		//if ($fieldname=="dateandtime") {
		if (strpos($fieldname, "_date")!==false || strpos($fieldname, "date_")!==false) {
			if ($value!="") {
				$value = date("Y-m-d H:i:s", strtotime($value));
			} else {
				$value = "0000-00-00 00:00:00";
			}
		}
		if ($fieldname=="loss_date") {
			//remove loss_date, does not exist 12/7/2017
			$value = "0000-00-00";
		}
		//die("before");
		if (!$blnUpdate) {
			$arrFields[] = "`" . $fieldname . "`";
			$arrSet[] = "'" . addslashes($value) . "'";
		} else {
			$arrSet[] = "`" . $fieldname . "` = " . "'" . addslashes($value) . "'";
		}
		//die("after");
	}	
	if (!$blnUpdate) {
		$arrFields[] = "`rental_info`";
		$arrSet[] = "''";
		$arrFields[] = "`repair_info`";
		$arrSet[] = "''";
	}
	//$case_id = passed_var("case_id", "post");
	//die("case:" . $case_id);
	//die("table:" . $table_id);
	//die(print_r($arrFields));
	//die(print_r($arrPersonnel));
	
	//insert the parent record first
	if (!$blnUpdate) { 
		$table_uuid = uniqid("RD", false);
		$sql = "INSERT INTO `cse_" . $table_name ."` (`" . $table_name . "_uuid`, `customer_id`, " . implode(",", $arrFields) . ", `case_id`) 
			VALUES('" . $table_uuid . "', '" . $customer_id . "', " . implode(",", $arrSet) . ", '" . $case_id . "')";
		//die($sql);
		try {
			$db = getConnection();
			
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			$new_id = $db->lastInsertId();
			
			$stmt = null; $db = null;
			
			echo json_encode(array("success"=>true, "id"=>$new_id));
			
			//track now
			trackPersonalInjury("insert", $new_id, $case_id);
		} catch(PDOException $e) {	
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}	
	} else {
		$personal_injury = getPersonalInjuryInfo($case_id);	
		$table_uuid = $personal_injury->uuid;
		
		//where
		$where_clause = "= '" . $table_id . "'";
		$where_clause = "`" . $table_name . "_id`" . $where_clause . "
		AND `customer_id` = " . $customer_id;

		//actual query
		$sql = "UPDATE `cse_" . $table_name . "`
		SET " . implode(",", $arrSet) . "
		WHERE " . $where_clause;
		//die($sql);  
		try {
			$db = getConnection();
			
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			
			$stmt = null; $db = null;
			
			echo json_encode(array("success"=>true, "id"=>$table_id));
			
			//make sure any other is deleted
			$sql = "UPDATE `cse_personal_injury`
			SET deleted = 'Y'
			WHERE case_id=:case_id
			AND personal_injury_id != :personal_injury_id
			AND customer_id = :customer_id";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("case_id", $case_id);
			$stmt->bindParam("personal_injury_id", $table_id);
			$stmt->bindParam("customer_id", $customer_id);
			$stmt->execute();
			
			//track now
			trackPersonalInjury("update", $table_id, $case_id) ;
		} catch(PDOException $e) {	
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}	
		
		if ($statute_limitation!="0000-00-00") {
			//if we have a statute_limitation, we need matching event
			$sql = "SELECT ev.event_id, ev.event_uuid 
			FROM cse_injury_event cie
			INNER JOIN cse_event ev
			ON cie.event_uuid = ev.event_uuid AND ev.deleted = 'N'
			WHERE cie.injury_uuid = '" . $table_uuid . "'
			AND cie.`attribute` = 'statute_limitation'
			AND cie.`deleted` = 'N'
			AND ev.deleted = 'N'
			AND cie.customer_id = " . $customer_id;
			
			if ($_SERVER['REMOTE_ADDR']=='47.153.51.181') {
			//	die($sql);
			}
			$arrOutput["inj_event"] = $sql;
			
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->execute(); 
			$inj_event = $stmt->fetchObject();
			$stmt->closeCursor(); $stmt = null; $db = null;
			$message_first = "Statute of Limitations Reminder for " . $kase->case_name .  " - " . $kase->case_number . " (SOL:" . date("m/d/Y", strtotime($statute_limitation)) .")";
			$subject =  "Statute of Limitations Reminder for " . $kase->case_name .  " - " . $kase->case_number;
			
			if (!is_object($inj_event)) {
				//create an event
				$event_uuid = uniqid("KS", false);	
				$sql = "INSERT INTO `cse_event` (`event_uuid`, `event_dateandtime`, `event_date`, `event_hour`, `event_title`, `full_address`, `event_type`, `color`, `event_description`, `customer_id`)
					VALUES('" . $event_uuid . "', '" . $statute_limitation . " 08:00:00', '" . $statute_limitation . "' , '08:00:00', '" . $message_first . "' , '' , 'statute_limitation', 'purple', '" . $message_first . "', " . $customer_id . ")";
				$db = getConnection();
				$stmt = $db->prepare($sql);  
				$stmt->execute();	
				$event_id = $db->lastInsertId();
				
				$stmt = null; $db = null;
				
				trackEvent("insert", $event_id);
				
				//attach the event to the injury
				$injury_table_uuid = uniqid("KA", false);
				$attribute_1 = "statute_limitation";
				$sql = "INSERT INTO cse_injury_event (`injury_event_uuid`, `injury_uuid`, `event_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $injury_table_uuid  ."', '" . $table_uuid . "', '" . $event_uuid . "', '" . $attribute_1 . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $customer_id . "')";
				$db = getConnection();
				$stmt = $db->prepare($sql);  
				$stmt->execute();	
				$stmt = null; $db = null;	
				
				//attach the event to the CASE
				$injury_table_uuid = uniqid("KS", false);
				$attribute_1 = "statute_limitation";
				$sql = "INSERT INTO cse_case_event (`case_event_uuid`, `case_uuid`, `event_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $injury_table_uuid  ."', '" . $case_uuid . "', '" . $event_uuid . "', '" . $attribute_1 . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $customer_id . "')";
				$db = getConnection();
				$stmt = $db->prepare($sql);  
				$stmt->execute();	
				$stmt = null; $db = null;	
				
				//reminders		
				//20 days before SOL		
				$reminder_type = "interoffice";
				$reminder_interval = 20;
				$reminder_span = "days";
				
				//$reminder_uuid = uniqid("RM", false);
				$reminder_datetime = date("Y-m-d H:i:s", strtotime($statute_limitation . " 08:00:00" . " - " . $reminder_interval . " " . $reminder_span));
				$sender_uuid = $_SESSION["user_id"];
				$sender_id = $_SESSION["user_plain_id"];
				$dateandtime = date("Y-m-d H:i:s");
				$from = $_SESSION["user_name"];

				$pcounter = 0;
				foreach($arrPersonnel as $personel_id) {
					$reminder_uuid = uniqid("R" . $pcounter, false);
					$reminder_number = $pcounter + 1;
					$values = "'" . $reminder_uuid . "', '" . $reminder_number . "', '" . $reminder_type . "', '" . $reminder_interval . "', '" . $reminder_span . "', '"  . $reminder_datetime . "', '" . $customer_id . "'"; 
					//insert the reminder
					$sql = "INSERT cse_reminder (`reminder_uuid`, `reminder_number`, `reminder_type`, `reminder_interval`, `reminder_span`,`reminder_datetime`, `customer_id`) 
					VALUES(" . $values . ")";
					//echo $sql . "\r\n";
					$db = getConnection();
					$stmt = $db->prepare($sql);  
					$stmt->execute();	
					$stmt = null; $db = null;	
					
					$event_reminder_uuid = uniqid("E" . $pcounter, false);
					//attach each one to the event
					$sql = "INSERT INTO cse_event_reminder (`event_reminder_uuid`, `event_uuid`, `reminder_uuid`, `attribute_1`, `last_updated_date`, `last_update_user`, `customer_id`)
					VALUES ('" . $event_reminder_uuid  ."', '" . $event_uuid . "', '" . $reminder_uuid . "', '" . $reminder_number . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $customer_id . "')";
					//echo $sql . "\r\n";
					$db = getConnection();
					$stmt = $db->prepare($sql);  
					$stmt->execute();	
					$stmt = null; $db = null;
					
					$to_user = getUserInfo($personel_id);
					$message_to = $to_user->nickname;
					$thread_uuid = uniqid("TD", false);
					
					//i have the worker, i can send an interoffice message
					$sql = "INSERT INTO `cse_thread` (`customer_id`, `dateandtime`, `thread_uuid`, `from`, `subject`) 
					VALUES('" . $customer_id . "', '" . $dateandtime . "','" . $thread_uuid . "', '" . $from . "', '" . $subject . "')";
					//echo $sql . "<br />";
					
					$db = getConnection();
					$stmt = $db->prepare($sql);						
					$stmt->execute();
					$stmt = null; $db = null;
					
					$message_uuid = uniqid("K" . $pcounter, false);
            		$reminder_message_uuid = uniqid("RM", false);
			
					$sql = "INSERT INTO cse_message (`message_uuid`, `message_type`, `dateandtime`, `from`, `message_to`, `message`, `callback_date`, `customer_id`)
					VALUES ('" . $message_uuid . "', 'reminder', '" . $dateandtime . "', 'system', '" . $message_to . "', '" . addslashes($message_first) . "', '0000-00-00 00:00:00', '" . $customer_id . "')";   
					$db = getConnection();
					$stmt = $db->prepare($sql);  
					$stmt->execute();	
					$message_id = $db->lastInsertId();
					$stmt = null; $db = null;
					
					$case_message_uuid = uniqid("T" . $pcounter, false);
					$sql = "INSERT INTO cse_thread_message (`thread_message_uuid`, `thread_uuid`, `message_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`, message_id)
					VALUES ('" . $case_message_uuid  ."', '" . $thread_uuid . "', '" . $message_uuid . "', 'main', '" . $dateandtime . "', 'system', '" . $customer_id . "','" . $message_id . "')";
					
					$db = getConnection();
					$stmt = $db->prepare($sql);  
					$stmt->execute();
					$stmt = null; $db = null;
					
					//source
					$message_user_uuid = uniqid("T" . $pcounter, false);
					$sql = "INSERT INTO cse_message_user 
					(`message_user_uuid`, `message_uuid`, `user_uuid`, `type`, `last_updated_date`, `last_update_user`, `customer_id`, `thread_uuid`, message_id, user_id)
					VALUES ('" . $message_user_uuid  ."', '" . $message_uuid . "', '" . $sender_uuid . "', 'from', '" . $dateandtime . "', '" . $sender_uuid . "', '" . $customer_id . "', '". $thread_uuid . "','" . $message_id . "','" . $sender_id . "')";
					//echo $sql . "<br />";	
			
					$db = getConnection();	
					$stmt = $db->prepare($sql);  
					$stmt->execute();
					$stmt = null; $db = null;
					
					//destination
					$message_user_uuid = uniqid("F" . $pcounter, false); 
					$sql = "INSERT INTO cse_message_user (`message_user_uuid`, `message_uuid`, `user_uuid`, `thread_uuid`, `type`, `read_date`, `action`, `last_updated_date`, `last_update_user`, `customer_id`, `user_type`, message_id, user_id)
                    VALUES ('" . $message_user_uuid . "', '" . $message_uuid . "', '" . $to_user->user_uuid . "', '', 'to', '0000-00-00 00:00:00', 'reminder', '0000-00-00 00:00:00', '" . $sender_uuid . "', '" . $customer_id . "', 'user','" . $message_id . "','" . $to_user->user_id . "')";
					
					$db = getConnection();	
					$stmt = $db->prepare($sql);  
					$stmt->execute();
					$stmt = null; $db = null;
					
					//attach the reminder to the message
					$sql = "INSERT INTO cse_reminder_message 
                    (`reminder_message_uuid`, `reminder_uuid`, `message_uuid`, `attribute`, `last_update_user`, `customer_id`)
                    VALUES ('" . $reminder_message_uuid . "', '" . $reminder_uuid . "', '" . $message_uuid . "', 'main', '" . $sender_uuid . "', '" . $customer_id . "')";
					
					$db = getConnection();	
					$stmt = $db->prepare($sql);  
					$stmt->execute();
					$stmt = null; $db = null;	
					
					$pcounter++;
				}
			} else {
				//change the date on the event itself
				//$injury was obtained _before_ the update, so it holds old info
				if ($personal_injury->statute_limitation != $statute_limitation) {
					$sql = "UPDATE cse_event
					SET event_dateandtime = '" . $statute_limitation . " 08:00:00'
					WHERE event_uuid = '" . $inj_event->event_uuid . "'
					AND customer_id = " . $customer_id;
					
					$db = getConnection();
					$stmt = $db->prepare($sql);
					$stmt->execute(); 
					$stmt = null; $db = null;
					
					$arrOutput["update"] = $sql;
					
					//and of course update the reminder itself
					$sql = "SELECT reminder_uuid 
					FROM cse_event_reminder
					where `event_uuid` = '" . $inj_event->event_uuid . "'
					AND `deleted` = 'N'
					AND `customer_id` = " . $customer_id;
					
					$db = getConnection();
					$stmt = $db->prepare($sql);
					$stmt->execute(); 
					$event_reminder = $stmt->fetchObject();
					$stmt->closeCursor(); $stmt = null; $db = null;
					
					$reminder_interval = 20;
					$reminder_span = "days";
					$reminder_number = 1;
					$reminder_datetime = date("Y-m-d H:i:s", strtotime($statute_limitation . " 08:00:00" . " - " . $reminder_interval . " " . $reminder_span));
					
					$sql = "UPDATE cse_reminder
					SET reminder_datetime = '" . $reminder_datetime . "',
					buffered = 'N'
					WHERE reminder_uuid = '" . $event_reminder->reminder_uuid . "'
					AND customer_id = " . $customer_id;
					
					$db = getConnection();
					$stmt = $db->prepare($sql);
					$stmt->execute(); 
					$stmt = null; $db = null;
					
					trackEvent("update", $inj_event->event_id);
				}
			}
		}
	}
}
function updatePersonalInjuryField() {
	session_write_close();
	$id = passed_var("id", "post");
	$fieldname = passed_var("fieldname", "post");
	$value = passed_var("value", "post");
	$customer_id = $_SESSION['user_customer_id'];
	
	$sql = "UPDATE cse_personal_injury
	SET `" . $fieldname . "` = :value
	WHERE personal_injury_id = :id
	AND customer_id = :customer_id";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		
		$stmt->bindParam("id", $id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("value",  $value);
		$stmt->execute();
		
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>$id)); 
		
		trackPersonalInjury("update", $id);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function updatePersonalInjury() {
	//die(print_r($_POST));
	//if ($_POST[0]) {
	//}
	$request = Slim::getInstance()->request();
	$arrFields = array();
	$arrSet = array();
	$table_name = "personal_injury";
	$table_id = "";
	$info = "";
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="case_id"){
			$case_id = $value;
			continue;
		}
		if ($fieldname=="billing_time") {
			continue;
		}
		if ($fieldname=="billing_time_dropdown") {
			continue;
		}
		if ($fieldname=="table_id") {
			$table_id = $value;
			continue;
		}
		if ($fieldname=="personal_injury_id") {
			continue;
		}
		if ($fieldname=="table_uuid") {
			$table_uuid = $value;
			continue;
		}
		
		//if ($fieldname=="dateandtime") {
		if (strpos($fieldname, "_date")!==false || strpos($fieldname, "date_")!==false) {
			if ($value!="") {
				$value = date("Y-m-d H:i:s", strtotime($value));
			} else {
				$value = "0000-00-00 00:00:00";
			}
		}
		
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "`" . $fieldname . "` = '" . addslashes($value) . "'";
	}	
	
	//where
	$where_clause = "= '" . $table_id . "'";
	$where_clause = "`" . $table_name . "_id`" . $where_clause . "
	AND `customer_id` = " . $_SESSION['user_customer_id'];
	
	//actual query
	$sql = "UPDATE `cse_" . $table_name . "`
	SET " . implode(",
	", $arrSet) . "
	WHERE " . $where_clause;
	
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		echo json_encode(array("success"=>true, "id"=>$table_id));
		$stmt = null; $db = null;	
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function addRepair() {
	$table_name = "personal_injury";
	//die(print_r($_POST));
	$case_id = passed_var("case_id", "post");
	$table_id = passed_var("table_id", "post");
	$representing = passed_var("representing", "post");
	$repair_info = $_POST;
	$table_name = "personal_injury";
	$customer_id = $_SESSION['user_customer_id'];
	
	//where
	$where_clause = "= :table_id";
	$where_clause = "`" . $table_name . "_id`" . $where_clause . "
	AND `customer_id` = :customer_id";
	
	try {
		
		//first look up current info
		$sql = "SELECT repair_info
		FROM cse_personal_injury
		WHERE " . $where_clause;
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("table_id", $table_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$pi = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		$arrRepair = array();
		if ($pi->repair_info=="") {
			$arrRepair["plaintiff"] = "";
			$arrRepair["defendant"] = "";
			//die(print_r($arrRepair));
			$arrRepair[$representing] = $repair_info;	
			$repair_json = json_encode($arrRepair);
		} else {
			$repair_data = $pi->repair_info;
			$arrRepair = json_decode($repair_data);
			$repair_info = (object)$_POST;
			$arrRepair->{$representing} = $repair_info;
			$repair_json = json_encode($arrRepair);
		}
		//actual query
		$sql = "UPDATE `cse_" . $table_name . "`
		SET repair_info = :repair_info
		WHERE " . $where_clause;
	
	//die($sql);
	
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("table_id", $table_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("repair_info", $repair_json);
		$stmt->execute();
		
		echo json_encode(array("success"=>true, "id"=>$table_id));
		$stmt = null; $db = null;	
		
		trackPersonalInjury("update", $table_id, $case_id) ;
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function addRental() {
	$table_name = "personal_injury";
	//die(print_r($_POST));
	$case_id = passed_var("case_id", "post");
	$table_id = passed_var("table_id", "post");
	$representing = passed_var("representing", "post");
	$rental_info = $_POST;
	$table_name = "personal_injury";
	$customer_id = $_SESSION['user_customer_id'];
	
	//where
	$where_clause = "= :table_id";
	$where_clause = "`" . $table_name . "_id`" . $where_clause . "
	AND `customer_id` = :customer_id";
	
	try {
		
		//first look up current info
		$sql = "SELECT rental_info
		FROM cse_personal_injury
		WHERE " . $where_clause;
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("table_id", $table_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$pi = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		$arrRental = array();
		if ($pi->rental_info=="") {
			$arrRental["plaintiff"] = "";
			$arrRental["defendant"] = "";
			//die(print_r($arrRental));
			$arrRental[$representing] = $rental_info;	
			$rental_json = json_encode($arrRental);
		} else {
			$rental_data = $pi->rental_info;
			$arrRental = json_decode($rental_data);
			$rental_info = (object)$_POST;
			$arrRental->{$representing} = $rental_info;
			$rental_json = json_encode($arrRental);
		}
		//actual query
		$sql = "UPDATE `cse_" . $table_name . "`
		SET rental_info = :rental_info
		WHERE " . $where_clause;
	
	//die($sql);
	
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("table_id", $table_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("rental_info", $rental_json);
		$stmt->execute();
		
		echo json_encode(array("success"=>true, "id"=>$table_id));
		$stmt = null; $db = null;	
		
		trackPersonalInjury("update", $table_id, $case_id) ;
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function trackPersonalInjury($operation, $personal_injury_id, $case_id) {
	$sql = "INSERT INTO cse_personal_injury_track (`user_uuid`, `user_logon`, `operation`, `personal_injury_id`, `personal_injury_uuid`, `personal_injury_date`, `statute_limitation`, `statute_interval`, `loss_date`, `personal_injury_description`, `personal_injury_info`, `personal_injury_details`, `personal_injury_other_details`, `deleted`, `customer_id`, `case_id`)
	SELECT '" . $_SESSION['user_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $operation . "', `personal_injury_id`, `personal_injury_uuid`, `personal_injury_date`, `statute_limitation`, `statute_interval`, `loss_date`, `personal_injury_description`, `personal_injury_info`, `personal_injury_details`, `personal_injury_other_details`, `deleted`, `customer_id`, `case_id`
	FROM cse_personal_injury
	WHERE 1
	AND personal_injury_id = :personal_injury_id
	AND customer_id = :customer_id
	LIMIT 0, 1";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("personal_injury_id", $personal_injury_id);
		$stmt->bindParam("customer_id", $_SESSION['user_customer_id']);
		$stmt->execute();
		$new_id = $db->lastInsertId();
		
		//new the case_uuid
		$kase = getKaseInfo($case_id);
		$case_uuid = $kase->uuid;
		$case_id = $kase->id;
		
		$activity_category = "Personal Injury";
		switch($operation){
			case "insert":
				$operation .= "ed";
				break;
			case "update":
				$operation .= "d";
				break;
			case "delete":
				$operation .= "d";
				break;
		}
		$activity_uuid = uniqid("KS", false);
		$activity = "Personal Injury was " . $operation . "  by " . $_SESSION['user_name'];
		
		$billing_time = 0;
		if (isset($_POST["billing_time"])) {
			$billing_time = passed_var("billing_time", "post");
		}
		recordActivity($operation, $activity, $case_uuid, $new_id, $activity_category, $billing_time);
		
		//recordActivity($operation, $activity, $case_uuid, $new_id, $activity_category);
	} catch(PDOException $e) {
		echo $sql . "\r\n\r\n";
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function weeklyReportNewPICases() {
	$arrLastWeek = lastWeekDays();
	
	//die(print_r($arrLastWeek));
	$first_day = $arrLastWeek["last_week_start"];
	$last_day = $arrLastWeek["last_week_end"];
	
	$source = passed_var("source", "post");
	if ($source != "") {
		$source = "`ikase_" . $source . "`.";
	} else {
		$source = "`ikase`.";
	}
	$customer_id = passed_var("customer_id", "post");
	
	//die(print_r( $arrLastWeek));
	/*
	$sql = "SELECT ccase.case_id, ccase.case_name, ccase.case_type, ccase.injury_type, time_stamp, 
	cpi.personal_injury_date, cpi.statute_limitation, cpi.statute_interval, cpi.personal_injury_description
	FROM " . $source . "cse_personal_injury_track pit
	INNER JOIN " . $source . "cse_personal_injury cpi
	ON pit.personal_injury_id = cpi.personal_injury_id
	INNER JOIN " . $source . "cse_case ccase
	ON pit.case_id = ccase.case_id
	WHERE operation = 'insert'
	AND time_stamp BETWEEN '$first_day' AND '$last_day'
	AND ccase.customer_id = :customer_id
	AND ccase.deleted = 'N'";
	*/
	$sql = "SELECT 
	ccase.case_id, ccase.case_name, IF(ccase.case_number='', ccase.file_number, '') case_number, 
	ccase.case_type, ccase.injury_type, time_stamp, 
	IFNULL(cpi.personal_injury_date, '') personal_injury_date, 
	IFNULL(cpi.statute_limitation, '') statute_limitation, 
	IFNULL(cpi.statute_interval, '') statute_interval, 
	IFNULL(cpi.personal_injury_description, '') personal_injury_description
	
	FROM " . $source . "cse_case_track pit
	
	INNER JOIN " . $source . "cse_case ccase
	ON pit.case_id = ccase.case_id
		
	LEFT OUTER JOIN " . $source . "cse_personal_injury cpi
	ON pit.case_id = cpi.case_id
	   
	WHERE pit.operation = 'insert'
	AND pit.case_type NOT LIKE 'WC%'
	AND pit.time_stamp BETWEEN :first_day AND :last_day
	AND pit.customer_id = :customer_id
	AND ccase.deleted = 'N'";
	//print_r($arrLastWeek);
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("first_day", $first_day);
		$stmt->bindParam("last_day", $last_day);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$pi_cases = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		$arrRows = array();
		//die(print_r($pi_cases));
		foreach($pi_cases as $case) {
			if ($case->statute_limitation!="" && $case->statute_limitation!="0000-00-00") {
				$statute = date("m/d/Y", strtotime($case->statute_limitation));
			} else {
				$statute = "";
			}
			$injury_type = $case->injury_type;
			$arrInjuryType = explode("|", $injury_type);
			$display_injury_type = $arrInjuryType[0];
			switch($display_injury_type) {
				case "carpass":
					$display_injury_type = "Car Accident";
					break;
				case "general":
					$display_injury_type = "General";
					break;
				case "slipandfall":
					$display_injury_type = "Slip and Fall";
					break;
				case "dogbite":
					$display_injury_type = "Dog Bite";
					break;
				case "disability":
					$display_injury_type = "Disability";
					break;
			}
			$representing = "";
			if (count($arrInjuryType)==2) {
				$representing = " - " . $arrInjuryType[1];
			}
			if ($case->personal_injury_date!="" && $case->personal_injury_date!="0000-00-00") {
				$personal_injury_date = date("m/d/Y", strtotime($case->personal_injury_date));
			} else {
				$personal_injury_date = "";
			}
			$row = "<tr><td align='left' valign='top'>" . 
					$case->case_name . 
				"</td><td align='left' valign='top'>" . 
					$case->case_number . 
				"</td><td align='left' valign='top'>" . 
					$case->case_type . " " . $display_injury_type . $representing .
				"</td><td align='left' valign='top'>" . 
					$personal_injury_date . 
				"</td><td align='left' valign='top'>" . 
					$statute . 
				"</td></tr><tr><td align='left' valign='top' colspan='5'><div style='font-weight:bold'>Description:</div>" . 
					$case->personal_injury_description . 
				"</td></tr><tr><td align='left' valign='top' colspan='5'><hr /></td></tr>";
			$arrRows[] = $row;
		}
		//die(print_r($arrRows));
		$output = '';
		if (count($arrRows) > 0) {
			$output = '<div style="font-size: 1.2em;font-weight: bold;">New PI Cases ' . date("m/d/Y", strtotime($first_day)) . ' - ' . date("m/d/Y", strtotime($last_day)) . '</div><table cellpadding="2" cellspacing="2" width="100%"><thead><tr>
					<th align="left" valign="top">Case</th><th align="left" valign="top">Case #</th><th align="left" valign="top">&nbsp;</th><th align="left" valign="top">DOL</th><th align="left" valign="top">SOL</th></tr></thead><tbody>' . implode("", $arrRows) . '</tbody></table>';
		} else {
			$output = '<div style="font-size: 1.2em;font-weight: bold;">There were no new PI Cases ' . date("m/d/Y", strtotime($first_day)) . ' - ' . date("m/d/Y", strtotime($last_day)) . '</div>';
		}
		
		die($first_day . "|" . $last_day . "|" . $output);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
?>