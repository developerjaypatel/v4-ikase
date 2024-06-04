<?php
$app->group('', function (\Slim\Routing\RouteCollectorProxy $app) {
	$app->group('/reminders', function (\Slim\Routing\RouteCollectorProxy $app) {
		$app->get('/{event_id}', 'getReminders');
		$app->post('/delete', 'deleteReminder');
		$app->post('/verify', 'verifyReminder');
		$app->post('/add', 'addReminder');
		$app->post('/update', 'updateReminder');
		$app->post('/newtime', 'getReminderDateTime');
	})->add(\Api\Middleware\Authorize::class);
	
	$app->post('/statute', 'getStatuteReminders');
	$app->post('/events', 'getEventReminders');
	$app->post('/customerinvoices', 'getCustomerInvoiceReminders');
});
$app->get('/reminder/{id}', 'getReminder')->add(\Api\Middleware\Authorize::class);

function getReminderDateTime() {
	$event_dateandtime = passed_var("date", "post");
	$reminder_interval = passed_var("interval", "post");
	$reminder_span = passed_var("span", "post");
	
	$reminder_datetime = date("D M jS, Y g:iA", strtotime($event_dateandtime . " - " . $reminder_interval . " " . $reminder_span));
	
	echo $reminder_datetime;
}

/**
 * THIS IS GOING TO BE A CRON JOB RUNNING EVERY MINUTE
 */
