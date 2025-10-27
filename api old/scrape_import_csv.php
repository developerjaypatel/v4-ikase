<?php
//import csv contents as events in ikase

error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);

die(print_r($_SERVER));

//die(date("Y-m-d H:i:s", strtotime("Jun 15, 2017 1:30 PM")));
include("connection.php");

function getKaseInfo($id, $customer_id, $db_name) {
	
	$sql = "SELECT ccase.case_id id, ccase.case_uuid uuid, ccase.lien_filed, ccase.special_instructions,ccase.case_description, IF(ccase.case_number='UNKNOWN' AND ccase.file_number!='', '', ccase.case_number) case_number, ccase.file_number, ccase.cpointer,inj.adj_number,
			ccase.case_date, ccase.case_type, venue.venue_uuid, ccase.rating, ccase.medical, ccase.td, ccase.rehab,  ccase.edd, ccase.claims, ccase.injury_type, ccase.sub_in,
			
			venue_corporation.corporation_id venue_id, venue.venue_uuid, IF(venue.venue IS NULL, '', venue.venue) venue, venue_abbr, 
			venue_corporation.street venue_street, venue_corporation.city venue_city, 
			venue_corporation.state venue_state, venue_corporation.zip venue_zip,
			
			ccase.case_status, ccase.case_substatus, ccase.case_subsubstatus, ccase.submittedOn, ccase.supervising_attorney,
    ccase.attorney, ccase.worker, ccase.interpreter_needed, ccase.case_language `case_language`, 
			app.deleted applicant_deleted, app.person_id applicant_id, app.person_uuid applicant_uuid, app.salutation applicant_salutation, IFNULL(app.full_name, '') `full_name`, app.first_name, app.last_name, app.middle_name, app.`aka`, 
			app.dob, app.gender, app.ssn, app.full_address applicant_full_address, app.street applicant_street, app.suite applicant_suite, app.city applicant_city, app.state applicant_state, app.zip applicant_zip, app.phone applicant_phone, app.fax applicant_fax, app.cell_phone applicant_cell, app.age applicant_age,
			
			IFNULL(employer.`corporation_id`,-1) employer_id, employer.company_name employer, employer.full_name employer_full_name, employer.street employer_street, employer.city employer_city,
			employer.state employer_state, employer.zip employer_zip,
			
			IFNULL(defendant.`corporation_id`,-1) defendant_id, defendant.company_name defendant, defendant.full_name defendant_full_name, defendant.street defendant_street, defendant.city defendant_city,
			defendant.state defendant_state, defendant.zip defendant_zip,
			
			CONCAT(app.first_name,' ', IF(app.middle_name='','',CONCAT(app.middle_name,' ')),app.last_name,' vs ', IFNULL(employer.`company_name`, '')) `name`, ccase.case_name, 
			
			IFNULL(att.nickname, '') as attorney_name, 
			IFNULL(att.user_name, '') as attorney_full_name, 
			IFNULL(att.user_email, '') as attorney_email, 
			IFNULL(user.nickname, '') as worker_name, IFNULL(user.user_name, '') as worker_full_name, IFNULL(user.user_email, '') as worker_email,
			IFNULL(lien.lien_id, -1) lien_id, 
			IFNULL(settlement.settlement_id, -1) settlement_id,
			IF (cif.fee_uuid IS NOT NULL, '1', '-1') fee_id,
			job.job_id worker_job_id, job.job_uuid worker_job_uuid, if(job.job IS NULL, '', job.job) worker_job
			
			FROM " . $db_name . "cse_case ccase ";

			$sql .= " 
			LEFT OUTER JOIN " . $db_name . "cse_case_person ccapp ON (ccase.case_uuid = ccapp.case_uuid AND ccapp.deleted = 'N')
			LEFT OUTER JOIN ";
if (($customer_id==1033)) { 
	$sql .= "(" . SQL_PERSONX . ")";
} else {
	$sql .= "" . $db_name . "cse_person";
}
$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN " . $db_name . "`cse_case_venue` cvenue
			ON (ccase.case_uuid = cvenue.case_uuid AND cvenue.deleted = 'N')
			LEFT OUTER JOIN " . $db_name . "`cse_venue` venue
			ON cvenue.venue_uuid = venue.venue_uuid
			
			LEFT OUTER JOIN " . $db_name . "`cse_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN " . $db_name . "`cse_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			
			LEFT OUTER JOIN " . $db_name . "`cse_case_corporation` dcorp
			ON (ccase.case_uuid = dcorp.case_uuid AND ccorp.attribute = 'defendant' AND dcorp.deleted = 'N')
			LEFT OUTER JOIN " . $db_name . "`cse_corporation` defendant
			ON dcorp.corporation_uuid = defendant.corporation_uuid
			
			LEFT OUTER JOIN " . $db_name . "`cse_case_corporation` ccorp_venue
			ON (ccase.case_uuid = ccorp_venue.case_uuid AND ccorp_venue.attribute = 'venue' AND ccorp_venue.deleted = 'N')
			LEFT OUTER JOIN " . $db_name . "`cse_corporation` venue_corporation
			ON ccorp_venue.corporation_uuid = venue_corporation.corporation_uuid
			LEFT OUTER JOIN " . $db_name . "`cse_case_injury` cinj
			ON ccase.case_uuid = cinj.case_uuid
			LEFT OUTER JOIN " . $db_name . "`cse_injury` inj
			ON cinj.injury_uuid = inj.injury_uuid
			LEFT OUTER JOIN " . $db_name . "`cse_injury_lien` cil
			ON inj.injury_uuid = cil.injury_uuid
			LEFT OUTER JOIN " . $db_name . "`cse_injury_fee` cif 
			ON inj.injury_uuid = cif.injury_uuid AND cif.deleted = 'N'
			LEFT OUTER JOIN " . $db_name . "`cse_lien` lien
			ON cil.lien_uuid = lien.lien_uuid
			LEFT OUTER JOIN " . $db_name . "`cse_injury_settlement` cis
			ON inj.injury_uuid = cis.injury_uuid AND cis.deleted = 'N'  AND cis.`attribute` = 'main'
			LEFT OUTER JOIN " . $db_name . "`cse_settlement` settlement
			ON cis.settlement_uuid = settlement.settlement_uuid
			
			LEFT OUTER JOIN ikase.`cse_user` att
			ON ccase.attorney = att.user_id
			LEFT OUTER JOIN ikase.`cse_user` user
			ON ccase.worker = user.user_id
			
			LEFT OUTER JOIN ikase.`cse_user_job` cjob
			ON (user.user_uuid = cjob.user_uuid AND cjob.deleted = 'N')
			LEFT OUTER JOIN ikase.`cse_job` job
			ON cjob.job_uuid = job.job_uuid
			
			where ccase.case_id='" . $id . "'
			AND ccase.customer_id = '" . $customer_id . "'";
	
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->bindParam("customer_id", $customer_id);
		
		$stmt->execute();
		$kase = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;
		if ($kase->case_name != "") {
			$kase->name = $kase->case_name;
		}
		if ($kase->case_number != "" && $kase->file_number=="") {
			$kase->file_number = $kase->case_number;
			$kase->case_number = "";
		}
		//die(print_r($kase));
		
        return $kase;

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function addEmailMessage($return, $customer_id, $case_uuid, $messageId, $messageSubject, $message_body, $user_id, $user_uuid, $destination) {
	if (!is_numeric($customer_id)) {
		die("no id");
	}
	
	$return = str_replace("`", "", $return);
	//get the user_uuid
	try {				
		$message_date = date("Y-m-d H:i:s");
		$message_body = addslashes($message_body);
		$table_uuid = $messageId;
		$thread_uuid = $messageId;
	 	$sender = "system";
		$subject = $messageSubject;
		$snippet = "";
		$attachments = "";
		
		//insert thread
		$db= getConnection();
		
	 	$sql = "INSERT INTO `" . $return . "`.cse_thread (`thread_uuid`, `dateandtime`, `from`, `subject`, `customer_id`) 
				SELECT '". $thread_uuid . "', '" . date("Y-m-d H:i:s") . "', '" . $sender . "', '" . addslashes($subject) . "', '" . $customer_id . "'
				FROM dual
				WHERE NOT EXISTS (
					SELECT * 
					FROM `" . $return . "`.cse_thread 
					WHERE thread_uuid = '" . $thread_uuid . "'
				)";
		//die($sql);
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		$stmt = null; $db = null;
		
		//message
		$sql = "INSERT INTO `" . $return . "`.`cse_message` (`message_uuid`, `message_type`, `message`, `subject`, 
		`snippet`, `dateandtime`, `from`, `message_to`, `attachments`, `customer_id`) ";
		$sql .= " VALUES(:messageId, 'email', :messageBody, :messageSubject, :messageSnippet, :messageDate, :messageSender, :destination, :attachments, :customer_id)";
		//echo $sql . "\r\n";
		$db = getConnection();
		$stmt = $db->prepare($sql);
		//die("Tab:" . $table_uuid);
		$stmt->bindParam("messageId", $table_uuid);
		$stmt->bindParam("messageBody", $message_body);
		$stmt->bindParam("messageSubject", $subject);
		$stmt->bindParam("messageSnippet", $snippet);
		$stmt->bindParam("messageDate", $message_date);
		$stmt->bindParam("messageSender", $sender);
		//$stmt->bindParam("destination", passed_var("destination", "post"));
		$stmt->bindParam("destination", $destination);
		$stmt->bindParam("attachments", $attachments);
		$stmt->bindParam("customer_id", $customer_id);
		
		//die(print_r($stmt));
		
		$stmt->execute();
		$new_id = $db->lastInsertId();
		$stmt = null; $db = null;
		
		//attach to thread
		$thread_message_uuid = uniqid("TD", false);
		
		$last_updated_date = date("Y-m-d H:i:s");
		$sql = "INSERT INTO `" . $return . "`.cse_thread_message (`thread_message_uuid`, `thread_uuid`, `message_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $thread_message_uuid  ."', '" . $thread_uuid . "', '" . $table_uuid . "', 'main', '" . $last_updated_date . "', '" . $user_id . "', '" . $customer_id . "')";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$stmt = null; $db = null;
		
		//attach to user
		$message_user_uuid = uniqid("MU", false);
		$sql = "INSERT INTO `" . $return . "`.cse_message_user (`message_user_uuid`, `message_uuid`, `user_uuid`, `type`, `last_updated_date`, `last_update_user`, `customer_id`, `thread_uuid`)
		VALUES ('" . $message_user_uuid  ."', '" . $table_uuid . "', '" . $user_uuid. "', 'to', '" . $last_updated_date . "', 'system', '" . $customer_id . "', '". $thread_uuid . "')";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$stmt = null; $db = null;
		
		$arrFields = array();
		$arrSet = array();
		
		$arrFields[] = "`note`";
		$arrSet[] = "'" . $message_body . "'";
		$arrFields[] = "`title`";
		$arrSet[] = "'" . $messageSubject . "'";
		$arrFields[] = "`subject`";
		$arrSet[] = "'" . $messageSubject . "'";
		$arrFields[] = "`attachments`";
		$arrSet[] = "''";
		
		$notes_uuid = uniqid("CN", false);
		//combine 
		$sql_note = "INSERT INTO `" . $return . "`.`cse_notes` (`customer_id`, `entered_by`, `notes_uuid`, " . implode(",", $arrFields) . ") 
				VALUES('" . $customer_id . "', 'system', '" . $notes_uuid . "', " . implode(",", $arrSet) . ")";
		//echo $sql_note . "\r\n";	
		
		$db = getConnection();
		$stmt = $db->prepare($sql_note);  
		$stmt->execute();
		$stmt = null; $db = null;
		
		$sql_note = "INSERT INTO `" . $return . "`.cse_case_notes (`case_notes_uuid`, `case_uuid`, `notes_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $notes_uuid  ."', '" . $case_uuid . "', '" . $notes_uuid . "', 'court_calendar', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
		
		$db = getConnection();
		$stmt = $db->prepare($sql_note);  
		$stmt->execute();
		$stmt = null; $db = null;
		
		//activity		
		$activity_uuid = uniqid("KS", false);
		$activity_category = "event";
		$billing_time = 0;
		$activity = $message_body;
		$operation = "insert";
		recordActivity($return, $customer_id, $operation, $activity, $case_uuid, $new_id, $activity_category, $billing_time);
		
		return json_encode(array("sql"=>$sql, "success"=>true, "id"=>$new_id));
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function recordActivity($return, $customer_id, $operation, $activity, $case_uuid, $track_id, $category = "", $billing_time = 0) {
	try {
		$db = getConnection();
		
		$activity_uuid = uniqid("KS", false);
		//fractions of an hour
		if ($billing_time > 0) {
			$billing_time = $billing_time / 60;
		}
		if ($billing_time == "") { 
			$billing_time = "0.00";
		}
		$sql = "INSERT INTO `" . $return . "`.cse_activity (`activity_uuid`, `activity`, `hours`, `activity_category`, `activity_user_id`, `customer_id`)
		VALUES ('" . $activity_uuid . "', '" . addslashes($activity) . "', '" . $billing_time . "', '" . addslashes($category) . "', '-1', " . $customer_id . ")";
		//echo $sql . "\r\n";
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		//if we passed a valid case
		if ($case_uuid!="") {
			$last_updated_date = date("Y-m-d H:i:s");
			$case_activity_uuid = uniqid("KA", false);
			$attribute = "main";
			if ($category != "") {
				$attribute = $category;
			}
			$sql = "INSERT INTO `" . $return . "`.cse_case_activity (`case_activity_uuid`, `case_uuid`, `activity_uuid`, `attribute`, `case_track_id`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_activity_uuid . "', '" . $case_uuid . "', '" . $activity_uuid . "', '" . $attribute . "', " . $track_id . ", '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
			//echo $sql . "\r\n";
			$stmt = $db->prepare($sql);  
			$stmt->execute();
		}
		$stmt = null; $db = null;
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .', "sql":'. $sql .'}}'; 
	}
}
//now let's deal with the data
$sql = "SELECT cer.eams_ref_number, cer.firm_name, cus.customer_id cus_id, cus.data_source, ccc.* 
FROM ikase.cse_eams_reps cer
INNER JOIN ikase.cse_courtcalendar ccc
ON cer.firm_name = ccc.applicant_law_firm
INNER JOIN ikase.cse_customer cus
ON cer.eams_ref_number = cus.eams_no
WHERE ccc.customer_id = 0
ORDER BY cer.firm_name, ccc.hearing_time ASC";

try {
	$db = getConnection();
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $calendars = $stmt->fetchAll(PDO::FETCH_OBJ);
    $stmt->closeCursor();    
    $db = null; $stmt = null;
	
	foreach($calendars as $calendar) {
		//die(print_r($calendar));
		$data_source = $calendar->data_source;
		$customer_id = $calendar->cus_id;
		
		$db_name = "`ikase`";
		if ($data_source!="") {
			$db_name = "`ikase_" . $data_source . "`";
		}
		
		//let's get all the details
		$sql = "SELECT ccc.*, 
		ccase.case_id, ccase.case_uuid, IF(ccase.case_number='', ccase.file_number, ccase.case_number) case_number
		FROM ikase.cse_eams_reps cer
		INNER JOIN ikase.cse_courtcalendar ccc
		ON cer.firm_name = ccc.applicant_law_firm
		INNER JOIN ikase.cse_customer cus
		ON cer.eams_ref_number = cus.eams_no
		
		INNER JOIN " . $db_name . ".cse_injury ci
		ON ccc.case_number = ci.adj_number
		INNER JOIN " . $db_name . ".cse_case_injury cci
		ON ci.injury_uuid = cci.injury_uuid
		INNER JOIN " . $db_name . ".cse_case ccase
		ON cci.case_uuid = ccase.case_uuid
		
		WHERE ccc.courtcalendar_id = " . $calendar->courtcalendar_id;
	
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$entry = $stmt->fetchObject();
		$stmt->closeCursor();    
		$db = null; $stmt = null;
		
		//only if found
		$blnProceedImport = true;
		if (!is_object($entry)) {
			$blnProceedImport = false;
		} else {
			if ($entry->case_id == "") {
				$blnProceedImport = false;
			}
		}
		if (!$blnProceedImport) {
			//update the customer id and event_uuid
			$event_uuid = "no_case";
			$sql = "UPDATE `ikase`.`cse_courtcalendar` 
			SET `event_uuid`=:event_uuid, 
			`customer_id`=:customer_id
			WHERE `courtcalendar_id`=:courtcalendar_id";
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("event_uuid", $event_uuid);
			$stmt->bindParam("customer_id", $customer_id);
			$stmt->bindParam("courtcalendar_id", $calendar->courtcalendar_id);
			$stmt->execute();
			$db = null; $stmt = null;
			
			continue;
		}
		//echo $sql . "\r\n";
		//die(print_r($entry));
		//get the case worker
		$kase = getKaseInfo($entry->case_id, $customer_id, $db_name . ".");
		//die(print_r($kase));
		
		$user_id = "";
		$user_uuid = "";
		$kase_worker = $kase->worker;
		if ($kase_worker=="") {
			$kase_worker = $kase->attorney;
		}
		if (is_numeric($kase_worker)) {
			//get the user id
			$sql = "SELECT user_uuid, nickname
			FROM ikase.cse_user
			WHERE 1";		
			$sql .= " AND user_id = '" . $kase_worker . "'
			AND customer_id = '" . $customer_id . "'";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->execute();
			$user = $stmt->fetchObject();
			$stmt->closeCursor();    
			$db = null; $stmt = null;
			if (is_object($user)) {
				$user_id = $kase_worker;
				$user_uuid = $user->user_uuid;
				$kase_worker = $user->nickname;
			}
		} else {
			if ($kase_worker=="") {
				//get the user id
				$sql = "SELECT user_uuid, user_id
				FROM ikase.cse_user
				WHERE 1";		
				$sql .= " AND nickname = '" . $kase_worker . "'
				AND customer_id = '" . $customer_id . "'";
				
				$db = getConnection();
				$stmt = $db->prepare($sql);
				$stmt->execute();
				$user = $stmt->fetchObject();
				$stmt->closeCursor();    
				$db = null; $stmt = null;
				
				if (is_object($user)) {
					$user_id = $user->user_id;
					$user_uuid = $user->user_uuid;
				}
			}
		}
		
		//die($result);
		//enter a new event if there is no event for this case
		$sql = "SELECT eve.*
		FROM " . $db_name . ".cse_event eve
		INNER JOIN " . $db_name . ".cse_case_event cce
		ON eve.event_uuid = cce.event_uuid
		INNER JOIN " . $db_name . ".cse_case ccase
		ON cce.case_uuid = ccase.case_uuid
		WHERE eve.event_dateandtime = '" . $entry->hearing_time . "'
		AND ccase.case_id = '" . $entry->case_id . "'
		AND eve.customer_id = '" . $customer_id . "'
		AND eve.event_type = '" . $entry->hearing_type . "'";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$case_events = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor();    
		$db = null; $stmt = null;
		
		if (count($case_events)==0) {
			//insert a new event
			$sql = "INSERT INTO " . $db_name . ".cse_event (`event_uuid`, `event_dateandtime`, `event_type`, `judge`, `full_address`, `customer_id`, `event_description`, `event_title`)";
			$event_uuid = uniqid("EV");
			$sql .= "
			VALUES ('" . $event_uuid . "', '" . $entry->hearing_time . "', '" . $entry->hearing_type . "', '" . $entry->judge_name . "', '" . $entry->hearing_location . "', '" . $customer_id . "', '" . $entry->hearing_type . "\r\n\r\nAdded via automatic Court Calendar Update on " . date("m/d/y") . "', '" . $entry->hearing_type . "')";
			//echo $sql . "\r\n";
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->execute();
			$db = null; $stmt = null;
			
			//attach it to the kase
			$attribute = "court_calendar";
			$last_updated_date = date("Y-m-d H:i:s");
			$sql = "INSERT INTO " . $db_name . ".cse_case_event 
			(`case_event_uuid`, `case_uuid`, `event_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `deleted`, `customer_id`)
			VALUES ('" . $event_uuid . "', '" . $entry->case_uuid . "', '" . $event_uuid . "', '" . $attribute . "', '" . $last_updated_date . "', 'system', 'N', '" . $customer_id . "')";
			//echo $sql . "\r\n";
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->execute();
			$db = null; $stmt = null;
			/*
			//send a notification to worker
			$message_uuid = uniqid("CR");
			$body = $entry->hearing_type . " was added automatically to the case calendar for <a href='v8.php?n=#kases/" . $entry->case_id . "' target='_blank' class='white_text'>" . $entry->case_number . "</a> from the Court Calendar on " . date("m/d/y g:iA", strtotime($entry->hearing_time));
			//notify the user
			$result = addEmailMessage($db_name, $customer_id, $entry->case_uuid, $message_uuid, "Hearing Automatically Added from Court Calendar", $body, $user_id, $user_uuid, $kase_worker);
			*/
			//update the customer_id and event_uuid
			$sql = "UPDATE `ikase`.`cse_courtcalendar` 
			SET `event_uuid`=:event_uuid, 
			`case_uuid`=:case_uuid,
			`customer_id`=:customer_id
			WHERE `courtcalendar_id`=:courtcalendar_id";
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("event_uuid", $event_uuid);
			$stmt->bindParam("case_uuid", $entry->case_uuid);
			$stmt->bindParam("customer_id", $customer_id);
			$stmt->bindParam("courtcalendar_id", $calendar->courtcalendar_id);
			$stmt->execute();
			$db = null; $stmt = null;
			*/
			die("done");
		} else {
			//already in?
			continue;
		}
	}
} catch(PDOException $e) {
	echo '{"error":{"text":'. $e->getMessage() .'}}'; 
}
?>