function getEventReminders () {
	$customer_id = $_POST["customer_id"];
	$authorize_key = $_POST["authorize_key"];
	if (!is_numeric($customer_id)) {
		die("no go");
	}
	if ($authorize_key!="ikase.org") {
		die("no go");
	}
	$_SESSION['user_customer_id'] = $customer_id;
	$_SESSION['user_id'] = "system";
	
	$sql = "SELECT 
	cet.user_uuid from_user_uuid,
    rem.*,
    ev.*,
    IFNULL(cc.case_id, -1) case_id,
	IFNULL(cc.case_uuid, '') case_uuid,
    IFNULL(inj.injury_id, -1) injury_id,
	IFNULL(inj.injury_uuid, '') injury_uuid
FROM
    cse_reminder rem
        INNER JOIN
    cse_event_reminder erem ON rem.reminder_uuid = erem.reminder_uuid
        INNER JOIN
    cse_event ev ON erem.event_uuid = ev.event_uuid
		INNER JOIN cse_event_track cet
        ON ev.event_id = cet.event_id AND cet.operation = 'insert'
		
        LEFT OUTER JOIN
    cse_case_event cce ON ev.event_uuid = cce.event_uuid
        LEFT OUTER JOIN
    cse_case cc ON cce.case_uuid = cc.case_uuid
    	LEFT OUTER JOIN
    cse_case_event cce ON ev.event_uuid = cce.event_uuid
        LEFT OUTER JOIN
    cse_case cc ON cce.case_uuid = cc.case_uuid AND cc.case_status NOT LIKE '%close%'
    	LEFT OUTER JOIN
    cse_injury_event cie ON ev.event_uuid = cie.event_uuid
        LEFT OUTER JOIN
    cse_injury inj ON cie.injury_uuid = inj.injury_uuid AND inj.deleted = 'N'
    WHERE 1
    AND rem.reminder_datetime = '2019-02-19 08:00:00'
	AND rem.customer_id = " . $customer_id . "
	AND rem.buffered = 'N'";
	//AND rem.reminder_datetime = '" . date("Y-m-d H:i") . ":00'
	try {
		$reminders = DB::select($sql);
		//die(print_r($reminders));
		$message_to = "";
		foreach($reminders as $reminder) {
			if ($reminder->reminder_type == "interoffice" || $reminder->reminder_type == "email") {
				//if you are on the list of assignee, or if no assignee then assigner
				//receive an interoffice 
				
				//start with thread
				$thread_uuid = uniqid("TD", false);
				$message_uuid = uniqid("MS", false);
				$from = "system";
				
				$subject = "Event Reminder";
				if ($reminder->injury_id!="-1" && $reminder->case_id=="-1") {
					//must be a statute reminder
					$subject = "Statute Limitation Reminder";
				}
				$dateandtime = date("Y-m-d H:i:s");
				$message_type = "reminder";
				
				$arrUsers = array();
				if ($reminder->assignee!="") {
					$arrTo = explode(";", $reminder->assignee);
					$message_to = $reminder->assignee;
					//we need user uuids for the message user link
					
					foreach($arrTo as $to_index=>$to) {
						if (!is_numeric($to)) {
							$the_user = getUserByNickname($to);
						} else {
							$the_user = getUserByID($to);
						}
						//die($to . "\r\n" . print_r($the_user));
						$arrUsers[] = $the_user->uuid;
					}
				}
				if (count($arrTo) == 0) {
					$arrUsers[] =  $reminder->from_user_uuid;
					$message_to = $reminder->event_from;
				}
				if (count($arrUsers) == 0) {
					//no destination
					continue;
				}
				
				//die(print_r($arrUsers));
				
				//pre-attach recipients via uuid
				$db = getConnection();
				attachRecipients('message', $message_uuid, $dateandtime, $arrUsers, 'to', $db, "N", $thread_uuid);
				
				
				$event_dateandtime = date("m/d/Y g:iA", strtotime($reminder->event_dateandtime));
				
				$message = "Reminder for Event <a id='" . $reminder->event_id . "_" . $reminder->case_id . "' class='edit_event' style='cursor:pointer'>" . $reminder->event_title;
				if ($reminder->event_type!="") {
					$message .= "[" . $reminder->event_type . "]";
				}
				$message .= "<a/> scheduled for <span style='font-weight:bold'>" . $event_dateandtime . "</span>";
				$priority = $reminder->event_priority;
				//die(print_r($arrUsers));
				
				$sql = "INSERT INTO `cse_thread` (`customer_id`, `dateandtime`, `thread_uuid`, `from`, `subject`) 
				VALUES('" . $customer_id . "', '" . $dateandtime . "','" . $thread_uuid . "', '" . $from . "', '" . $subject . "')";
				$stmt = DB::run($sql);
				
				$message_table_uuid = uniqid("KA", false);
				$last_updated_date = date("Y-m-d H:i:s");
				//now we have to attach the message to the thread
				$sql = "INSERT INTO cse_thread_message (`thread_message_uuid`, `thread_uuid`, `message_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $message_table_uuid  ."', '" . $thread_uuid . "', '" . $message_uuid . "', 'main', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
				
				$stmt = DB::run($sql);
				
				$sql = "INSERT INTO `cse_message`
				(`message_uuid`, `message_type`, `dateandtime`, `from`, `message_to`, `message`, `subject`, `priority`, `customer_id`)
				VALUES ('" . $message_uuid . "', '" . $message_type . "', '" . $dateandtime .  "', '" . $from . "', '" . $message_to . "', '" . addslashes($message) . "', '" . addslashes($subject) . "', '" . $priority . "', '" . $customer_id . "')";
				
				DB::run($sql);
	$message_id = DB::lastInsertId();
				
				if ($reminder->case_uuid!="") {
					//attach to reminder
					$case_message_uuid = uniqid("TD", false);
					$sql = "INSERT INTO cse_case_message (`case_message_uuid`, `case_uuid`, `message_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
					VALUES ('" . $case_message_uuid  ."', '" . $reminder->case_uuid . "', '" . $message_uuid . "', 'main', '" . $dateandtime . "', 'system', '" . $customer_id . "')";
					//echo $sql . "<br />";	
					$stmt = DB::run($sql);
				}
				//i need the system user_id
				$sql = "SELECT user_id
				FROM ikase.cse_user
				WHERE user_name = 'system'
				AND customer_id = " . $customer_id;
				
				$stmt = DB::run($sql);
				$system_user = $stmt->fetchObject();
				
				//attach the from
				$message_user_uuid = uniqid("TD", false);
				$sql = "INSERT INTO cse_message_user (`message_user_uuid`, `message_uuid`, `user_uuid`, `type`, `last_updated_date`, `last_update_user`, `customer_id`, `thread_uuid`, message_id, user_id)
				VALUES ('" . $message_user_uuid  ."', '" . $message_uuid . "', 'system', 'from', '" . $last_updated_date . "', 'system', '" . $customer_id . "', '". $thread_uuid . "','" . $message_id . "','" . $system_user->user_id . "')";
				$stmt = DB::run($sql);
				
				//mark reminder as buffered
				$sql = "UPDATE cse_reminder
				SET buffered = 'Y'
				WHERE reminder_id = " . $reminder->reminder_id;
				$stmt = DB::run($sql);
				
				//may need to send it
				if ($reminder->reminder_type=="email" && $the_user->user_email!="") {
					$email_address = $the_user->user_email;
					if (isValidEmail($email_address)) {
						$arrRecipients[] = $email_address;
						$arrEmailTo = $arrRecipients;
						$arrEmailCc = array();
						$arrEmailBcc = array();
						$db = getConnection();
						$blnSent = sendEmail($message_uuid, "donotreply@ikase.org", "iKase Reminders", $arrRecipients, $arrEmailTo, $arrEmailCc, $arrEmailBcc, $subject, $message, $db, "");
					}
				}
			}
		}
		echo json_encode(array("success"=>true, "recipients"=>$message_to));
	} catch(PDOException $e) {
		$error = array("error"=> array("sql"=>$sql, "text"=>$e->getMessage()));
        echo json_encode($error);
	}
}
function getCustomerInvoiceReminders () {
	//THIS IS GOING TO BE A CRON JOB RUNNING EVERY day
	$authorize_key = passed_var("authorize_key", "post");
	$reminder_date = passed_var("reminder_date", "post");
	$reminder_date = date("Y-m-d", strtotime($reminder_date));
	
	if ($authorize_key!="ikase.org") {
		die("no go");
	}
	$_SESSION['user_customer_id'] = "-2";
	$_SESSION['user_id'] = "system";
	
	session_write_close();
	
	$sql = "SELECT 
    rem.*,
    inv.*,
	cus.cus_name, cus_email, cus.customer_id cus_id
FROM
    `ikase`.`cse_reminder` `rem`
        INNER JOIN
    `ikase`.`cse_invoice_reminder` `irem` ON rem.reminder_uuid = irem.reminder_uuid
        INNER JOIN
    `ikase`.`cse_invoice` `inv` ON irem.invoice_uuid = inv.invoice_uuid
    	INNER JOIN
    `ikase`.`cse_customer` `cus` ON inv.customer_id = cus.customer_id
    WHERE 1
    AND CAST(rem.reminder_datetime AS DATE) = '" . $reminder_date . "'
	AND rem.buffered = 'N'
	";
	//AND inv.total - inv.payments > 0
	try {
		$reminders = DB::select($sql);
		//die(print_r($reminders));
		$message_to = "";
		foreach($reminders as $reminder) {
			if ($reminder->reminder_type == "interoffice" || $reminder->reminder_type == "email") {
				//if you are on the list of assignee, or if no assignee then assigner
				//receive an interoffice 
				
				//start with thread
				$thread_uuid = uniqid("TD", false);
				$message_uuid = uniqid("MS", false);
				$from = "system";
				$subject = "iKase Customer Invoice Renewal Reminder for " . $reminder->cus_name;
				$dateandtime = date("Y-m-d H:i:s");
				$message_type = "reminder";
				
				$arrUsers = array();
				$arrUsers[] = 'dakfjaalkdfj';
				$message_to = "nick@kustomweb.com";
				
				//pre-attach recipients via uuid
				$db = getConnection();
				attachRecipients('message', $message_uuid, $dateandtime, $arrUsers, 'to', $db, "N", $thread_uuid);
				
				$event_dateandtime = date("m/d/Y g:iA", strtotime($reminder->reminder_datetime));
				
				$message = "The last invoice  for " . $reminder->cus_name . " <a href='https://www.ikase.org/manage/customers/invoice.php?cus_id=" . $reminder->cus_id . "&invoice_id=" . $reminder->invoice_id . "'>" . $reminder->invoice_number . "</a> covered " . date("m/d/y", strtotime($reminder->start_date)) . " through " . date("m/d/y", strtotime($reminder->end_date));
				$message .= ".<br />";
				$message .= "<br />";
				$message .= "It is time to create and send the next invoice for this customer.";
				$priority = "high";
				//die(print_r($arrUsers));
				
				$sql = "INSERT INTO `ikase`.`cse_thread` (`customer_id`, `dateandtime`, `thread_uuid`, `from`, `subject`) 
				VALUES('" . $reminder->cus_id . "', '" . $dateandtime . "','" . $thread_uuid . "', '" . $from . "', '" . $subject . "')";
				$stmt = DB::run($sql);
				
				$message_table_uuid = uniqid("KA", false);
				$last_updated_date = date("Y-m-d H:i:s");
				//now we have to attach the message to the thread
				$sql = "INSERT INTO `ikase`.cse_thread_message (`thread_message_uuid`, `thread_uuid`, `message_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $message_table_uuid  ."', '" . $thread_uuid . "', '" . $message_uuid . "', 'main', '" . $last_updated_date . "', 'system', '" . $reminder->cus_id . "')";
				
				$stmt = DB::run($sql);
				
				$sql = "INSERT INTO `ikase`.`cse_message`
				(`message_uuid`, `message_type`, `dateandtime`, `from`, `message_to`, `message`, `subject`, `priority`, `customer_id`)
				VALUES ('" . $message_uuid . "', '" . $message_type . "', '" . $dateandtime .  "', '" . $from . "', '" . $message_to . "', '" . addslashes($message) . "', '" . addslashes($subject) . "', '" . $priority . "', '" . $reminder->cus_id . "')";
				
				DB::run($sql);
	$message_id = DB::lastInsertId();
				
				$reminder_message_uuid = uniqid("RM", false);
				//attach reminder to the message
				$sql = "INSERT INTO `ikase`.`cse_reminder_message` (`reminder_message_uuid`, `reminder_uuid`, `message_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $reminder_message_uuid  ."', '" . $reminder->reminder_uuid . "', '" . $message_uuid . "', 'main', '" . date("Y-m-d H:i:s") . "', 'system', '" . $reminder->cus_id . "')";
				//echo $sql . "\r\n";
				$stmt = DB::run($sql);	
				
				//i need the system user_id
				$sql = "SELECT user_id
				FROM ikase.cse_user
				WHERE user_name = 'system'
				AND customer_id = " . $customer_id;
				
				$stmt = DB::run($sql);
				$system_user = $stmt->fetchObject();
				
				//attach the from
				$message_user_uuid = uniqid("TD", false);
				$sql = "INSERT INTO `ikase`.cse_message_user (`message_user_uuid`, `message_uuid`, `user_uuid`, `type`, `last_updated_date`, `last_update_user`, `customer_id`, `thread_uuid`, message_id, user_id)
				VALUES ('" . $message_user_uuid  ."', '" . $message_uuid . "', 'system', 'from', '" . $last_updated_date . "', 'system', '" . $reminder->cus_id . "', '". $thread_uuid . "','" . $message_id . "','" . $system_user->user_id . "')";
				$stmt = DB::run($sql);
				
				//mark reminder as buffered
				$sql = "UPDATE `ikase`.cse_reminder
				SET buffered = 'Y'
				WHERE reminder_id = " . $reminder->reminder_id . "
				AND customer_id = '" . $reminder->cus_id . "'";
				$stmt = DB::run($sql);
				
				//may need to send it
				if ($reminder->reminder_type=="email" && $reminder->cus_email!="") {
					$email_address = $reminder->cus_email;
					$email_address = "nick@kustomweb.com";
					if (isValidEmail($email_address)) {
						$arrRecipients[] = $email_address;
						$arrEmailTo = $arrRecipients;
						$arrEmailCc = array();
						$arrEmailBcc = array();
						$db = getConnection();
						$blnSent = sendEmail($message_uuid, "donotreply@ikase.org", "iKase Invoice Renewal Reminder", $arrRecipients, $arrEmailTo, $arrEmailCc, $arrEmailBcc, $subject, $message, $db, "");
					}
				}
				
			}
		}
		emptyReminders();
		echo json_encode(array("success"=>true, "recipients"=>$message_to));
	} catch(PDOException $e) {
		$error = array("error"=> array("sql"=>$sql, "text"=>$e->getMessage()));
        echo json_encode($error);
	}
}
function getStatuteReminders() {
	//THIS IS GOING TO BE A CRON JOB RUNNING EVERY MINUTE
	
	$customer_id = $_POST["customer_id"];
	$authorize_key = $_POST["authorize_key"];
	if (!is_numeric($customer_id)) {
		die("no go");
	}
	if ($authorize_key!="ikase.org") {
		die("no go");
	}
	$_SESSION['user_customer_id'] = $customer_id;
	$_SESSION['user_id'] = "system";
	//get the datasource 
	$db = getConnection();
	//we need a batchscan_id to go through
	$batchscan_id = -1;
	include("customer_lookup.php");
	
	$sql = "SELECT cc.case_id, cc.case_number, inj.injury_id, inj.start_date, inj.end_date, inj.statute_limitation, 
	cci.case_uuid, cc.worker, cc.supervising_attorney, cc.attorney, cie.attribute, rem.*
	FROM cse_reminder rem
	INNER JOIN cse_event_reminder erem
	ON rem.reminder_uuid = erem.reminder_uuid
	INNER JOIN cse_event ev
	ON erem.event_uuid = ev.event_uuid
	INNER JOIN cse_injury_event cie
	ON ev.event_uuid = cie.event_uuid
	INNER JOIN cse_case_injury cci
	ON cie.injury_uuid = cci.injury_uuid
	INNER JOIN cse_injury inj
	ON cci.injury_uuid = inj.injury_uuid
	INNER JOIN cse_case cc
	ON cci.case_uuid = cc.case_uuid
	WHERE rem.reminder_datetime >= '" . date("Y-m-d H:i") . "'
	AND rem.customer_id = " . $customer_id . "
	AND rem.buffered = 'N'
	AND cc.case_status NOT LIKE '%close%'";
	
	try {
		$reminders = DB::select($sql);
		//die(print_r($reminders));
		
		foreach($reminders as $reminder) {
			if ($reminder->reminder_type == "interoffice") {
				//if you are on the list of worker, attorney, or supervising attorney				
				//receive an interoffice 
				
				//start with thread
				$thread_uuid = uniqid("TD", false);
				$message_uuid = uniqid("MS", false);
				$from = "system";
				$subject = "Statute of Limitation Expiring";
				$dateandtime = date("Y-m-d H:i:s");
				$message_type = "reminder";
				
				$arrTo = array();
				if ($reminder->worker!="") {
					$arrTo[] = $reminder->worker;
				}
				if ($reminder->supervising_attorney!="") {
					$arrTo[] = $reminder->supervising_attorney;
				}
				if ($reminder->attorney!="") {
					$arrTo[] = $reminder->attorney;
				}
				if (count($arrTo) == 0) {
					$sql = "SELECT nickname
					FROM `ikase`.cse_user 
					WHERE default_attorney = 'Y'
					AND customer_id = " . $customer_id;
						
					$db = getConnection();
					$stmt = $db->prepare($sql);
					$stmt->bindParam("id", $id);
					$stmt->execute();
					$default_atty = $stmt->fetchObject();
					
					if (is_object($default_atty)) {
						$arrTo[] = $default_atty->nickname;
					}
				}
				if (count($arrTo) == 0) {
					//no destination
					continue;
				}
				//we need user uuids for the message user link
				$arrUsers = array();
				foreach($arrTo as $to_index=>$to) {
					if (!is_numeric($to)) {
						$the_user = getUserByNickname($to);
					} else {
						$the_user = getUserByID($to);
					}
					$arrUsers[] = $the_user->uuid;
				}
				//die(print_r($arrUsers));
				
				//pre-attach recipients via uuid
				$db = getConnection();
				attachRecipients('message', $message_uuid, $dateandtime, $arrUsers, 'to', $db, "N", $thread_uuid);
				
				$message_to = implode(";", $arrTo);
				
				$doi = date("m/d/Y", strtotime($reminder->start_date));
				if ($reminder->end_date!="0000-00-00") {
					$doi .= " - " . date("m/d/Y", strtotime($reminder->end_date)) . " CT";
				}
				$message = "Statute of Limitation Expiration Date for DOI [" . $doi . "] is <span style='font-weight:bold'>" . date("m/d/Y", strtotime($reminder->statute_limitation)) . "</span>";
				$priority = "normal";
				//die(print_r($arrUsers));
				
				$sql = "INSERT INTO `cse_thread` (`customer_id`, `dateandtime`, `thread_uuid`, `from`, `subject`) 
				VALUES('" . $customer_id . "', '" . $dateandtime . "','" . $thread_uuid . "', '" . $from . "', '" . $subject . "')";
				$stmt = DB::run($sql);
				
				$message_table_uuid = uniqid("KA", false);
				$last_updated_date = date("Y-m-d H:i:s");
				//now we have to attach the message to the thread
				$sql = "INSERT INTO cse_thread_message (`thread_message_uuid`, `thread_uuid`, `message_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $message_table_uuid  ."', '" . $thread_uuid . "', '" . $message_uuid . "', 'main', '" . $last_updated_date . "', 'system', '" . $customer_id . "')";
				
				$stmt = DB::run($sql);
				
				$sql = "INSERT INTO `cse_message`
				(`message_uuid`, `message_type`, `dateandtime`, `from`, `message_to`, `message`, `subject`, `priority`, `customer_id`)
				VALUES ('" . $message_uuid . "', '" . $message_type . "', '" . $dateandtime .  "', '" . $from . "', '" . $message_to . "', '" . addslashes($message) . "', '" . addslashes($subject) . "', '" . $priority . "', '" . $customer_id . "')";
				
				DB::run($sql);
	$message_id = DB::lastInsertId();
				
				//attach to case
				$case_message_uuid = uniqid("TD", false);
				$sql = "INSERT INTO cse_case_message (`case_message_uuid`, `case_uuid`, `message_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $case_message_uuid  ."', '" . $reminder->case_uuid . "', '" . $message_uuid . "', 'main', '" . $dateandtime . "', 'system', '" . $customer_id . "')";
				//echo $sql . "<br />";	
				$stmt = DB::run($sql);
				
				//i need the system user_id
				$sql = "SELECT user_id
				FROM ikase.cse_user
				WHERE user_name = 'system'
				AND customer_id = " . $customer_id;
				
				$stmt = DB::run($sql);
				$system_user = $stmt->fetchObject();
				
				//attach the from
				$message_user_uuid = uniqid("TD", false);
				$sql = "INSERT INTO cse_message_user (`message_user_uuid`, `message_uuid`, `user_uuid`, `type`, `last_updated_date`, `last_update_user`, `customer_id`, `thread_uuid`, message_id, user_id)
				VALUES ('" . $message_user_uuid  ."', '" . $message_uuid . "', 'system', 'from', '" . $last_updated_date . "', 'system', '" . $customer_id . "', '". $thread_uuid . "','" . $message_id . "','" . $system_user->user_id . "')";
				$stmt = DB::run($sql);
				
				//mark reminder as buffered
				$sql = "UPDATE cse_reminder
				SET buffered = 'Y'
				WHERE reminder_id = " . $reminder->reminder_id;
				$stmt = DB::run($sql);
			}
		}
		echo json_encode(array("success"=>true, "recipients"=>$message_to));
	} catch(PDOException $e) {
		$error = array("error"=> array("sql"=>$sql, "text"=>$e->getMessage()));
        echo json_encode($error);
	}	
}
function getReminders($event_id) {
    $sql = "SELECT `reminder_type`, `reminder_interval`, `reminder_span`, `reminder_datetime`, 
	`verified`, csr.`deleted` 
	FROM `cse_reminder` csr
	INNER JOIN `cse_event_reminder` cer 
	ON csr.reminder_uuid = cer.reminder_uuid
	INNER JOIN `cse_event` cev
	ON cer.event_uuid = cev.event_uuid
	WHERE csr.deleted = 'N'
	AND cev.event_id = :event_id";

	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("event_id", $event_id);
		$stmt->execute();
		$reminders = $stmt->fetchAll(PDO::FETCH_OBJ);

        // Include support for JSONP requests
        echo json_encode($reminders);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function getReminder($id) {
    $sql = "SELECT `reminder_type`, `reminder_interval`, `reminder_span`, `reminder_datetime`, `verified`, `deleted` 
			FROM `reminder` 
			WHERE reminder_id=:id
			AND deleted = 'N'";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$reminder = $stmt->fetchObject();

        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($reminder);
        } else {
            echo $_GET['callback'] . '(' . json_encode($reminder) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function deleteReminder() {
	$id = $_POST["reminder_id"];
	$sql = "UPDATE employee 
			SET deleted = 'Y'
			WHERE reminder_id=:id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		echo json_encode(array("success"=>"reminder marked as deleted"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function verifyReminder() {
	$id = $_POST["reminder_id"];
	$sql = "UPDATE employee 
			SET verified = 'Y'
			WHERE reminder_id=:id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		echo json_encode(array("success"=>"reminder marked as deleted"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function addReminder() {
	$sql = "INSERT INTO`reminder` (`reminder_type`, `reminder_interval`, `reminder_span`, `reminder_datetime`, `verified`, `deleted`) 
			VALUES (':reminder_type', ':reminder_interval', ':reminder_span', ':reminder_datetime', ':verified', ':deleted');";
	try {
		
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("reminder_type", $_POST["reminder_type"]);
		$stmt->bindParam("reminder_interval", $_POST["reminder_interval"]);
		$stmt->bindParam("reminder_span", $_POST["reminder_span"]);
		$stmt->bindParam("reminder_datetime", $_POST["reminder_datetime"]);
		$stmt->bindParam("verified", $_POST["verified"]);
		$stmt->bindParam("deleted", $_POST["deleted"]);
		$stmt->execute();
		$new_id = $db->lastInsertId();
		//die(print_r($newEmployee));
		
		echo json_encode(array("id"=>$new_id)); 
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}

function updateReminder() {
	$sql = "UPDATE reminder 
	SET reminder_type = :reminder_type, 
	reminder_interval =  :reminder_interval, 
	reminder_span =  :reminder_span,
	reminder_datetime =  :reminder_datetime, 
	verified = :verified, 
	deleted = :deleted
	WHERE reminder_id = :reminder_id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("reminder_id", $_POST["reminder_id"]);
		$stmt->bindParam("reminder_type", $_POST["reminder_type"]);
		$stmt->bindParam("reminder_interval", $_POST["reminder_interval"]);
		$stmt->bindParam("reminder_span", $_POST["reminder_span"]);

		$stmt->bindParam("reminder_datetime", $_POST["reminder_datetime"]);
		$stmt->bindParam("verified", $_POST["verified"]);
		$stmt->bindParam("deleted", $_POST["deleted"]);
		$stmt->execute();
		//die(print_r($newEmployee));
		
		echo json_encode(array("success"=>$_POST["reminder_id"])); 
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}
