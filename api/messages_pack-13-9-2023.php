<?php
use Slim\Routing\RouteCollectorProxy;

$app->group('', function (RouteCollectorProxy $app) {
	$app->get('/contactmessages/{contact_id}', 'getContactMessages');
	$app->get('/pendings', 'getPendingMessages');
	$app->get('/inbox', 'getInbox');
	$app->get('/inboxnew', 'newInbox');
	$app->get('/inboxday/{day}', 'dayInbox');
	$app->get('/outboxday/{day}', 'dayOutbox');
	$app->get('/inboxcheck', 'checkInbox');
	$app->get('/outbox', 'getOutbox');
	$app->get('/drafts', 'getOutboxDrafts');
	$app->get('/draftcount', 'getDraftsCount');

	$app->group('/thread', function (RouteCollectorProxy $app) {
		$app->get('/pendings', 'getThreadInboxPendings');
		$app->get('/inbox', 'getThreadInbox');
		$app->get('/outbox', 'getThreadOutbox');
		$app->post('/confirm_email', 'confirmEmailThread');
		$app->post('/block_email', 'blockEmailThread');
	});
	$app->group('/threads', function (RouteCollectorProxy $app) {
		$app->get('/{thread_id}', 'getThreadBodies');
		$app->post('/delete', 'deleteSpecificThread');
		$app->post('/unread', 'unreadThread');
	});

	//$app->get('/phone_calls/{case_id}', 'getCalls');
	//$app->get('/callsnew', 'newPhoneCalls');

	$app->get('/message/{message_id}', 'getMessageBody');
	$app->group('/messages', function (RouteCollectorProxy $app) {
		$app->get('', 'getMessages');
		$app->get('/{message_id}', 'getMessage');
		$app->post('/read', 'readMessage');
		$app->post('/delete', 'deleteMessage');
		$app->post('/add', 'addMessage');
		$app->post('/update', 'updateMessage');
		$app->post('/confirm_email', 'confirmEmailMessage');
		$app->post('/block_email', 'blockEmailMessage');
	});

	$app->get('/notifications', 'getScanNotifications');
	$app->post('/notification/add', 'addNotification');
})->add(\Api\Middleware\Authorize::class);

$app->group('/messages', function (RouteCollectorProxy $app) {
	$app->post('/add_email', 'addEmailMessage');
	$app->post('/check_email', 'checkEmailMessage');
	$app->post('/setattachments', 'setMessageAttachments');
});

$app->get('/autoassign/{message_id}/{customer_id}', 'autoAssignMessage');
$app->post('/feedback/add', 'addMessage');

function getPendingMessages() {
	getMessages("pending");
}
function getContactMessages($contact_id) {
	getMessages("", "", "", $contact_id);
}
function getMessages($status, $start_date = "", $end_date = "", $contact_id = "") {
	session_write_close();
	$customer_id = $_SESSION['user_customer_id'];
	$user_uuid = $_SESSION['user_id'];
    $sql = "SELECT DISTINCT msg.*, msg.message_id id, msg.message_uuid uuid,
		msg.message_id id, msg.message_uuid uuid 
		, cmu.read_status, cmu.read_date, ccase.case_id, ccase.case_name
		#,thread.thread_id, thr.thread_uuid
		FROM `cse_message` msg
		/*
		INNER JOIN `cse_thread_message` thr
		ON msg.message_id = thr.message_id
		INNER JOIN cse_thread thread
		ON thr.thread_uuid = thread.thread_uuid
		*/
		
		LEFT OUTER JOIN cse_case_message ccm
		ON msg.message_uuid = ccm.message_uuid
		LEFT OUTER JOIN cse_case ccase
		ON ccm.case_uuid = ccase.case_uuid
		
		";
		$sql .= "
		INNER JOIN cse_message_user cmu
        ON 
			(msg.message_id = cmu.message_id AND cmu.user_uuid = :user_uuid 
				AND (cmu.`type` = 'to' || cmu.`type` = 'bcc' || cmu.`type` = 'cc')
			)";
			
	if ($contact_id!="") {
		$sql .= "
		INNER JOIN (
			SELECT DISTINCT mess.message_id contact_mess_id
			FROM cse_message mess
			INNER JOIN cse_message_contact cmc
			ON mess.message_uuid = cmc.message_uuid
			INNER JOIN cse_contact cont
			ON cmc.contact_uuid = cont.contact_uuid
			WHERE cont.contact_id = :contact_id
			AND mess.customer_id = :customer_id
		) contact_mess
		ON msg.message_id = contact_mess.contact_mess_id
		";
	}
	$sql .= "
		
		WHERE 1
		AND msg.customer_id = :customer_id
		AND msg.deleted = 'N'
		AND cmu.deleted = 'N'
		#AND thread.deleted = 'N'";
	if ($contact_id=="") {
		if ($status == "pending") {
			$sql .= "
			AND msg.message_type = 'email'
			AND msg.status = 'created'";
			$two_weeks = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d") - 15, date("Y")));
			$sql .= "
			AND CAST(msg.dateandtime AS DATE) > :two_weeks";
		} else {
			$sql .= "
			AND msg.status != 'created'";
		
			$email_user = getEmailInfo($_SESSION["user_plain_id"]);
			$blnEmailUser = (is_object($email_user));			
			if ($blnEmailUser) {
				if (isset($email_user->emails_pending)) {
					if ($email_user->emails_pending=="Y") {
						$sql .= " 
						AND msg.status!='created'";
					}
				}
			}
		}
		if ($status == "new") {
			$sql .= "
			AND cmu.read_date = '0000-00-00 00:00:00'";
		}
		if ($start_date!="") {
			$sql .= "
			AND CAST(msg.dateandtime AS DATE) >= :start_date";
		}
		if ($end_date!="") {
			$sql .= "
			AND CAST(msg.dateandtime AS DATE) <= :end_date";
		}
	}
	$sql .= "
	ORDER BY msg.message_id DESC";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("user_uuid", $user_uuid);
		if ($start_date!="") {
			$stmt->bindParam("start_date", $start_date);
		}
		if ($end_date!="") {
			$stmt->bindParam("start_date", $end_date);
		}
		if ($status == "pending") {
			$stmt->bindParam("two_weeks", $two_weeks);
		}
		if ($contact_id!="") {
			$stmt->bindParam("contact_id", $contact_id);
		}
		$stmt->execute();
		$messages = $stmt->fetchAll(PDO::FETCH_OBJ);

        echo json_encode($messages);     
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	exit();
}
function getDraftsCount() {
	session_write_close();
	$sql = "SELECT COUNT(cm.message_id) draft_count
	FROM cse_message cm
	INNER JOIN cse_message_user cmu
	ON cm.message_id = cmu.message_id
	WHERE cm.deleted = 'D'
	AND cmu.user_uuid = '" . $_SESSION['user_id'] . "'
	AND cmu.`type` = 'from'
	AND cm.customer_id = " . $_SESSION['user_customer_id'];
	try {
		$stmt = DB::run($sql);
		$draft = $stmt->fetchObject();

        echo json_encode($draft);     
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getDrafts() {
	$sql = "SELECT COUNT(cm.message_id) draft_count
	FROM cse_message cm
	INNER JOIN cse_message_user cmu
	ON cm.message_id = cmu.message_id
	WHERE cm.deleted = 'D'
	AND cmu.user_uuid = '" . $_SESSION['user_id'] . "'
	AND cmu.`type` = 'from'
	AND cm.customer_id = " . $_SESSION['user_customer_id'];
	try {
		$stmt = DB::run($sql);
		$draft = $stmt->fetchObject();

        echo json_encode($draft);     
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	exit();
}
function getCalls($case_id) {
	session_write_close();
    $sql = "SELECT DISTINCT `message_id` id, mess.*, eve.`customer_id`,ccase.case_id, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`
			FROM `cse_event` mess
			INNER JOIN `cse_case_message` cmess
			ON eve.event_uuid = ceve.event_uuid
			INNER JOIN cse_case ccase
			ON ceve.case_uuid = ccase.case_uuid
			LEFT OUTER JOIN cse_case_person ccapp ON ccase.case_uuid = ccapp.case_uuid
			LEFT OUTER JOIN ";
	if (($_SESSION['user_customer_id']==1033)) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
			LEFT OUTER JOIN `cse_case_corporation` ccorp
			ON (ccase.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
			LEFT OUTER JOIN `cse_corporation` employer
			ON ccorp.corporation_uuid = employer.corporation_uuid
			WHERE ccase.deleted != 'Y'
			AND eve.event_type = 'phone_call'
			AND ccase.case_id = :case_id";
	

	$sql .=	" AND `eve`.`deleted` ='N'
	AND eve.customer_id = " . $_SESSION['user_customer_id'];
	$sql .= " ORDER BY eve.event_id ASC";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		
		$stmt->execute();
		$events = $stmt->fetchAll(PDO::FETCH_OBJ);
        echo json_encode($events);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	exit();
}
function getThreadOutbox() {
	session_write_close();
	
	$email_user = getEmailInfo($_SESSION["user_plain_id"]);
	$blnEmailUser = (is_object($email_user));
	
	$month_ago =  mktime(0, 0, 0, date("m") - 12, date("d"),   date("Y"));
	$month_ago = date("Y-m-d", $month_ago);
	
	$sql = "SELECT DISTINCT thr.thread_id `id`, thr.thread_uuid `uuid`, 
	msg.customer_id, msg.`message_type` `thread_type`,
	thread_counts.message_count, REPLACE(thread_counts.thread_attachments, ',', '|') thread_attachments,
	thread_counts.thread_message_ids,
	IFNULL(`user`.`user_name`, `msg`.`from`) `sender`, IFNULL(`cc`.`case_id`, -1) `case_id`,
	msg.`subject`, msg.`status` message_status,  IFNULL(CAST(CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`)  AS CHAR (10000) CHARACTER SET UTF8), '') `case_name`, msg.attachments, 
	IF(msg.snippet = '', msg.message, msg.snippet) `snippet`,
	thread_counts.max_message_id, msgmax.dateandtime, IFNULL(cmu_max.read_status, 'N') read_status,
    msg.message_to, msg.message_cc, msg.message_bcc, 
	IFNULL(ccd.message_attachments, '') message_attachments
	
	FROM cse_thread thr	
	
	INNER JOIN (
		SELECT ct.thread_uuid
		FROM cse_thread ct
		INNER JOIN cse_thread_message ctm
		ON ct.thread_uuid = ctm.thread_uuid
		INNER JOIN cse_message_user cmut
		ON ctm.message_uuid = cmut.message_uuid AND cmut.user_uuid = '" . $_SESSION['user_id'] . "'
    ) my_threads
    ON thr.thread_uuid = my_threads.thread_uuid
	
	INNER JOIN (
		SELECT thread_uuid, COUNT(DISTINCT cse_thread_message.message_uuid) message_count,  
		MIN(DISTINCT cse_message.message_id) min_message_id, MAX(DISTINCT cse_message.message_id) max_message_id,
		GROUP_CONCAT(DISTINCT cse_message.message_id) thread_message_ids,
		GROUP_CONCAT(attachments) thread_attachments
		FROM cse_thread_message
		INNER JOIN cse_message ON cse_thread_message.message_id = cse_message.message_id
		WHERE cse_message.customer_id = '" . $_SESSION['user_customer_id'] . "'
		GROUP BY thread_uuid
	) thread_counts
	ON thr.thread_uuid = thread_counts.thread_uuid
	
	INNER JOIN cse_message msg
	ON thread_counts.min_message_id = msg.message_id
	
	INNER JOIN cse_message msgmax
	ON thread_counts.max_message_id = msgmax.message_id
	
	LEFT OUTER JOIN `cse_message_user` cmu_max
	ON msgmax.message_id = cmu_max.message_id AND cmu_max.type = 'from' AND cmu_max.user_uuid = '" . $_SESSION["user_id"] . "'
	
	INNER JOIN `cse_message_user` cmu_from
	ON (msg.message_id = cmu_from.message_id AND cmu_from.type = 'from' AND cmu_from.user_uuid = '" . $_SESSION["user_id"] . "')
	LEFT OUTER JOIN ikase.`cse_user` `user`
	ON cmu_from.user_uuid = `user`.`user_uuid`
	
	LEFT OUTER JOIN (
		SELECT icmd.message_uuid, GROUP_CONCAT(DISTINCT icd.document_filename) message_attachments
        FROM `cse_message_document` icmd
        INNER JOIN `cse_message` im
        ON icmd.message_uuid = im.message_uuid
        INNER JOIN `cse_document` icd
        ON icmd.document_uuid = icd.document_uuid
        WHERE 1
        GROUP BY message_uuid
    ) ccd
	ON msg.message_uuid = ccd.message_uuid
	
	LEFT OUTER JOIN `cse_case_message` ccm
		ON msg.message_uuid = ccm.message_uuid AND ccm.deleted = 'N'
	LEFT OUTER JOIN `cse_case` cc
	ON ccm.case_uuid = cc.case_uuid
	LEFT OUTER JOIN cse_case_person ccapp ON cc.case_uuid = ccapp.case_uuid
	LEFT OUTER JOIN ";
	
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
	LEFT OUTER JOIN `cse_case_corporation` ccorp
	ON (cc.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
	LEFT OUTER JOIN `cse_corporation` employer
	ON ccorp.corporation_uuid = employer.corporation_uuid	
	WHERE thr.deleted = 'N'";

	$sql .= "
	AND thr.customer_id = '" . $_SESSION['user_customer_id'] . "'
	ORDER BY msgmax.dateandtime DESC";
	//AND cc.case_uuid IS NULL
	
	//die($sql);
	
	try {
		$threads = DB::select($sql);

        echo json_encode($threads);     
	} catch(PDOException $e) {
		$error = array("error"=> array("sql"=>$sql, "text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	exit();
}
function getThreadInboxPendings() {
	getThreadInbox("pending");
}
function getThreadInbox($new = "") {
	/*
	if ($_SERVER['REMOTE_ADDR']!='47.153.49.248' && $_SERVER['REMOTE_ADDR']!='173.58.194.146') {
		getMessages($new);
		return;
	}
	*/
	//whatever is below is too much
	/*
	    $db = getConnection();
		$stmt = $db->prepare("SELECT * FROM cse_message_user");
		$stmt->execute();
		$threads = $stmt->fetchAll(PDO::FETCH_OBJ);
		print_r($threads);
		
		echo '123';
		die;*/
	session_write_close();
	
	if($_SESSION["user_customer_id"]==1075 || $_SESSION["user_customer_id"]==1042) {
		//$error = array("error"=> array("text"=>"Inbox is down"));
		//echo json_encode($error);
		
		//return;
	}
	
	$email_user = getEmailInfo($_SESSION["user_plain_id"]);
	$blnEmailUser = (is_object($email_user));
	
	$month_ago =  mktime(0, 0, 0, date("m") - 12, date("d"),   date("Y"));
	$month_ago = date("Y-m-d", $month_ago);

	
	//IF(cc.case_name='', IFNULL(CAST(CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`)  AS CHAR (10000) CHARACTER SET UTF8), ''), cc.case_name) `case_name`, 
	
	/*
	LEFT OUTER JOIN cse_case_person ccapp ON cc.case_uuid = ccapp.case_uuid
	LEFT OUTER JOIN ";
		
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	// OR cmu_max.user_uuid = '" . $_SESSION["user_plain_id"] . "'
	// OR cmu_max.user_uuid = '" . $_SESSION["user_plain_id"] . "'
	// OR cmu_from.user_uuid = `user`.`user_id`
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
	
	LEFT OUTER JOIN `cse_case_corporation` ccorp
	ON (cc.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
	LEFT OUTER JOIN `cse_corporation` employer
	ON ccorp.corporation_uuid = employer.corporation_uuid
	*/
	
	$sql = "
SELECT DISTINCT thread_list.*,
IFNULL(`cmu_from`.`user_name`, `msg_min`.`from`) `sender`, IFNULL(`cc`.`case_id`, -1) `case_id`,
	msg_min.`subject`, msg_min.`status` message_status,
	IF(cc.case_name='', cc.case_number, cc.case_name) `case_name`, 
	IF(msg_min.snippet = '', msg_min.message, msg_min.snippet) `snippet`,
	msg_max.dateandtime, 
	IF (msg_max.`from`='" . $_SESSION["user_name"] . "', 'Y', IFNULL(cmu_max.read_status, 'N')) read_status, 
	IFNULL(ccd.message_attachments, '') message_attachments
FROM 
(
	SELECT DISTINCT thr.thread_id `id`, thr.thread_uuid `uuid`, 
		thr.customer_id, msg.`message_type` `thread_type`,
		thread_counts.message_count, thread_counts.thread_attachments,
		thread_counts.thread_message_ids,
		thread_counts.min_message_id, thread_counts.max_message_id,
		CONCAT(msg.message_to, ';', msg.message_cc, ';', msg.message_bcc) message_destination, msg.receiver
	FROM cse_message msg
	
	INNER JOIN cse_thread_message ctm
	ON msg.message_id = ctm.message_id AND (
		CONCAT(msg.message_to, ';', msg.message_cc, ';', msg.message_bcc) LIKE '%" . $_SESSION["user_nickname"] . "%'";
		
	if ($blnEmailUser) {
		if ($email_user->email_name!="") {
			$sql .= " 
			OR CONCAT(msg.message_to, ';', msg.message_cc, ';', msg.message_bcc) LIKE '%" . $email_user->email_name . "%'";
		}
	}
	$sql .= " )
	
	INNER JOIN cse_thread thr
	ON ctm.thread_uuid = thr.thread_uuid
	
	INNER JOIN (
		SELECT thread_uuid, COUNT(DISTINCT cse_thread_message.message_uuid) message_count,  
		MIN(DISTINCT cse_message.message_id) min_message_id, MAX(DISTINCT cse_message.message_id) max_message_id,
		GROUP_CONCAT(DISTINCT cse_message.message_id) thread_message_ids,
		GROUP_CONCAT(attachments) thread_attachments
		FROM cse_thread_message
		INNER JOIN cse_message 
		ON cse_thread_message.message_id = cse_message.message_id
		WHERE cse_message.customer_id = '" . $_SESSION["user_customer_id"] . "'
		AND (CONCAT(cse_message.message_to, ';', cse_message.message_cc, ';', cse_message.message_bcc) LIKE '%" . $_SESSION["user_nickname"] . "%' OR cse_message.`from` = '" . $_SESSION["user_name"] . "')
		GROUP BY thread_uuid
	) thread_counts
	ON thr.thread_uuid = thread_counts.thread_uuid
	
	WHERE 1
	AND msg.deleted = 'N'";
	
	if ($new == "pending") {
		$sql .= " 
		AND msg.message_type='email' 
		AND msg.status = 'created'";
		$two_weeks = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d") - 15, date("Y")));
		$sql .= "
		AND CAST(msg.dateandtime AS DATE) > :two_weeks";
	} else {
		//if ($_SERVER['REMOTE_ADDR']!='47.153.49.248') {
		//if ($_SESSION["user_plain_id"]!="2") {
		if ($blnEmailUser) {
			if (isset($email_user->emails_pending)) {
				if ($email_user->emails_pending=="Y") {
					$sql .= " 
					AND msg.status!='created'";
				}
			}
		}
	}
	
	$sql .= " 
	) thread_list
	INNER JOIN cse_message msg_min
	ON thread_list.min_message_id = msg_min.message_id
	
	INNER JOIN cse_message msg_max
	ON thread_list.max_message_id = msg_max.message_id
	
	LEFT OUTER JOIN `cse_case_message` ccm
	ON msg_min.message_uuid = ccm.message_uuid
	
	LEFT OUTER JOIN `cse_case` cc
	ON ccm.case_uuid = cc.case_uuid
	
	
	LEFT OUTER JOIN (
		SELECT cmu_max.message_uuid, cmu_max.message_id, cmu_max.read_status, max_users.deleted message_deleted 
		FROM cse_message_user cmu_max
		INNER JOIN (
			SELECT message_uuid, deleted, MAX(message_user_id) max_id 
			FROM cse_message_user cmu_max
			WHERE 1	
			AND (cmu_max.user_uuid = '" . $_SESSION["user_id"] . "')
			GROUP BY message_uuid
		) max_users
		ON cmu_max.message_user_id = max_users.max_id
		WHERE 1
		AND (cmu_max.user_uuid = '" . $_SESSION["user_id"] . "')
	) cmu_max
	ON msg_max.message_id = cmu_max.message_id
	/*
	LEFT OUTER JOIN (
		SELECT icmd.message_uuid, GROUP_CONCAT(DISTINCT icd.document_filename) message_attachments
		FROM `cse_message_document` icmd
		INNER JOIN `cse_message` im
		ON icmd.message_uuid = im.message_uuid
		INNER JOIN `cse_document` icd
		ON icmd.document_uuid = icd.document_uuid
		WHERE 1
		GROUP BY message_uuid
	) ccd
	ON msg_max.message_uuid = ccd.message_uuid
	
	LEFT OUTER JOIN (
		SELECT * 
        FROM `cse_message_user`
        WHERE 1
        AND `type` = 'from'
	) cmu_from
	ON msg_min.message_id = cmu_from.message_id 
	
	LEFT OUTER JOIN ikase.`cse_user` `user`
	ON (cmu_from.user_id = `user`.`user_id`)
	*/
	
	LEFT OUTER JOIN cse_message_attachments ccd
	ON msg_max.message_id = ccd.message_id
	
    LEFT OUTER JOIN cse_message_from cmu_from
    ON msg_min.message_id = cmu_from.message_id 
	
	WHERE IFNULL(message_deleted, 'N') = 'N'
	
	";
	if ($new=="new") {
		//$sql .= " AND IFNULL(cmu_max.read_status, 'N') = 'N'";
		$sql .= " 
		AND IF (msg_max.`from`='" . $_SESSION["user_name"] . "', 'Y', IFNULL(cmu_max.read_status, 'N')) = 'N'";
	}
	//$blnGoGetMail = ($_SESSION["user_plain_id"]==1288 || $_SESSION["user_plain_id"]==2 || ($_SESSION['user_plain_id']==670 && $_SERVER['REMOTE_ADDR']=='47.153.49.248'));
	$blnGoGetMail = true;
	
	if ($_SERVER['REMOTE_ADDR']=='47.153.49.248') {
		//echo $two_weeks . "<br /><br />";
		//die($sql);
	}
	if ($_SERVER['REMOTE_ADDR']!='47.153.49.248') {
		/*
		$sql .= " 
		ORDER BY msg_max.dateandtime DESC";
		*/
	}
	if ($blnGoGetMail) {
		//
	}
	try {
		//die(print_r($sql));
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($new == "pending") {
			$stmt->bindParam("two_weeks", $two_weeks);
		}
		$stmt->execute();
		$threads = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		
		$user_nickname = $_SESSION["user_nickname"];
		$arrOutput = array();
		//die(print_r($threads));
		$current_thread_id = 0;
		$arrLastDestin = array();
		
		
		usort($threads,function($first,$second){
			return strtotime($first->dateandtime) < strtotime($second->dateandtime);
		});
	
		//no repeats
		foreach($threads as $tindex=>$thread) {
			$thread_id = $thread->id;
			
			$message_destination = $thread->message_destination;
			$arrDestin = explode(";", $message_destination);
			
			if ($current_thread_id == $thread_id) {
				$arrLastDestin = array_merge($arrLastDestin, $arrDestin);
			} else {
				if ($tindex > 0) {
					foreach($arrLastDestin as $destin_index=>$destin) {
						if ($destin=="") {
							unset($arrLastDestin[$destin_index]);
						}
					}
					$result_unique = array_unique($arrLastDestin);
					
					$threads[$tindex - 1]->message_destination = implode(";", $result_unique);
					
					//more than one row for this threadid?
					if(count($arrRows[$current_thread_id]) > 1) {
						//echo $current_thread_id . " -> tindex:" . $tindex . "<br />";
						//die(print_r($arrRows[$current_thread_id]));
						//remove any previous rows
						$arrThreadRows = $arrRows[$current_thread_id];
						foreach($arrThreadRows as $row_index=>$thread_index) {
							if ($row_index==count($arrThreadRows)-1) {
								break;
							}
							unset($threads[$thread_index]);
						}
					}
				}
				$current_thread_id = $thread_id;
				$arrLastDestin = $arrDestin;
			}
			//pick the row after current check
			$arrRows[$thread_id][] = $tindex;
		}
		//die(print_r($threads));
		foreach($threads as $tindex=>$thread) {
			$message_destination = $thread->message_destination;
			$arrDestin = explode(";", $message_destination);
			//we have to make sure that the nickname is an exact match
			if (!in_array($user_nickname, $arrDestin)) {
				//unset($threads[$tindex]);
				continue;
			}
			
			$arrOutput[] = $thread;
		}
		$threads = $arrOutput;
		
        echo json_encode($threads);     
		exit();
	} catch(PDOException $e) {
		$error = array("error"=> array("sql"=>$sql, "text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getInbox($new = "") {
	session_write_close();
	$month_ago =  mktime(0, 0, 0, date("m"), date("d") - 360,   date("Y"));
	$month_ago = date("Y-m-d", $month_ago);
	
	$messages = DB::select("SELECT * FROM cse_message");

        echo json_encode($messages);     
	
	$sql = "
	SELECT DISTINCT IFNULL(thread_counts.message_count, 1) message_count,
		msg.*, msg.message_id id, msg.message_uuid uuid, 
		msg.message_id id, msg.message_uuid uuid, 
		IFNULL(cc.case_id, -1) case_id, cc.case_number,
		`user`.user_name `sender`, 
		CAST(CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`)  AS CHAR (10000) CHARACTER SET UTF8)  `case_name`,
		IFNULL(cmr.reply_date, '') reply_date, IFNULL(cmr.forward_date, '') forward_date,
		IFNULL(cmu_to.to_user_uuids, '') to_user_uuids, 
		IFNULL(cmu_to.to_user_names, '') to_user_names, 
		IFNULL(cmu_to.read_dates, '') read_dates, 
		IFNULL(cmu_to.to_nicknames, '') to_nicknames,
		IFNULL(cmu_to.to_types, '') to_types
        
FROM cse_message msg

LEFT OUTER JOIN cse_thread_message ctm
ON msg.message_id = ctm.message_id
        
INNER JOIN cse_message_user cmu
ON (msg.message_id = cmu.message_id AND (cmu.`type` = 'to' OR cmu.`type` = 'cc' OR cmu.`type` = 'bcc'))

INNER JOIN ikase.cse_user cu
ON cmu.user_id = cu.user_id

LEFT OUTER JOIN (
	SELECT thread_uuid, COUNT(message_uuid) message_count 
	FROM cse_thread_message
	GROUP BY thread_uuid
) thread_counts
ON ctm.thread_uuid = thread_counts.thread_uuid
LEFT OUTER JOIN `cse_message_reaction` cmr
ON (msg.message_uuid = cmr.message_uuid AND cmr.deleted = 'N' AND cmr.user_uuid = '" . $_SESSION["user_id"] . "')
LEFT OUTER JOIN `cse_message_user` cmu_from
ON (msg.message_id = cmu_from.message_id AND cmu_from.type = 'from')
LEFT OUTER JOIN ikase.`cse_user` `user`
ON cmu_from.user_id = `user`.`user_id`

LEFT OUTER JOIN `cse_case_message` ccm
ON msg.message_uuid = ccm.message_uuid
LEFT OUTER JOIN `cse_case` cc
ON ccm.case_uuid = cc.case_uuid
LEFT OUTER JOIN cse_case_person ccapp ON cc.case_uuid = ccapp.case_uuid
LEFT OUTER JOIN ";
if ($_SESSION['user_customer_id']==1033) { 
	$sql .= "(" . SQL_PERSONX . ")";
} else {
	$sql .= "cse_person";
}
$sql .= " app ON ccapp.person_uuid = app.person_uuid
LEFT OUTER JOIN `cse_case_corporation` ccorp
ON (cc.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
LEFT OUTER JOIN `cse_corporation` employer
ON ccorp.corporation_uuid = employer.corporation_uuid

LEFT OUTER JOIN (
	SELECT msg.message_uuid, 
	GROUP_CONCAT(DISTINCT cu.user_uuid
		ORDER BY cu.nickname ASC
		SEPARATOR '|') to_user_uuids,
    GROUP_CONCAT(cu.nickname
		ORDER BY cu.nickname ASC
		SEPARATOR '|') to_nicknames,
	GROUP_CONCAT(mu.read_status
		ORDER BY cu.nickname ASC
		SEPARATOR '|') to_statuses,
	GROUP_CONCAT(cu.user_name
		ORDER BY cu.nickname ASC
		SEPARATOR '|') to_user_names,
	GROUP_CONCAT(mu.read_date
		ORDER BY cu.nickname ASC
		SEPARATOR '|') read_dates,
	GROUP_CONCAT(mu.`type`
		ORDER BY cu.nickname ASC
		SEPARATOR '|') to_types
	
	FROM `cse_message` msg
	
	INNER JOIN `cse_message_user` mu
	ON msg.message_id = mu.message_id AND (mu.`type` = 'to' OR mu.`type` = 'cc' OR mu.`type` = 'bcc')
	AND mu.customer_id = " . $_SESSION['user_customer_id'] . "
    
	INNER JOIN 	ikase.cse_user cu
	ON mu.user_id = cu.user_id
	
	WHERE 1
	
	AND cu.customer_id = " . $_SESSION['user_customer_id'] . "
	AND msg.customer_id = " . $_SESSION['user_customer_id'] . "
	AND INSTR(CONCAT(msg.message_to, msg.message_cc, msg.message_bcc), cu.nickname) > 0
	GROUP BY msg.message_uuid
    HAVING INSTR(GROUP_CONCAT(DISTINCT mu.user_uuid), '" . $_SESSION['user_id'] . "') > 0
	ORDER BY cu.nickname ASC
) cmu_to
ON msg.message_uuid = cmu_to.message_uuid
        
WHERE 1
AND msg.dateandtime > '" . $month_ago . "'
AND msg.deleted = 'N'
AND msg.customer_id = " . $_SESSION['user_customer_id'] . "
AND INSTR(CONCAT(msg.message_to, msg.message_cc, msg.message_bcc), cu.nickname) > 0
AND cmu.user_uuid = '" . $_SESSION['user_id'] . "'";
if ($new=="Y") {
	$sql .= " AND cmu.read_status = 'N'";
}
$sql .= " AND cmu.deleted = 'N'";

$sql .= " ORDER BY message_id DESC";

//die($sql);
	
	try {
		$messages = DB::select($sql);

        echo json_encode($messages);     
	} catch(PDOException $e) {
		$error = array("error"=> array("sql"=>$sql, "text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	exit();
}
function newInbox() {
	getThreadInbox("new");
	return;
	
	session_write_close();
	
    $sql = "SELECT DISTINCT msg.*, msg.message_id id, msg.message_uuid uuid, 
		msg.message_id id, msg.message_uuid uuid, cmu.read_status, cmu.read_date, cc.case_id,
		IFNULL(`user`.`user_name`, `msg`.`from`) `sender`, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`
		FROM `cse_message` msg
		LEFT OUTER JOIN (
			SELECT cm.message_id
			FROM cse_message cm
			INNER JOIN cse_message_user cmu
			ON cm.message_id = cmu.message_id
			INNER JOIN cse_thread_message ctm
			ON cm.message_id = ctm.message_id
			INNER JOIN (
				SELECT 
					thread_uuid,
						COUNT(DISTINCT cse_thread_message.message_uuid) message_count
				FROM
					cse_thread_message
				INNER JOIN cse_message ON cse_thread_message.message_id = cse_message.message_id
				GROUP BY thread_uuid
			) thread_counts 
			ON ctm.thread_uuid = thread_counts.thread_uuid
			WHERE 1 
			AND cmu.user_uuid = '" . $_SESSION["user_id"] . "'
			AND cmu.`type` = 'from'
			AND message_count = 1
		) forbidden_messages
		ON msg.message_id = forbidden_messages.message_id
		
		INNER JOIN `cse_thread_message` ctm
		ON msg.message_uuid = ctm.message_uuid
		INNER JOIN `cse_thread` thr
		ON ctm.thread_uuid = thr.thread_uuid
		INNER JOIN `cse_message_user` cmu
		ON msg.message_id = cmu.message_id AND `cmu`.deleted = 'N' AND (cmu.type = 'to' OR cmu.type = 'cc' OR cmu.type = 'bcc')
		INNER JOIN ikase.cse_user cu
		ON cmu.user_id = cu.user_id
		LEFT OUTER JOIN `cse_message_user` cmu_from
		ON (msg.message_id = cmu_from.message_id AND cmu_from.type = 'from')
		LEFT OUTER JOIN ikase.`cse_user` `user`
		ON cmu_from.user_id = `user`.`user_id`
		LEFT OUTER JOIN `cse_case_message` ccm
		ON msg.message_uuid = ccm.message_uuid
		LEFT OUTER JOIN `cse_case` cc
		ON ccm.case_uuid = cc.case_uuid
		LEFT OUTER JOIN cse_case_person ccapp ON cc.case_uuid = ccapp.case_uuid
		LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	
	//$sql .= "cse_person";
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
		LEFT OUTER JOIN `cse_case_corporation` ccorp
		ON (cc.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
		LEFT OUTER JOIN `cse_corporation` employer
		ON ccorp.corporation_uuid = employer.corporation_uuid
		WHERE 1
		AND msg.customer_id = " . $_SESSION['user_customer_id'] . "
		AND cmu.user_uuid = '" . $_SESSION['user_id'] . "'
		AND msg.deleted = 'N'
		AND msg.message_type != 'phone_call'		
		AND INSTR(CONCAT(msg.message_to, msg.message_cc, msg.message_bcc), cu.nickname) > 0
		AND `cmu`.read_status = 'N'
		AND `cmu`.deleted = 'N'
		AND forbidden_messages.message_id IS NULL
		ORDER BY msg.message_id DESC";
	if (isset($_GET["debug"])) {
		die($sql);	
	}
	die(false);
	try {
		$messages = DB::select($sql);

        echo json_encode($messages);     
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	exit();
}
function dayInbox($day) {
	getMessages("", $day);
	return;
	session_write_close();
	$sql = "SELECT DISTINCT msg.*, msg.message_id id, msg.message_uuid uuid, 
		msg.message_id id, msg.message_uuid uuid, cmu.read_status, cmu.read_date, cc.case_id,
		`user`.user_name `sender`, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`,
		IFNULL(cmr.reply_date, '') reply_date, IFNULL(cmr.forward_date, '') forward_date
		FROM `cse_message` msg
		LEFT OUTER JOIN `cse_message_reaction` cmr
		ON (msg.message_uuid = cmr.message_uuid AND cmr.deleted = 'N')
		INNER JOIN `cse_message_user` cmu_from
		ON (msg.message_id = cmu_from.message_id AND cmu_from.type = 'from')
		INNER JOIN ikase.`cse_user` `user`
		ON cmu_from.user_id = `user`.`user_id`
		INNER JOIN `cse_message_user` cmu
		ON msg.message_id = cmu.message_id
		LEFT OUTER JOIN `cse_case_message` ccm
		ON msg.message_uuid = ccm.message_uuid
		LEFT OUTER JOIN `cse_case` cc
		ON ccm.case_uuid = cc.case_uuid
		LEFT OUTER JOIN cse_case_person ccapp ON cc.case_uuid = ccapp.case_uuid
		LEFT OUTER JOIN ";
	
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	
	//$sql .= "cse_person";
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
		LEFT OUTER JOIN `cse_case_corporation` ccorp
		ON (cc.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
		LEFT OUTER JOIN `cse_corporation` employer
		ON ccorp.corporation_uuid = employer.corporation_uuid
		WHERE 1
		AND CAST(`msg`.`dateandtime` AS DATE) = :day
		AND msg.customer_id = " . $_SESSION['user_customer_id'] . "
		AND cmu.user_uuid = '" . $_SESSION['user_id'] . "'
		AND msg.deleted = 'N'
		AND (`cmu`.`type` = 'to' OR `cmu`.`type` = 'cc' OR `cmu`.`type` = 'bcc')
		AND `cmu`.deleted = 'N'
		ORDER BY msg.message_id DESC";
	//die($sql);	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("day", $day);
		$stmt->execute();
		$messages = $stmt->fetchAll(PDO::FETCH_OBJ);

        echo json_encode($messages);     
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function checkInbox() {
	session_write_close();
    $sql = "SELECT COUNT( msg.message_id) message_count
		FROM `cse_message` msg
		INNER JOIN `cse_message_user` cmu
		ON msg.message_id = cmu.message_id
		WHERE 1
		AND msg.customer_id = " . $_SESSION['user_customer_id'] . "
		AND cmu.user_uuid = '" . $_SESSION['user_id'] . "'
		AND msg.deleted = 'N'
		AND (`cmu`.`type` = 'to' OR `cmu`.`type` = 'cc' OR `cmu`.`type` = 'bcc')
		AND `cmu`.read_status = 'N'
		AND `cmu`.deleted = 'N'
		ORDER BY msg.message_id DESC";
	//die($sql);	
	try {
		$stmt = DB::run($sql);
		$message_count = $stmt->fetchObject();
		
		header('Content-Type: text/event-stream');
		header('Cache-Control: no-cache'); // recommended to prevent caching of event data.
		
		echo "id: 0" . PHP_EOL;
		echo "data: " . $message_count->message_count . PHP_EOL;
		echo PHP_EOL;
		ob_flush();
		flush();
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	exit();
}
function getOutboxDrafts() {
	getOutbox("D");
}
function dayOutbox($day) {
	getOutbox("N", $day);
}
function getOutbox($drafts = "N", $day = "") {
	session_write_close();
	
		$sql = "SELECT DISTINCT IFNULL(thread_counts.message_count, 1) message_count, 
		msg.*, msg.message_id id, msg.message_uuid uuid, 
		msg.message_id id, msg.message_uuid uuid, cc.case_id,
		`user`.user_name `sender`, CONCAT(app.first_name,' ',app.last_name,' vs ', employer.`company_name`) `case_name`, cmu.read_status, cmu.read_date,
		cmu_to.to_user_uuids, cmu_to.to_user_names, cmu_to.read_dates, cmu_to.to_nicknames, cmu_to.to_types
		FROM `cse_message` msg
		INNER JOIN cse_thread_message ctm
		ON msg.message_id = ctm.message_id
        INNER JOIN (
			SELECT thread_uuid, COUNT(message_uuid) message_count 
			FROM cse_thread_message
			GROUP BY thread_uuid
        ) thread_counts
        ON ctm.thread_uuid = thread_counts.thread_uuid
		INNER JOIN `cse_message_user` cmu_from
		ON (msg.message_id = cmu_from.message_id AND cmu_from.type = 'from')
		INNER JOIN ikase.`cse_user` `user`
		ON cmu_from.user_id = `user`.`user_id`
		
		INNER JOIN `cse_message_user` cmu
		ON msg.message_uuid = cmu.message_uuid
		LEFT OUTER JOIN `cse_case_message` ccm
		ON msg.message_uuid = ccm.message_uuid
		LEFT OUTER JOIN `cse_case` cc
		ON ccm.case_uuid = cc.case_uuid
		LEFT OUTER JOIN cse_case_person ccapp ON cc.case_uuid = ccapp.case_uuid
		LEFT OUTER JOIN ";
	if ($_SESSION['user_customer_id']==1033) { 
		$sql .= "(" . SQL_PERSONX . ")";
	} else {
		$sql .= "cse_person";
	}
	
	//$sql .= "cse_person";
	$sql .= " app ON ccapp.person_uuid = app.person_uuid
		LEFT OUTER JOIN `cse_case_corporation` ccorp
		ON (cc.case_uuid = ccorp.case_uuid AND ccorp.attribute = 'employer' AND ccorp.deleted = 'N')
		LEFT OUTER JOIN `cse_corporation` employer
		ON ccorp.corporation_uuid = employer.corporation_uuid
		
		LEFT OUTER JOIN (
			SELECT msg.message_uuid, 
            GROUP_CONCAT(DISTINCT cu.user_uuid
                ORDER BY cu.nickname ASC, mu.read_date DESC
				SEPARATOR '|') to_user_uuids,
            GROUP_CONCAT(DISTINCT cu.nickname
                ORDER BY cu.nickname ASC, mu.read_date DESC
				SEPARATOR '|') to_nicknames,
            GROUP_CONCAT(DISTINCT cu.user_name
                ORDER BY cu.nickname ASC, mu.read_date DESC
				SEPARATOR '|') to_user_names,
            GROUP_CONCAT(mu.read_date
                ORDER BY cu.nickname ASC, mu.read_date DESC
				SEPARATOR '|') read_dates,
			GROUP_CONCAT(mu.`type`
				ORDER BY cu.nickname ASC, mu.read_date DESC
				SEPARATOR '|') to_types
            
			FROM `cse_message` msg
            
            INNER JOIN `cse_message_user` mu
            ON msg.message_id = mu.message_id AND (mu.`type` = 'to' OR mu.`type` = 'cc' OR mu.`type` = 'bcc')
            AND mu.customer_id = " . $_SESSION['user_customer_id'] . "
            
            INNER JOIN `cse_message_user` cmu
            ON msg.message_id = cmu.message_id AND cmu.`type` = 'from'
            AND cmu.customer_id = " . $_SESSION['user_customer_id'] . "
			
            INNER JOIN 	ikase.cse_user cu
            ON mu.user_id = cu.user_id AND cu.customer_id = " . $_SESSION['user_customer_id'] . "
            
            WHERE 1
			AND cmu.user_uuid = '" . $_SESSION['user_id'] . "'
			AND msg.customer_id = " . $_SESSION['user_customer_id'] . "
            
            GROUP BY msg.message_uuid
			ORDER BY cu.nickname ASC
			LIMIT 0, 5000
        ) cmu_to
		ON msg.message_uuid = cmu_to.message_uuid
		
		WHERE 1
		AND msg.customer_id = " . $_SESSION['user_customer_id'] . "
		AND cmu.user_uuid = '" . $_SESSION['user_id'] . "'
		AND msg.deleted = '" . $drafts . "'
		AND `cmu`.`type` = 'from'
		AND `cmu`.deleted = 'N'";
		if ($day!="") {
			$sql .= "
			AND CAST(`msg`.`dateandtime` AS DATE) = :day";
		}
	$sql .= "
		ORDER BY msg.message_id DESC";
	
	//die($sql);	
	
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($day!="") {
			$stmt->bindParam("day", $day);
		}
		$stmt->execute();
		$messages = $stmt->fetchAll(PDO::FETCH_OBJ);

        echo json_encode($messages);     
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	exit();
}
function getMessage($message_id) {
	session_write_close();
    $sql = "SELECT msg.*, msg.message_id id, msg.message_uuid uuid, cse.case_id,
	IFNULL(ctm.thread_uuid, '') thread_uuid
		FROM `cse_message` msg
		LEFT OUTER JOIN cse_case_message ccm
		ON msg.message_uuid = ccm.message_uuid
		LEFT OUTER JOIN cse_case cse
		ON ccm.case_uuid = cse.case_uuid
		LEFT OUTER JOIN cse_thread_message ctm
		ON msg.message_id = ctm.message_id
		WHERE msg.message_id = :message_id
		AND msg.customer_id = " . $_SESSION['user_customer_id'] . "
		AND msg.deleted != 'Y'
		ORDER BY msg.message_id DESC";
	//die($sql);	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("message_id", $message_id);
		$stmt->execute();
		$message = $stmt->fetchObject();

        echo json_encode($message);     
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	exit();
}
function unreadThread() {
	session_write_close();
	$thread_id = passed_var("thread_id", "post");
	$customer_id = $_SESSION["user_customer_id"];
	
	$sql = "UPDATE cse_message_user cmu, cse_thread ct
	SET cmu.read_status = 'N'
	WHERE cmu.thread_uuid = ct.thread_uuid
	AND ct.thread_id = :thread_id
	AND ct.customer_id = :customer_id";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("thread_id", $thread_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		
		echo json_encode(array("success"=>true, "thread_id"=>$thread_id));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
	}
	exit();
}
function getThreadBodies($thread_id) {
	session_write_close();
	
	$sql = "
	SELECT 
			msg.*, msg.message_id id, msg.message_uuid uuid, IFNULL(cse.case_id, -1) case_id,
			IFNULL(cmu_to.to_user_uuids, '') to_user_uuids, 
			IFNULL(cmu_to.to_user_names, '') to_user_names, 
			IFNULL(cmu_to.read_dates, '') read_dates, 
			IFNULL(cmu_to.to_nicknames, '') to_nicknames,
			IFNULL(cmu_to.to_types, '') to_types, 
			IFNULL(ccd.message_attachments, '') message_attachments
		FROM `cse_message` msg
        
		INNER JOIN cse_thread_message tmes
		ON msg.message_id = tmes.message_id
		
		INNER JOIN `cse_thread` thr
		ON tmes.thread_uuid = thr.thread_uuid
		
		LEFT OUTER JOIN cse_case_message ccm
		ON msg.message_uuid = ccm.message_uuid
		
		LEFT OUTER JOIN cse_case cse
		ON ccm.case_uuid = cse.case_uuid
		
		LEFT OUTER JOIN (
			SELECT msg.message_uuid, 
			GROUP_CONCAT(DISTINCT cu.user_uuid
				ORDER BY cu.nickname ASC, mu.read_date DESC
				SEPARATOR '|') to_user_uuids,
			GROUP_CONCAT(cu.nickname
				ORDER BY cu.nickname ASC, mu.read_date DESC
				SEPARATOR '|') to_nicknames,
			GROUP_CONCAT(mu.read_status
				ORDER BY cu.nickname ASC, mu.read_date DESC
				SEPARATOR '|') to_statuses,
			GROUP_CONCAT(cu.user_name
				ORDER BY cu.nickname ASC, mu.read_date DESC
				SEPARATOR '|') to_user_names,
			GROUP_CONCAT(mu.read_date
				ORDER BY cu.nickname ASC, mu.read_date DESC
				SEPARATOR '|') read_dates,
			GROUP_CONCAT(mu.`type`
				ORDER BY cu.nickname ASC, mu.read_date DESC
				SEPARATOR '|') to_types
			
			FROM `cse_message` msg
			
			INNER JOIN `cse_message_user` mu
			ON msg.message_id = mu.message_id AND (mu.`type` = 'to' OR mu.`type` = 'cc' OR mu.`type` = 'bcc')
			AND mu.customer_id = " . $_SESSION['user_customer_id'] . "
			INNER JOIN 	ikase.cse_user cu
			ON mu.user_id = cu.user_id
			
			WHERE 1
			
			AND cu.customer_id = " . $_SESSION['user_customer_id'] . "
			AND msg.customer_id = " . $_SESSION['user_customer_id'] . "
			AND INSTR(CONCAT(msg.message_to, msg.message_cc, msg.message_bcc), cu.nickname) > 0
			GROUP BY msg.message_uuid
			HAVING 
				INSTR(GROUP_CONCAT(DISTINCT mu.user_uuid), '" . $_SESSION['user_id'] . "') > 0
				
		) cmu_to
		ON msg.message_uuid = cmu_to.message_uuid
		
		LEFT OUTER JOIN (
			SELECT icmd.message_uuid, GROUP_CONCAT(DISTINCT icd.document_filename) message_attachments
			FROM `cse_message_document` icmd
			INNER JOIN `cse_message` im
			ON icmd.message_uuid = im.message_uuid
			INNER JOIN `cse_document` icd
			ON icmd.document_uuid = icd.document_uuid
			WHERE 1
			GROUP BY message_uuid
		) ccd
		ON msg.message_uuid = ccd.message_uuid
		
		WHERE thr.thread_id = :thread_id
		AND thr.customer_id = " . $_SESSION['user_customer_id'] . "
		AND msg.deleted = 'N'
		
		ORDER BY msg.dateandtime DESC";
		
	try {
		
		if ($_SERVER['REMOTE_ADDR']=='47.153.49.248') {
			//die($sql);
		}
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("thread_id", $thread_id);
		$stmt->execute();
		$messages = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		//mark the last message in the thread as "read"
		$sql = "UPDATE 
		cse_message_user cmu, cse_message cm,
		(
			SELECT ct.thread_uuid, MAX(cm.message_id) max_message_id
			FROM cse_thread ct
			INNER JOIN cse_thread_message ctm
			ON ct.thread_uuid = ctm.thread_uuid
			INNER JOIN cse_message cm
			ON ctm.message_id = cm.message_id
			where ct.thread_id = :thread_id
			AND (CONCAT(cm.message_to, ';', cm.message_cc, ';', cm.message_bcc) LIKE '%" . $_SESSION["user_nickname"] . "%' OR cm.`from` = '" . $_SESSION["user_name"] . "')
		) max_message
		
		SET cmu.read_status = 'Y', cmu.read_date = :read_date
		
		WHERE cm.message_id = max_message.max_message_id
		AND cm.message_uuid = cmu.message_uuid
		
		AND cmu.user_uuid = :user_id";
		
		$read_date = date("Y-m-d H:i:s");
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("thread_id", $thread_id);
		$stmt->bindParam("read_date", $read_date);
		$stmt->bindParam("user_id",  $_SESSION["user_id"]);
		$stmt->execute();
		//echo $_SESSION["user_id"];
		//die($sql);
        echo json_encode($messages);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	exit();
}
function getMessageBody($message_id) {
	session_write_close();
    $sql = "SELECT msg.*, msg.message_id id, msg.message_uuid uuid, cse.case_id
		FROM `cse_message` msg
		LEFT OUTER JOIN cse_case_message ccm
		ON msg.message_uuid = ccm.message_uuid
		LEFT OUTER JOIN cse_case cse
		ON ccm.case_uuid = cse.case_uuid
		WHERE msg.message_id = :message_id
		AND msg.customer_id = " . $_SESSION['user_customer_id'] . "
		ORDER BY msg.message_id DESC";
	//die($sql);	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("message_id", $message_id);
		$stmt->execute();
		$message = $stmt->fetchObject();

		//added by mukesh on 9-5-2023
		if(isset($message->message_cc) && !empty($message->message_cc))
		{
			echo "<small>&nbsp;CC:" . $message->message_cc . '</small>';
		}
		if(isset($message->message_bcc) && !empty($message->message_bcc))
		{
			echo "<small>&nbsp;BCC:" . $message->message_bcc . '</small>';
		}
		//end added by mukesh
        echo '<p>' . $message->message . '</p>';     
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	exit();
}
function getMessageInfo($message_id, $return = "", $message_uuid = "") {
	session_write_close();
	$customer_id =  $_SESSION['user_customer_id'];
	$dbname = "";
	if (isset($_SESSION["dbname"]) ) {
		$dbname = "`" . $_SESSION["dbname"] . "`.";
	}
	if ($return != "") {
		$dbname = $return;
	}
    $sql = "SELECT msg.*, msg.message_id id, msg.message_uuid uuid, IFNULL(cse.case_id, '') case_id,
		IFNULL(cont.contact_id, -1) contact_id
		FROM " . $dbname . "`cse_message` msg
		LEFT OUTER JOIN " . $dbname . "cse_case_message ccm
		ON msg.message_uuid = ccm.message_uuid
		LEFT OUTER JOIN " . $dbname . "cse_case cse
		ON ccm.case_uuid = cse.case_uuid
		LEFT OUTER JOIN " . $dbname . "cse_message_contact cmc
		ON msg.message_uuid = cmc.message_uuid
		LEFT OUTER JOIN " . $dbname . "cse_contact cont
		ON cmc.contact_uuid = cont.contact_uuid";
	if ($message_uuid=="") {
		$sql .= "
		WHERE msg.message_id = :message_id";
	} else {
		$sql .= "
		WHERE msg.message_uuid = :message_uuid";
	}
	$sql .= "
		AND msg.customer_id = :customer_id
		ORDER BY msg.message_id DESC";
	//die($sql);	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($message_uuid=="") {
			$stmt->bindParam("message_id", $message_id);
		} else {
			$stmt->bindParam("message_uuid", $message_uuid);
		}
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$message = $stmt->fetchObject();

        return $message;     
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function explodeRecipient($value, &$arrTo, &$arrToID, $db) {
	//take the value, make into array
	$to = $value;
	if ($to=="-1") {
		$to = "";
	}
	$to = str_replace(",", ";", $to);
	$arrTo = explode(";", $to);
	$arrTo = array_unique($arrTo);
	//lookup recipients
	$arrToID = array();
	$arrCheck = $arrTo;
	foreach($arrCheck as $to_index=>$to) {
		if ($to=="") {
			continue;
		}
		if (!isValidEmail($to)) {
			$db = getConnection();
			
			//lookup
			$sql = "SELECT user_uuid, nickname 
			FROM ikase.cse_user 
			WHERE 1";
			if (is_numeric($to)) {
				$sql .= " AND `user_id` = '" . $to . "'";
			} else {
				$sql .= " AND `nickname` = '" . $to . "'";
			}
			$sql .= " AND `customer_id` = " . $_SESSION['user_customer_id'];
			//echo $sql . "<br />";
			$stmt = DB::run($sql);
			$recipient = $stmt->fetchObject();
			//echo $recipient->nickname . "\r\n";
			//die(print_r($recipient));
			if (is_object($recipient)) {
				$recipient_nickname = $recipient->nickname;
				$recipient_id = $recipient->user_uuid;
				
				$arrToID[$recipient_nickname] = $recipient_id;
				$arrTo[$to_index] = $recipient_nickname;
			}
		}
	}
	
}
function attachRecipients($table_name, $table_uuid, $last_updated_date, $arrRecipientID, $type, $db, $read_status = "N", $thread_uuid = "", $message_id = "", $user_id = "") {
	session_write_close();
	//clear any recipients not on the list
	$sql = "UPDATE cse_" . $table_name ."_user
	SET deleted = 'Y'
	WHERE 1
	AND `" . $table_name . "_uuid` = '" . $table_uuid . "'
	AND `user_uuid` NOT IN ('" . implode("','", $arrRecipientID) . "')
	AND `type` = '" . $type . "'";
	
	$stmt = DB::run($sql);
	
	foreach ($arrRecipientID as $user_uuid) {
		//get the user_id
		$sql = "SELECT user_id 
		FROM ikase.cse_user
		WHERE user_uuid = :user_uuid";
		
		$stmt = $db->prepare($sql);
		$stmt->bindParam("user_uuid", $user_uuid);
		$stmt->execute();
		$user_info = $stmt->fetchObject();
		
		$user_id = $user_info->user_id;
		
		//then check if the recipient is already attached
		$sql = "SELECT count(*) user_count FROM cse_" . $table_name ."_user
		WHERE 1
		AND `" . $table_name . "_uuid` = '" . $table_uuid . "'
		AND `user_uuid` = '" . $user_uuid . "'
		AND `type` = '" . $type . "'";
		
		$stmt = DB::run($sql);
		$user_count = $stmt->fetchObject();
		
		if ($user_count->user_count == 0) {
			$the_uuid = uniqid("TD", false);
			
			
			$sql = "INSERT INTO cse_" . $table_name ."_user (`" . $table_name . "_user_uuid`, `" . $table_name . "_uuid`, `user_uuid`, `thread_uuid`, `type`, `last_updated_date`, `last_update_user`, `customer_id`";
			if ($read_status=="Y") {
				$sql .= ", `read_status`, `read_date`";
			}
			if ($message_id != "") {
				$sql .= ", message_id, user_id";
			}
			$sql .= ")";
			$sql .= " VALUES ('" . $the_uuid  ."', '" . $table_uuid . "', '" . $user_uuid . "', '" . $thread_uuid . "', '" . $type . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "'";
			
			if ($read_status=="Y") {
				$sql .= ", 'Y', '" . date("Y-m-d H:i:s") . "'";
			}
			if ($message_id != "") {
				$sql .= ",'" . $message_id . "', '" . $user_id . "'";	
			}
			$sql .= ")";
			
			try {
				$stmt = DB::run($sql);
			} catch(PDOException $e) {
				echo json_encode(array("attach error"=>$e->getMessage(), "sql"=>$sql));
			}
		}
	}
}
function sendEmail($table_uuid, $from_address, $from_name, $arrRecipients, $arrEmailTo, $arrEmailCc, $arrEmailBcc, $subject, $message, $db, $attachments, $customer_id = "") {
	
	if ($customer_id == "") {
		$customer_id = $_SESSION['user_customer_id'];
	}
	
	//getEmailInfo
	//email it
	$sql_buffer = "INSERT INTO `cse_buffer` (`message_uuid`, `from`, `from_address`, `recipients`, `to`, `cc`, `bcc`, `subject`, `message`, `attachments`, `customer_id`)
		VALUES ('" . $table_uuid . "','" . $from_name . "','" . $from_address . "','" . implode(";", $arrRecipients) . "','" . implode(";", $arrEmailTo) . "','" . implode(";", $arrEmailCc) . "','" . implode(";", $arrEmailBcc) . "','" . addslashes($subject) . "','" . addslashes($message) . "', '" . $attachments . "', '" . $customer_id . "')";
		
	if ($_SERVER['REMOTE_ADDR']=='47.153.49.248') {
	//	echo $sql_buffer . "<br />";
	}
	try {
		$stmt = DB::run($sql_buffer);
		
		//merge all the emails
		$arrEmailAll = array_merge($arrEmailTo, $arrEmailCc, $arrEmailBcc);
		
		
		//if ($_SERVER['REMOTE_ADDR']=='71.119.40.148') {
			//die(print_r($arrEmailAll));
			$mess = getMessageInfo("", "", $table_uuid);
						
			$message_id = $mess->id;
			$message_uuid = $table_uuid;
			foreach($arrEmailAll as $index=>$email_contact) {
				//is it already in the database
				$sql = "SELECT COUNT(contact_id) contact_count
				FROM cse_contact
				WHERE `email` = '" . $email_contact . "'
				AND user_uuid = '" . $_SESSION["user_id"] . "'
				AND customer_id = " . $_SESSION["user_customer_id"];
				
				
				$stmt = DB::run($sql);
				$contact = $stmt->fetchObject();
				
				//if ($contact->contact_count==0) {
					if ($index < 10) {
						$index = "K" . $index;
					}
					if ($email_contact!="") {
						$contact_uuid = uniqid($index);
						//insert emails in contacts for this user
						$sql = "INSERT INTO `cse_contact`
						(`contact_uuid`, `user_uuid`, `email`, `customer_id`)
						SELECT '" . $contact_uuid . "', '" . $_SESSION["user_id"] . "', '" . $email_contact . "', '" . $_SESSION["user_customer_id"] . "'
						FROM dual
						WHERE NOT EXISTS (
								SELECT * 
								FROM `cse_contact` 
								WHERE `email` = '" . $email_contact . "'
								AND `user_uuid` = '" . $_SESSION["user_id"] . "'
								AND customer_id = '" . $_SESSION["user_customer_id"] . "'
							)";
						//die($sql);
						$stmt = DB::run($sql);
						$count = $stmt->rowCount();
						if ($count > 0) {
							$contact_id = DB::lastInsertId();
							
							trackContact("insert", $contact_id);
						} else {
							$contact = getContactInfoByEmail($email_contact);
							$contact_uuid = $contact->uuid;
						}
						$message_contact_uuid = uniqid("MC", false);
						
						$attribute = "from";
						if ($mess->message_type=="email") {
							$attribute = "to";
						}
						$last_updated_date = date("Y-m-d H:i:s");
						
						$sql = "INSERT INTO cse_message_contact (`message_contact_uuid`, `message_uuid`, `message_id`, `contact_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
						VALUES ('" . $message_contact_uuid . "', '" . $message_uuid . "', '" . $message_id . "', '" . $contact_uuid . "', '" . $attribute . "', '" . $last_updated_date . "', :user_uuid, :customer_id)";
						//echo $sql . "\r\n";
						
						$stmt = $db->prepare($sql);  
						$stmt->bindParam("user_uuid", $_SESSION["user_id"]);
						$stmt->bindParam("customer_id", $_SESSION["user_customer_id"]);
						$stmt->execute();
					}
				//}
			}
		//}
		return true;
	} catch(PDOException $e) {
		echo '{"error":{"send error text":'. $e->getMessage() .'}}'; 
		die();
	}
}
function getScanNotifications() {
	session_write_close();
	
	$customer_id = $_SESSION["user_customer_id"];
	$user_uuid = $_SESSION['user_id'];
	
	$sql = "
	SELECT DISTINCT cd.*
	FROM `cse_notification` cn
	INNER JOIN `cse_document` cd
	ON cn.document_uuid = cd.document_uuid AND cn.notification = 'review' AND cn.deleted = 'N'";

	$sql .= " 
	INNER JOIN `ikase`.`cse_user` cu
	ON cn.user_uuid = cu.user_uuid AND cu.user_uuid = :user_uuid
	
	INNER JOIN cse_batchscan cb
	ON cd.parent_document_uuid = cb.batchscan_id AND cb.deleted ='N'
	INNER JOIN cse_batchscan_track cbt
	ON cb.batchscan_id = cbt.batchscan_id AND cbt.operation = 'insert'
	
	INNER JOIN `cse_case_document` cdoc
	ON cd.document_uuid = cdoc.document_uuid
	INNER JOIN `cse_case` ccase
	ON cdoc.case_uuid = ccase.case_uuid AND cdoc.deleted = 'N'
	
	WHERE cd.customer_id = :customer_id
	AND cd.deleted = 'N'
	AND cn.notifier != ''
	AND cb.stitched != 'unassigned' ";
	
	//die($sql);
	try {
		$db = getConnection();
		
		$stmt = $db->prepare($sql);
		$stmt->bindParam("user_uuid", $user_uuid);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$notifications = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		echo json_encode($notifications);
	} catch(PDOException $e) {
		echo '{"error":{"send error text":'. $e->getMessage() .'}}'; 
		die();
	}
}
function addNotification() {
	session_write_close();
	
	$db = getConnection();
	
	//retrieve the user and the document to get uuids
	$document_id = passed_var("document_id", "post");
	$message_to = passed_var("message_to", "post");
	$instructions = passed_var("instructions", "post");
	
	if ($message_to=="") {
		return false;	
	}
	$document = getDocumentInfo($document_id);
	$to = getUserInfo($message_to);
	
	try {
		//remove notification for me, completed as far as I'm concerned
		$sql = "UPDATE cse_notification
		SET notification = 'completed',
		notification_date = '" . date("Y-m-d H:i:s") . "'
		WHERE document_uuid = '" . $document->uuid . "'
		AND user_uuid = '" . $_SESSION["user_id"] . "'
		AND notification = 'review'";
		
		$stmt = DB::run($sql);

		$notification_uuid = uniqid("KN", false);
		//insert the notification for user
		$sql = "INSERT INTO `cse_notification` (`document_uuid`, `notification_uuid`, `user_uuid`, 
		`notification`, `notification_date`, `instructions`,
		`notifier`, `customer_id`)
		VALUES ('" . $document->uuid . "', '" . $notification_uuid . "', '" . $to->user_uuid . "', 
		'review', '" . date("Y-m-d H:i:s") . "', '" . addslashes($instructions) . "', '" . $_SESSION["user_nickname"] . "', '" . $_SESSION["user_customer_id"] . "')";
		
		$stmt = DB::run($sql);
				
        echo json_encode(array("success"=>true));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	exit();
}
function setMessageAttachments() {
	session_write_close();
	//die(print_r($_POST));
	$customer_id = passed_var("customer_id", "post");
	
	$sql_customer = "SELECT cus_name, data_source, permissions
	FROM  `ikase`.`cse_customer` 
	WHERE customer_id = :customer_id";
	try {				
		$db = getConnection();
		$stmt = $db->prepare($sql_customer);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$customer = $stmt->fetchObject();
		
		//echo print_r($customer);
		if (!is_object($customer)) {
			die("no go");
		}
		$cus_name = $customer->cus_name;
		$data_source = $customer->data_source;
		if ($data_source=="") {
			$return = "ikase";
		}
		if ($data_source!="") {
			$return = "ikase_" . $data_source;
		}
				
		//truncate
		$sql = "TRUNCATE `" . $return . "`.`cse_message_attachments`;";
		 
		//refill
		$sql .= "
		INSERT INTO `" . $return . "`.`cse_message_attachments`
		SELECT im.message_id, icmd.message_uuid, GROUP_CONCAT(icd.document_filename) message_attachments, '" . date("Y-m-d H:i:s") . "' created
		FROM `" . $return . "`.`cse_message_document` icmd
		INNER JOIN `" . $return . "`.`cse_message` im
		ON icmd.message_uuid = im.message_uuid
		INNER JOIN `" . $return . "`.`cse_document` icd
		ON icmd.document_uuid = icd.document_uuid
		WHERE 1
		GROUP BY message_uuid';";
		//echo $sql . "\r\n";
		
		$sql .= "
		TRUNCATE `" . $return . "`.`cse_message_from`";
		
		$sql .= "
		INSERT INTO `" . $return . "`.`cse_message_from`
		SELECT message_id,  cmu.user_id, `user`.user_name
        FROM `" . $return . "`.`cse_message_user` cmu
        LEFT OUTER JOIN `ikase`.`cse_user` `user`
		ON cmu.user_id = `user`.`user_id`
        WHERE 1
        AND `type` = 'from'";
		
		//echo $sql . "\r\n";
		$stmt = DB::run($sql);
		
		
		echo json_encode(array("success"=>true));
		
		exit();
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function checkEmailMessage($customer_id = "", $user_id="", $messageId = "", $from = "") {
	$blnInternal = true;
	if ($customer_id=="") {
		$blnInternal = false;
		// NISHIT REPLACE IP FROM 173.58.194.150 TO ikase.xyz
		if ($_SERVER['REMOTE_ADDR']!="ikase.xyz" && $_SERVER['REMOTE_ADDR']!="173.58.194.146" && $_SERVER['REMOTE_ADDR']!="173.58.194.148" && $_SERVER['REMOTE_ADDR']!="71.254.171.237" && $_SERVER['SERVER_NAME']!="v4.ikase.org") {
			echo $_SERVER['REMOTE_ADDR'] . "\r\n";
			die("no go 1...");
		}
		$customer_id = passed_var("customer_id", "post");
		$user_id = passed_var("user_id", "post");
		$messageId = passed_var("id", "post");
		$from = passed_var("from", "post");
	}
	//$messageId = urldecode($messageId);
	
	if (!is_numeric($customer_id)) {
		die("no id");
	}
	
	$_SESSION['user_customer_id'] = $customer_id;
	$_SESSION['user_plain_id'] = $user_id;
	
	//get the user_uuid
	$user = getUserInfo($user_id);	
	$_SESSION["user_id"] = $user->user_uuid;
	
	$sql_customer = "SELECT cus_name, data_source, permissions
	FROM  `ikase`.`cse_customer` 
	WHERE customer_id = :customer_id";
	try {				
		$db = getConnection();
		$stmt = $db->prepare($sql_customer);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$customer = $stmt->fetchObject();
		
		//echo print_r($customer);
		if (!is_object($customer)) {
			die("no go");
		}
		$cus_name = $customer->cus_name;
		$data_source = $customer->data_source;
		if ($data_source=="") {
			$return = "ikase";
		}
		if ($data_source!="") {
			$return = "ikase_" . $data_source;
		}
		
		$_SESSION["dbname"] = $return;
		
		session_write_close();
		
		$the_count = 0;
		//is the sender on the blocked list
		$sql = "SELECT spam_status 
		FROM cse_contact
		WHERE `email` = :from";
		$sql .= " AND `customer_id` = :customer_id
		ORDER BY contact_id DESC
		LIMIT 0, 1";
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("from", $from);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$spam_status = $stmt->fetchObject();
		if (is_object($spam_status)) {
			$contact_status = $spam_status->spam_status;
		}
				
		//does the message exist?
		$sql = "SELECT * FROM `" . $return . "`.`cse_message` 
		WHERE `message_uuid` = '" . $messageId . "'";
		$sql .= " AND `customer_id` = " . $customer_id;
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		//$stmt->bindParam("messageId", $messageId);
		//$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$message = $stmt->fetchObject();
		if (is_object($message)) {
			$the_count = 1;
		}
		if (!$blnInternal) {
			echo json_encode(array("success"=>true, "id"=>$messageId, "count"=>$the_count, "contact_status"=>$contact_status));
		} else {
			return json_encode(array("success"=>true, "id"=>$messageId, "count"=>$the_count, "contact_status"=>$contact_status));
		}
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function autoAssignMessage($message_id, $customer_id, $return = "") {
	if (isset($_SESSION['user_customer_id'])) {
		$_SESSION['user_id'] = 'system';
		$_SESSION['user_name'] = 'system';
		$_SESSION['user_customer_id'] = $customer_id;
	}
	session_write_close();
	
	$blnEcho = false;
	if ($return=="") {
		$blnEcho = true;
		//now let's find the customer
		$sql_customer = "SELECT cus_name, data_source, permissions
		FROM  `ikase`.`cse_customer` 
		WHERE customer_id = :customer_id";
		try {				
			$db = getConnection();
			$stmt = $db->prepare($sql_customer);
			$stmt->bindParam("customer_id", $customer_id);
			$stmt->execute();
			$customer = $stmt->fetchObject();
			
			//echo print_r($customer);
			if (!is_object($customer)) {
				die("no go");
			}
			$cus_name = $customer->cus_name;
			$data_source = $customer->data_source;
			if ($data_source=="") {
				$return = "ikase";
			}
			if ($data_source!="") {
				$return = "ikase_" . $data_source;
			}
		} catch(PDOException $e) {	
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}
	}
	
	try {
		//does the message exist?
		$message = getMessageInfo($message_id, $return . ".");
	
		$contents = $message->message;
		$message_uuid = $message->message_uuid;
		
		$blnAssociated = (strpos($contents, " // ID")!==false);
		
		if ($blnAssociated) {
			
			//die($contents);
			//break up the message line by line
			$arrLines = explode("<br>", $contents);
			foreach($arrLines as $line) {
				$line_string = trim($line);
				if (strpos($line_string, "RE: ")!==false) {
					$blnCorrectLine = (strpos($line_string, " // ID")!==false);
					if ($blnCorrectLine) {
						$arrParts = explode(" // ID", $line_string);
						//die(print_r($arrParts));
						$case_name = trim(str_replace("RE:", "", $arrParts[0]));;
						$search_item = trim($arrParts[1]);
						
						$sql = "SELECT case_id, case_uuid
						FROM `" . $return . "`.`cse_case` 
						WHERE case_id = :search_item
						AND INSTR(case_name, :case_name) > 0
						AND deleted = 'N'
						AND `customer_id` = :customer_id";
						
						$db = getConnection();
						$stmt = $db->prepare($sql);
						$stmt->bindParam("search_item", $search_item);
						$stmt->bindParam("case_name", $case_name);
						$stmt->bindParam("customer_id", $customer_id);
						$stmt->execute();
						$kase = $stmt->fetchObject();
						
						if (is_object($kase)) {
							//die(print_r($kase));
							$case_uuid = $kase->case_uuid;
							
							$last_updated_date = date("Y-m-d H:i:s");
							//associate with kase if not already so
							$case_message_uuid = uniqid("TD", false);
							$sql = "INSERT INTO `" . $return . "`.cse_case_message (`case_message_uuid`, `case_uuid`, `message_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
							SELECT '" . $case_message_uuid  ."', :case_uuid, :message_uuid, 'main', '" . $last_updated_date . "', '" . $_SESSION["user_id"] . "', :customer_id
							FROM dual 
							WHERE NOT EXISTS (
								SELECT ccm.*
								FROM `" . $return . "`.cse_case_message ccm
								WHERE case_uuid = :case_uuid  AND message_uuid = :message_uuid
							)";
							//echo $sql . "<br />";	
							//die();
							$db = getConnection();	
							$stmt = $db->prepare($sql);
							$stmt->bindParam("case_uuid", $case_uuid);
							$stmt->bindParam("message_uuid", $message_uuid);
							$stmt->bindParam("customer_id", $customer_id);  
							$stmt->execute();
							
							$sql = "UPDATE `" . $return . "`.cse_message mes
							SET mes.`status` = ''
							WHERE 1
							AND mes.message_uuid = :message_uuid
							AND mes.customer_id = :customer_id";
							
							$db = getConnection();	
							$stmt = $db->prepare($sql);
							$stmt->bindParam("message_uuid", $message_uuid);
							$stmt->bindParam("customer_id", $customer_id);  
							$stmt->execute();
							$count_affected = $stmt->rowCount();
							
							if ($count_affected > 0) {
								//track it
								trackMessage("assigned", $message_id, true, $return . ".");
							}
							
							//create note from message
							$arrFields = array();
							$arrSet = array();
							
							$arrFields[] = "`note`";
							$arrSet[] = "'" . addslashes($contents) . "'";
							$arrFields[] = "`title`";
							$arrSet[] = "'" . addslashes($message->subject) . "'";
							$arrFields[] = "`subject`";
							$arrSet[] = "'" . addslashes($message->subject) . "'";
							$arrFields[] = "`attachments`";
							$arrSet[] = "'" . $message->attachments . "'";
							//explodeRecipient($head['to'], $arrTo, $arrToID, $db);
							//explodeRecipient($head['cc'], $arrCc, $arrCcID, $db);
									
							$notes_uuid = uniqid("MN", false);
							//combine 
							$sql = "
							INSERT INTO `" . $return . "`.`cse_notes` (`customer_id`, `entered_by`, `notes_uuid`, " . implode(",", $arrFields) . ") 
							VALUES(:customer_id, :user_name, '" . $notes_uuid . "', " . implode(",", $arrSet) . ")";
							
							$db = getConnection();	
							$stmt = $db->prepare($sql);
							$stmt->bindParam("user_name", $_SESSION['user_name']);
							$stmt->bindParam("customer_id", $customer_id);  
							$stmt->execute();
							
							$case_table_uuid = uniqid("KA", false);
							//now we have to attach the note to the case 
							$sql = "INSERT INTO `" . $return . "`.`cse_case_notes` 
							(`case_notes_uuid`, `case_uuid`, `notes_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
							VALUES ('" . $case_table_uuid  ."', :case_uuid, :notes_uuid, :message_uuid, '" . $last_updated_date . "', :user_uuid, :customer_id)";
							
							$db = getConnection();	
							$stmt = $db->prepare($sql);
							$stmt->bindParam("case_uuid", $case_uuid);
							$stmt->bindParam("notes_uuid", $notes_uuid);
							$stmt->bindParam("message_uuid", $message_uuid);
							$stmt->bindParam("user_uuid", $_SESSION['user_id']);
							$stmt->bindParam("customer_id", $customer_id);  
							$stmt->execute();
							
							if ($blnEcho) {
								echo json_encode(array("success"=>true, "message"=>$message_id . " auto assigned to case " . $kase->case_id));	
							}
						}
					}
					break;
				}
			}
		}
		
	} catch(PDOException $e) {	
		echo $sql . "\r\n";
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function addEmailMessage($arrMessage = "") {
	// echo "<br/>twoooooooooooooooooooooooooooooo<br/>";
	// print_r($arrMessage);
	// echo "<br/>threeeeeeeeeeeeeeeeeeeeeeeeeeeee<br/>";
	//die();
	if (!is_array($arrMessage)) {
		$arrMessage = array();
	}
	
	$case_id = "";
	if (count($arrMessage)==0) {
		// NISHIT REPLACE IP FROM 173.58.194.150 TO ikase.xyz
		if ($_SERVER['REMOTE_ADDR']!="ikase.xyz"  
		&& $_SERVER['REMOTE_ADDR']!="173.58.194.146" 
		&& $_SERVER['REMOTE_ADDR']!="173.58.194.148" 
		&& $_SERVER['REMOTE_ADDR']!="71.254.171.237" 
		&& $_SERVER['SERVER_NAME']!="v4.ikase.org"
		&& $_SERVER['SERVER_NAME']!="www.ikase.org") {
			echo $_SERVER['SERVER_NAME'] . "\r\n";
			die("no go 2...");
		}
		//die(print_r($_POST));
	
		$customer_id = passed_var("customer_id", "post");
		$user_id = passed_var("user_id", "post");
		$user_name = passed_var("user_name", "post");
		$message = passed_var("message", "post");
		$attachments = passed_var("attachments", "post");
		$table_uuid = passed_var("messageId", "post");
		$thread_uuid = passed_var("threadId", "post");
		$sender = passed_var("messageSender", "post");
		$receiver = passed_var("messageReceiver", "post");
		$subject = passed_var("messageSubject", "post");
		$snippet = passed_var("messageSnippet", "post");
		$attachments = passed_var("attachments", "post");
		$message_cc = passed_var("messageCc", "post");
		$message_bcc = passed_var("messageBcc", "post");
		
		
		$message_body = urldecode($_POST["messageBody"]);
		// echo "<br/>4<br/>";
		// print_r($customer_id);
		// echo "<br/>5<br/>";
	} else {
		$case_id = $arrMessage["case_id"];
		$customer_id = $arrMessage["customer_id"];
		$user_id = $arrMessage["user_id"];
		$user_name = $arrMessage["user_name"];
		$message = $arrMessage["message"];
		$attachments = $arrMessage["attachments"];
		$table_uuid = $arrMessage["messageId"];
		$thread_uuid = $arrMessage["threadId"];
		$sender = $arrMessage["messageSender"];
		$receiver = $arrMessage["messageReceiver"];
		$subject = $arrMessage["messageSubject"];
		$snippet = $arrMessage["messageSnippet"];
		$attachments = $arrMessage["attachments"];
		$messageCc = $arrMessage["messageCc"];
		$messageBcc = $arrMessage["messageBcc"];
		
		$message_body = urldecode($arrMessage["messageBody"]);
		// echo "<br/>6<br/>";
		// print_r($attachments);
		// echo "<br/>7<br/>";
	}
	// echo "<br/>8<br/>";
	// print_r($customer_id);
	// print_r(is_numeric($customer_id));
	// print_r(!is_numeric($customer_id));
	//echo "<br/>9<br/>";
	if (!is_numeric($customer_id)) {
		
		die("no id");
	
	}
	/*
	$_SESSION['user_customer_id'] = $customer_id;
	$_SESSION['user_plain_id'] = $user_id;
	$_SESSION['user_name'] = $user_name;
	
	//get the user_uuid
	$user = getUserInfo($user_id);	
	$_SESSION["user_id"] = $user->user_uuid;
	*/
	//$user = getUserInfo($user_id);	
	//echo "user:" . $user_id . "\r\n";
	//die(print_r($user));
	$sql_customer = "SELECT cus_name, data_source, permissions
	FROM  `ikase`.`cse_customer` 
	WHERE customer_id = :customer_id";
	try {				
		$db = getConnection();
		$stmt = $db->prepare($sql_customer);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$customer = $stmt->fetchObject();
		
		// echo "<br/>customer:<br/>";
		// echo print_r($customer);
		// echo "<br/>";
		if (!is_object($customer)) {
			die("no go");
		}
		$cus_name = $customer->cus_name;
		$data_source = $customer->data_source;
		if ($data_source=="") {
			$return = "ikase";
		}
		if ($data_source!="") {
			$return = "ikase_" . $data_source;
		}
		
		$message_body = @processHTML($message_body);
		
		//case
		if ($case_id!="") {
			$kase = getKaseInfo($case_id, $return);
			
			$message_body = "RE: " . $kase->name . " // ID " . $kase->id . "
			" . $message_body;
			//die($message_body );
		}
		
		//first make sure that it is not already in the database
		$sql = "SELECT message_id
		FROM `" . $return . "`.`cse_message`
		WHERE message_uuid = :message_uuid
		AND customer_id = :customer_id
		LIMIT 0, 1";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("message_uuid", $table_uuid);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$original = $stmt->fetchObject();
		// echo "<br/>original:<br/>";
		// echo print_r($original);
		// echo "<br/>";
		if (is_object($original)) {
			//the email is already in the system
			echo "<br/>the email is already in the system<br/>";
			autoAssignMessage($original->message_id, $customer_id, $return);
			echo json_encode(array("sql"=>$sql, "success"=>true, "id"=>$original->message_id));
			// return;
			exit;
		}
		//$_SESSION["dbname"] = $return;
		//session_write_close();
		//get email address
		$sql = "SELECT usr.user_id, usr.user_uuid, usr.nickname, email_name 
		FROM `" . $return . "`.cse_email email
		INNER JOIN `" . $return . "`.cse_user_email euser
		ON email.email_uuid = euser.email_uuid
		INNER JOIN ikase.cse_user usr
		ON euser.user_uuid = usr.user_uuid
		WHERE usr.user_id = '" . $user_id . "'
		AND usr.customer_id = " . $customer_id;
		
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("user_uuid", $user);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$recipient = $stmt->fetchObject();
		// echo "<br/>recipient:<br/>";
		// echo print_r($recipient);
		// echo "<br/>";

		$_SESSION['user_id'] = $recipient->user_uuid;
		$_SESSION['user_plain_id'] = $recipient->user_id;
		$_SESSION['user_name'] = $user_name;
		$_SESSION['user_customer_id'] = $customer_id;
		
		session_write_close();
		
		$message_date = date("Y-m-d H:i:s", strtotime(passed_var("messageDate", "post")));
		
		// In email want to mail id 20-April-2023
		 $message_to = $recipient->nickname;
		//$message_to = $receiver;
		
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
		$stmt = DB::run($sql);
		// echo "<br/>";
		// echo "stmt:";
		// echo "<br/>";
		// echo print_r($stmt);
		// echo "<br/>";

		//message
		$sql = "INSERT INTO `" . $return . "`.`cse_message` (`message_uuid`, `message_type`, `message`, `subject`, 
		`snippet`, `dateandtime`, `from`, `message_to`, `attachments`, `status`, `customer_id`, `receiver`, `message_cc`, `message_bcc`) ";
		$sql .= " VALUES(:messageId, 'email', :messageBody, :messageSubject, :messageSnippet, :messageDate, :messageSender, :message_to, :attachments, 'created', :customer_id, :receiver, :message_cc, :message_bcc)";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		// echo "<br/>";
		// echo "stmttt:";
		// echo "<br/>";
		// echo print_r($stmt);
		// echo "<br/>";
		//die($stmt);

		//die("Tab:" . $table_uuid);
		$stmt->bindParam("messageId", $table_uuid);
		$stmt->bindParam("messageBody", $message_body);
		$stmt->bindParam("messageSubject", $subject);
		$stmt->bindParam("messageSnippet", $snippet);
		$stmt->bindParam("messageDate", $message_date);
		$stmt->bindParam("messageSender", $sender);
		//$stmt->bindParam("destination", passed_var("destination", "post"));
		$stmt->bindParam("message_to", $message_to);
		$stmt->bindParam("attachments", $attachments);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("receiver", $receiver);
		$stmt->bindParam("message_cc", $message_cc);
		$stmt->bindParam("message_bcc", $message_bcc);
		

		echo "<br/>7<br/>";
		print_r($attachments);
		echo "<br/>8<br/>";
		//die(print_r($stmt));
		
		$stmt->execute();
		$new_id = $db->lastInsertId();
		
		//track now
		$operation = "insert";
		trackMessage($operation, $new_id, true, $return . ".");	
		
		//attach to thread
		$thread_message_uuid = uniqid("TD", false);
		
		$last_updated_date = date("Y-m-d H:i:s");
		$sql = "INSERT INTO `" . $return . "`.cse_thread_message (`thread_message_uuid`, `thread_uuid`, `message_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`, message_id)
		VALUES ('" . $thread_message_uuid  ."', '" . $thread_uuid . "', '" . $table_uuid . "', 'main', '" . $last_updated_date . "', '" . $user_id . "', '" . $customer_id . "', '" . $new_id . "')";
		
		echo $sql . "\r\n";
		$stmt = DB::run($sql);
		
		//attach to user
		$message_user_uuid = uniqid("MU", false);
		$sql = "INSERT INTO `" . $return . "`.cse_message_user (`message_user_uuid`, `message_uuid`, `user_uuid`, `type`, `last_updated_date`, `last_update_user`, `customer_id`, `thread_uuid`, message_id, user_id)
		VALUES ('" . $message_user_uuid  ."', '" . $table_uuid . "', '" . $_SESSION['user_id'] . "', 'to', '" . $last_updated_date . "', '" . $user_id . "', '" . $customer_id . "', '". $thread_uuid . "', '" . $new_id . "', '" . $_SESSION["user_plain_id"] . "')";
		echo $sql . "\r\n";
		
		$stmt = DB::run($sql);
		
		autoAssignMessage($new_id, $customer_id, $return);
		if ($case_id=="") {
			//, "session"=>$_SESSION
			echo json_encode(array("sql"=>$sql, "success"=>true, "id"=>$new_id));
		}
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function addMessage() {

	//die("here");
	$arrFields = array();
	$arrSet = array();
	$table_name = "";
	$to = "";
	$cc = "";
	$bcc = "";
	$from = "";	
	$subject = "";
	$table_id = "";
	$case_id = "";
	$case_uuid = "";
	$source_message_id = "";
	$send_document_id = "";
	$thread_uuid = "";
	$message = "";
	$signature = "";
	$attachments = "";
	$apply_notes = "";
	$specialty = "";
	$blnAttachments = true;
	$arrAttachedCaseDocuments = array();
	$attachment_case = "";
	$email_footer_value = "";
	$arrTo = array();
	$arrToID = array();
	$arrCc = array();
	$arrCcID = array();
	$arrBcc = array();
	$arrBccID = array();
	$blnEmailIt = false;
	$blnNotify = false;

	$kinvoice_id = "";
	$kinvoice_path = "";
	$kinvoice_uuid = "";
	$kinvoice_invoiced_id = "";
	$kinvoice_invoiced_type = "";
	
	//reply, forward
	$reaction = "";
	$deleted = "";
	//die(print_r($_POST));
	$arrExistingDocuments = array();
	
	foreach($_POST as $fieldname=>$value) {
		$clean_val = $value;
		
		if (strpos($fieldname, "reminder_") > -1 || strpos($fieldname, "recurrent_") > -1 ) {
			continue;
		}
		if ($fieldname!="messageInput") {
			$value = passed_var($fieldname, "post");	
		} else {
			if (strpos($fieldname, "emailaddress") > -1) {
				continue;
			}
			if (strlen($_POST["messageInput"]) > 0) {
				$value = @processHTML($_POST["messageInput"]);
			} else {
				$value = "";
			}
			
			$message = $value;
			$crlf = "<br />
			";
			$message = str_replace("||", $crlf, $message);
		}
		$fieldname = str_replace("Input", "", $fieldname);
		
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="message_to") {
			if ($value=="VocationalStaff") {
				$value = "cavoucher@gmail.com,voucherQRR@gmail.com,stvpineda@gmail.com,nick@kustomweb.com";
				//$value = "nick@kustomweb.com";
			}
		}
		//signature for email
		if ($fieldname=="signature") {
			//$signature = @processHTML($_POST["signature"]);
			$signature = $value;			
			$arrSign = explode("\r\n", $signature);
			if (count($arrSign)==1) {
				$arrSign = explode("\n", $signature);
			}
			if (count($arrSign)==1) {
				$arrSign = explode(chr(13), $signature);
			}
			$signature = "<div>" . implode("</div><div>", $arrSign) .  "</div>";
		
			continue;
		}
		if ($fieldname=="kinvoice_id") {
			$kinvoice_id = $value;
			if ($kinvoice_id!="") {
				$kinvoice = getKInvoiceInfo($kinvoice_id);
				$kinvoice_uuid = $kinvoice->kinvoice_uuid;
			}
			continue;
		}
		if ($fieldname=="kinvoice_document_id" || $fieldname=="kinvoice_path" || $fieldname=="kinvoice_invoiced_id" || $fieldname=="kinvoice_invoiced_type") {
			continue;
		}
		
		if ($fieldname=="billing_time") {
			continue;
		}
		if ($fieldname=="deleted") {
			$deleted = $value;
		}
		
		//could be an update to a draft
		if ($fieldname=="table_id") {
			$table_id = $value;
			continue;
		}
		//part of a thread
		if ($fieldname=="thread_uuid") {
			$thread_uuid = $value;
			continue;
		}
		if ($fieldname=="specialty") {
			$specialty = $value;
			continue;
		}
		if ($fieldname=="reaction") {
			if ($value!="compose") {
				$reaction = $value;
			}
			continue;
		}
		if ($fieldname=="case_file" || $fieldname=="case_id") {
			$case_id = $value;
			if ($case_id!="") {
				$kase = getKaseInfo($case_id);
				if (is_object($kase)) {
					$case_uuid = $kase->uuid;
				}
			}
			continue;
		}
		if ($fieldname=="apply_notes") {
			$apply_notes = $value;
			continue;
		}
		if ($fieldname=="source_message_id") {
			$source_message_id = $value;
			continue;
		}
		if ($fieldname=="send_document_id") {
			$send_document_id = $value;
			if ($send_document_id!="") {
				$send_document = getDocumentInfo($send_document_id);
				$attachment_filename = $send_document->document_filename;
				if ($send_document->case_id!="") {
					$attachment_filename = $send_document->case_id . "/" . $attachment_filename;
				}
				
				if ($attachments=="") {
					$attachments = "D:/uploads/" . $_SESSION['user_customer_id'] . "/" . $attachment_filename;
				} else {
					$attachments .= "|D:/uploads/" . $_SESSION['user_customer_id'] . "/" . $attachment_filename;
				}
				
				$arrAttach = explode("/", $attachments);
				$arrExistingDocuments[] = $arrAttach[count($arrAttach) - 1];
				$blnAttachments = false;
			}
			
			continue;
		}

		if ($fieldname=="attach_document_id") {
			$arrSendDocumentsID = explode("|", $value);
			foreach($arrSendDocumentsID as $attach_document_id) {
				if ($attach_document_id!="") {
					$attach_document = getDocumentInfo($attach_document_id);
					$attach_document_case_id = $attach_document->case_id;
					
					//store the document object in an array
					$arrAttachedCaseDocuments[] = $attach_document;
					
					$path = findDocumentFolder($_SESSION['user_customer_id'], $case_id, $attach_document->document_filename, $attach_document->type, $attach_document->thumbnail_folder, $attach_document_id);
					
					if ($attachment_case=="") {
						/*
						$attachment_case = "D:/uploads/" . $_SESSION['user_customer_id'] . "/";
						if ($attach_document_case_id!="") {
							$attachment_case .= $attach_document_case_id . "/";
						}
						$attachment_case .= $attach_document->document_filename;
						*/
						
						
						if ($path != false) {
							$attachment_case = $path;
						}
					} else {
						/*
						$attachment_case .= "|D:/uploads/" . $_SESSION['user_customer_id'] . "/";
						if ($attach_document_case_id!="") {
							$attachment_case .= $attach_document_case_id . "/";
						}
						$attachment_case .= $attach_document->document_filename;
						*/
						if ($path != false) {
							$attachment_case .= "|" . $path;
						}
					}
				}
			}
			continue;
		}

		if ($fieldname=="case_id" || $fieldname=="case_uuid" || $fieldname=="table_id" || $fieldname=="colleague" || $fieldname=="event_priority" || $fieldname=="event_type" || $fieldname=="event_dateandtime" || $fieldname=="full_address" || $fieldname=="street" || $fieldname=="city" || $fieldname=="state" || $fieldname=="zip" || $fieldname=="documents_count" || $fieldname=="task_assignee" || $fieldname=="calendar_id" || $fieldname=="injury_id" || $fieldname=="user_id" || $fieldname=="number_of_days" || $fieldname=="event_duration") {
			continue;
		}
		if ($fieldname=="notification") {
			if ($value=="Y") {
				$blnNotify = true;
			}
			continue;
		}
		if ($fieldname=="attachments") {
			if (!$blnAttachments) {
				continue;
			}
			if ($value!="") {
				$attachments = $value;
			} else {
				continue;
			}

		}
		
		//special condition when they want to do a Send All
		if ($fieldname=="select_all_clients") {
			if ($value=="Y") {
				//must load all the client emails into arrTo
				$sql = "SELECT DISTINCT pers.email
				FROM `cse_person` pers 
				WHERE pers.deleted = 'N'
				AND pers.`email` != ''
				AND pers.customer_id = " . $_SESSION['user_customer_id'] . "
				ORDER by pers.person_id";
				$db = getConnection();			
				$stmt = $db->query($sql);
				$persons = $stmt->fetchAll(PDO::FETCH_OBJ);
				
				$arrTo = array();
				foreach($persons as $person) {
					$arrTo[] = $person->email;
				}
				
				$blnEmailIt = true;
				
				$arrFields[] = "`message_to`";
				$arrSet[] = "'all_clients'";
			}
			continue;
		}
		if ($fieldname=="message_to") {
			if ($value=="All") {
				$value = "";
			}
			$db = getConnection();
			
			explodeRecipient($value, $arrTo, $arrToID, $db);
			
			foreach($arrTo as $toindex=>$thisto) {
				if ($thisto=="") {
					unset($arrTo[$toindex]);
				}
			}
			if (count($arrTo) > 0) {
				if ($to=="") {
					$to = implode(";", $arrTo);
				} else {
					$to .= ";" . implode(";", $arrTo);
				}
			}

			$value = $to;
			//should we be emailing
			if (strpos($value, "@") > -1 && strpos($value, ".") > -1) {
				$blnEmailIt = true;
			}
		}
		
		if ($fieldname=="message_cc") {
			explodeRecipient($value, $arrCc, $arrCcID, $db);
			foreach($arrCc as $toindex=>$thiscc) {
				if ($thiscc=="") {
					unset($arrCc[$toindex]);
				}
			}
			if (count($arrCc) > 0) {
				if ($cc=="") {
					$cc = implode(";", $arrCc);
				} else {
					$cc .= ";" . implode(";", $arrCc);
				}
			}

			$value = $cc;
			//should we be emailing
			if (strpos($value, "@") > -1 && strpos($value, ".") > -1) {
				$blnEmailIt = true;
			}
		}
		if ($fieldname=="message_bcc") {
			explodeRecipient($value, $arrBcc, $arrBccID, $db);
			foreach($arrBcc as $toindex=>$thisbcc) {
				if ($thisbcc=="") {
					unset($arrBcc[$toindex]);
				}
			}
			if (count($arrBcc) > 0) {
				if ($bcc=="") {
					$bcc = implode(";", $arrBcc);
				} else {
					$bcc .= ";" . implode(";", $arrBcc);
				}
			}

			$value = $bcc;
			//should we be emailing
			if (strpos($value, "@") > -1 && strpos($value, ".") > -1) {
				$blnEmailIt = true;
			}
		}
		//part of a thread
		if ($fieldname=="subject") {
			$subject = $value;
			$subject = addslashes($subject);
		}
		if ($fieldname=="from") {
			$from = $value;
		}
		/*
		if ($fieldname=="message") {
			$message = $value;
			$message = addslashes($message);
		}
		*/
		if ($fieldname=="dateandtime") {
			if ($value!="") {
				$value = date("Y-m-d H:i:s", strtotime($value));
			} else {
				$value = "0000-00-00 00:00:00";
			}
		}
		if ($fieldname=="callback_date") {
			if ($value!="") {
				$value = date("Y-m-d H:i:s", strtotime($value));
			} else {
				$value = "0000-00-00 00:00:00";
			}
		}
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "'" . addslashes($value) . "'";
	}
	
	//print_r($arrFields);
	//die(print_r($arrSet));
	//let's get the parent uuid if any
	if ($source_message_id != "") {
		if (strpos($source_message_id, "vservice")===false) {
			$source_message = getMessageInfo($source_message_id);
		} else {
			$key = md5(microtime());
			$injury_id = explode("_", $source_message_id);
			$injury_id = $injury_id[2];
			$sql = "INSERT INTO ikase.cse_downloads (`downloadkey`, `sent_by`, `injury_id`, `file`, `expires`, `customer_id`) 
			VALUES ('" . $key . "', '" . $_SESSION['user_plain_id'] . "', '" . $injury_id . "', 'downloads/injury_sheet.php', '" . date("Y-m-d H:i:s", (time()+(60*60*24*7))) ."', '" . $_SESSION['user_customer_id'] ."')";
			$stmt = DB::run($sql);
			//first add the doctor if any
			if ($specialty!="") {
				$message .= "\r\n\r\nDoctor Type Needed: " . $specialty;
			}
			$message .= "\r\n\r\nhttps://www.ikase.org/down.php?key=" . $key;
		}
	}
	$email_message = $message;
	
	//it's an email, tack on the case name to the value
	if ($case_id > 0) {
		if (is_object($kase)) {
			$email_message = "RE: " . $kase->name . " // ID " . $kase->id . "
			
" . $email_message;
		}
	}
	
	if ($signature!="") {
		
		//$email_message .= "<div> </div><div>";
		/*
		$email_message .= "<br><br>";
		$signature = str_replace("\r\n", "<br>", $signature);
		$signature = str_replace("\n", "<br>", $signature);
		$signature = str_replace(chr(13), "<br>", $signature);
		*/
		$email_message .= $signature;
	}
	
	
	if ($email_footer_value!="") {
		$email_message .= "\r\n\r\n" . $email_footer_value;
	}
	
	$table_uuid = uniqid("KS", false);
	$last_updated_date = date("Y-m-d H:i:s");
	
	//identify email recipients
	$arrEmailTo = array();
	$arrEmailCc = array();
	$arrEmailBcc = array();
	if ($blnEmailIt) {
		$arrEmailTo = explode(";", $to);
		foreach($arrEmailTo as $email_index=>$email_to) {
			$email_to = trim($email_to);
			if (!isValidEmail($email_to)) {
				unset($arrEmailTo[$email_index]);
			} else {
				//reset just in case
				$arrEmailTo[$email_index] = $email_to;
			}
		}
		$arrEmailTo = array_values($arrEmailTo);
		$arrEmailCc = explode(";", $cc);
		foreach($arrEmailCc as $email_index=>$email_cc) {
			$email_cc = trim($email_cc);
			if (!isValidEmail($email_to)) {
				unset($arrEmailCc[$email_index]);
			} else {
				//reset just in case
				$arrEmailCc[$email_index] = $email_cc;
			}
		}
		$arrEmailCc = array_values($arrEmailCc);
		
		$arrEmailBcc = explode(";", $bcc);
		foreach($arrEmailBcc as $email_index=>$email_bcc) {
			$email_bcc = trim($email_bcc);
			if (!isValidEmail($email_bcc)) {
				unset($arrEmailBcc[$email_index]);
			} else {
				//reset just in case
				$arrEmailBcc[$email_index] = $email_bcc;
			}
		}
		$arrEmailBcc = array_values($arrEmailBcc);
	}

	// added by mukesh for send email attachment
	if($attachments != "")
	{
		if($attachment_case != "")
		{
			$attachments .= "|" . $attachment_case;
		}
	}
	else
	{
		if($attachment_case != "")
		{
			$arrFields[] = "`attachments`";
			$attachments = $attachment_case;
		}
	}

	if($attachments!= "")
	{
		$arrThisAttachments = explode("|", $attachments);
		$arrUnique = array_unique($arrThisAttachments);
		$attachments = implode("|", $arrUnique);

		$arrSet[8] = "'" . $attachments . "'";
	}
	// end add by mukesh, below code commented as well
	
	// if ($attachments!="" || $attachment_case!="") {
	// 	if (!in_array("`attachments`", $arrFields)) { 
	// 		$arrFields[] = "`attachments`";
	// 		if ($attachment_case!="") {
	// 			if ($attachments!="") {
	// 				$arrSet[] = "'" . $attachments . "|" . $attachment_case . "'";
	// 				$attachments .= "|" . $attachment_case;
					
	// 				//no duplicates
	// 				$arrThisAttachments = explode("|", $attachments);
	// 				$arrUnique = array_unique($arrThisAttachments);
	// 				$attachments = implode("|", $arrUnique);
	// 			} else {
	// 				$arrSet[] = "'" . $attachment_case . "'";
	// 				$attachments = $attachment_case;
	// 			}
	// 		} else {
	// 			$arrSet[] = "'" . $attachments . "'";
	// 		}
	// 	}
	// }
	
	if($_SERVER['REMOTE_ADDR']=='47.153.49.248') {
		//echo $attachments;
		//die(print_r($arrAttachedCaseDocuments));
	}
	//insert the message
	$sql = "INSERT INTO `cse_" . $table_name ."` (`customer_id`, `dateandtime`, `" . $table_name . "_uuid`, " . implode(",", $arrFields) . ") 
			VALUES('" . $_SESSION['user_customer_id'] . "', '" . date("Y-m-d H:i:s") . "','" . $table_uuid . "', " . implode(",", $arrSet) . ")";
	
	$sql_prelim = "";
	try {
		if ($table_id!="") {
			$db = getConnection();
			//if they passed a table_id, need to mark it as deleted
			$sql_prelim = "UPDATE `cse_" . $table_name ."` 
			SET deleted = 'Y' 
			WHERE message_id = " . $table_id . "
			AND deleted = 'D'
			AND customer_id = " . $_SESSION["user_customer_id"];
			
			//die(json_encode(array("sql"=>$sql_prelim)));  
			
			$stmt = DB::run($sql_prelim);
		}
		DB::run($sql);
	$new_id = DB::lastInsertId();
		
		//is it a reaction to a previous email
		//get previous dates if any
		if ($reaction!='' && isset($source_message) && is_object($source_message)) {
			$original_reply_date = "0000-00-00";
			$original_forward_date = "0000-00-00";
			
			$sql = "SELECT reply_date, forward_date
			FROM `cse_message_reaction`
			WHERE message_uuid = '" . $source_message->uuid . "'
			AND user_uuid = '" . $_SESSION["user_id"] . "'
			AND customer_id = " . $_SESSION["user_customer_id"];
			//echo $sql . "\r\n";
			$stmt = DB::run($sql);
			$original_reaction = $stmt->fetchObject();
			
			if (is_object($original_reaction)) {
				$original_reply_date = $original_reaction->reply_date;
				$original_forward_date = $original_reaction->forward_date;
			}
			
			//delete any previous entries, the deleteds will serve as history
			$sql = "UPDATE `cse_message_reaction`
			SET `deleted` = 'Y'
			WHERE message_uuid = '" . $source_message->uuid . "'
			AND user_uuid = '" . $_SESSION["user_id"] . "'
			AND customer_id = " . $_SESSION["user_customer_id"];
			//echo $sql . "\r\n";
			$stmt = DB::run($sql);
			$reply_date = "0000-00-00 00:00:00";
			$forward_date = "0000-00-00 00:00:00";
			
			if ($reaction == "reply") {
				$reply_date = date("Y-m-d H:i:s");
				$forward_date = $original_forward_date;
			}
			
			if ($reaction == "forward") {
				$reply_date = $original_reply_date;
				$forward_date = date("Y-m-d H:i:s");
			}
			$sql = "INSERT INTO `cse_message_reaction` (`message_uuid`, `user_uuid`, `customer_id`, `reply_date`, `forward_date`)
			VALUES ('" . $source_message->uuid . "', '" . $_SESSION["user_id"] . "', '" . $_SESSION["user_customer_id"] . "', '" . $reply_date . "', '" . $forward_date . "')";
			//echo $sql . "\r\n";
			$stmt = DB::run($sql);
		}
		$attribute_1 = "attach";
		if ($blnNotify) {
			$attribute_1 = "notify";
		}
		//let's get send document details if any
		//$arrMessageAttach = explode("/", $attachments);
		//$message_attachments = $arrMessageAttach[count($arrMessageAttach) - 1];
		$message_attachments = $attachments;
		if ($send_document_id != "") {
			$message_document_uuid = uniqid("TD", false);
			$sql = "INSERT INTO cse_message_document (`message_document_uuid`, `message_uuid`, `document_uuid`, `attribute_1`, `attribute_2`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $message_document_uuid  ."', '" . $table_uuid . "', '" . $send_document->document_uuid . "', '" . $attribute_1 . "', '', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			$stmt = DB::run($sql);
			if ($case_uuid!="") {
				$sql = "INSERT INTO cse_case_document (`case_document_uuid`, `case_uuid`, `document_uuid`, `attribute_1`, `attribute_2`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $message_document_uuid  ."', '" . $case_uuid . "', '" . $send_document->document_uuid . "', '" . $attribute_1 . "', '', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
				$stmt = DB::run($sql);
			}
			if (!$blnNotify) {
				//clear out attachments
				$attachments = "";
			}
			
			//if it's a stack, hide it
			if ($subject=="Document Imported") {
				$sql = "UPDATE `cse_document` 
				SET verified = 'Y'
				WHERE document_uuid = '" . $send_document->document_uuid . "'
				AND customer_id = '" . $_SESSION['user_customer_id'] . "'";
				
				$stmt = DB::run($sql);
			}
		}
		
		//attach case documents
		if (count($arrAttachedCaseDocuments) > 0) {
			foreach($arrAttachedCaseDocuments as $attached_document) {
				$message_document_uuid = uniqid("TD", false);
				$sql = "INSERT INTO cse_message_document (`message_document_uuid`, `message_uuid`, `document_uuid`, `attribute_1`, `attribute_2`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $message_document_uuid  ."', '" . $table_uuid . "', '" . $attached_document->document_uuid . "', '" . $attribute_1 . "', '', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
				
				$stmt = DB::run($sql);
				
				if ($case_uuid!="") {
					$sql = "INSERT INTO cse_case_document (`case_document_uuid`, `case_uuid`, `document_uuid`, `attribute_1`, `attribute_2`, `last_updated_date`, `last_update_user`, `customer_id`)
					VALUES ('" . $message_document_uuid  ."', '" . $case_uuid . "', '" . $attached_document->document_uuid . "', '" . $attribute_1 . "', '', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
					$stmt = DB::run($sql);
				}
			}
		}
		//attach attachments
		if ($attachments!="") {
			$arrAttachments = explode("|", $attachments);
			foreach ($arrAttachments as $attachment) {
				$document_name = $attachment;
				$document_name = explode("/", $document_name);
				$document_name = $document_name[count($document_name) - 1];
				if (in_array($document_name, $arrExistingDocuments)) {
					continue;
				}
				$document_date = date("Y-m-d H:i:s");
				$document_extension = explode(".", $document_name);
				$document_extension = $document_extension[count($document_extension) - 1];
				$customer_id = $_SESSION["user_customer_id"];
				$description = "message attachment";
				$description_html = "message attachment";
				$type = "message attachment";
				$verified = "Y";
				
				//attachment is a document
				$document_uuid = uniqid("KS");
				$sql = "INSERT INTO cse_document (document_uuid, parent_document_uuid, document_name, document_date, document_filename, document_extension, description, description_html, type, verified, customer_id) 
			VALUES (:document_uuid, :parent_document_uuid, :document_name, :document_date, :document_filename, :document_extension, :description, :description_html, :type, :verified, :customer_id)";
				$db = getConnection();
				
				$stmt = $db->prepare($sql);  
				$stmt->bindParam("document_uuid", $document_uuid);
				$stmt->bindParam("parent_document_uuid", $document_uuid);
				$stmt->bindParam("document_name", $document_name);
				$stmt->bindParam("document_date", $document_date);
				$stmt->bindParam("document_filename", $document_name);
				$stmt->bindParam("document_extension", $document_extension);
				$stmt->bindParam("description", $description);
				$stmt->bindParam("description_html", $description_html);
				$stmt->bindParam("type", $type);
				$stmt->bindParam("verified", $verified);
				$stmt->bindParam("customer_id", $customer_id);
				$stmt->execute();
				$new_document_id = $db->lastInsertId();
				//die(print_r($newEmployee));
				trackDocument("insert", $new_document_id);
				
				$message_document_uuid = uniqid("TD", false);
				$sql = "INSERT INTO cse_message_document (`message_document_uuid`, `message_uuid`, `document_uuid`, `attribute_1`, `attribute_2`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $message_document_uuid  ."', '" . $table_uuid . "', '" . $document_uuid . "', 'attach', '', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
				$stmt = DB::run($sql);
				
				if ($case_uuid!="") {
					$sql = "INSERT INTO cse_case_document (`case_document_uuid`, `case_uuid`, `document_uuid`, `attribute_1`, `attribute_2`, `last_updated_date`, `last_update_user`, `customer_id`)
					SELECT '" . $message_document_uuid  ."', '" . $case_uuid . "', '" . $document_uuid . "', '" . $attribute_1 . "', '', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "'
					FROM dual
						WHERE NOT EXISTS (
							SELECT * 
							FROM `cse_case_document` 
							WHERE case_uuid = '" . $case_uuid . "'
							AND document_uuid = '" . $document_uuid . "'
							AND customer_id = '" . $_SESSION['user_customer_id'] . "'
						)";
					$stmt = DB::run($sql);
				}
			}
		}
		//attach to the case
		if ($case_id!="") {
			//attach the from
			$case_message_uuid = uniqid("TD", false);
			$sql = "INSERT INTO cse_case_message (`case_message_uuid`, `case_uuid`, `message_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			SELECT '" . $case_message_uuid  ."', `case_uuid`, '" . $table_uuid . "', 'main', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "'
			FROM cse_case 
			WHERE case_id = " . $case_id . "
			AND customer_id = " . $_SESSION['user_customer_id'];
			//echo $sql . "<br />";	
			$stmt = DB::run($sql);
		}
		
		//invoice?
		if ($kinvoice_uuid!="") {
			$message_kinvoice_uuid = uniqid("MS", false);
			
			//insert into cse_message_kinvoice
			$sql = "INSERT INTO `cse_message_kinvoice`
			(`message_kinvoice_uuid`, `message_uuid`, `kinvoice_uuid`, `attribute_1`, `attribute_2`, `last_updated_date`, `last_update_user`,
`customer_id`)
			VALUES ('" . $message_kinvoice_uuid . "', '" . $table_uuid . "', '" . $kinvoice_uuid . "', 'main', 'draft', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			
			$stmt = DB::run($sql);
	
		}
		if ($thread_uuid == "") {
			//insert a thread
			$thread_uuid = uniqid("TD", false);
			
			$sql = "INSERT INTO `cse_thread` (`customer_id`, `dateandtime`, `thread_uuid`, `from`, `subject`) 
					VALUES('" . $_SESSION['user_customer_id'] . "', '" . date("Y-m-d H:i:s") . "','" . $thread_uuid . "', '" . $from . "', '" . $subject . "')";
			//die($sql);			
			try { 
				$stmt = DB::run($sql);
				
				$message_table_uuid = uniqid("KA", false);
				$last_updated_date = date("Y-m-d H:i:s");
				//now we have to attach the message to the thread
				$sql = "INSERT INTO cse_thread_" . $table_name . " (`thread_" . $table_name . "_uuid`, `thread_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`, `message_id`)
				VALUES ('" . $message_table_uuid  ."', '" . $thread_uuid . "', '" . $table_uuid . "', 'main', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "', '" . $new_id . "')";
				
				$stmt = DB::run($sql);
			} catch(PDOException $e) {
				echo json_encode(array("text"=>$e->getMessage(), "sql"=>$sql));
			}
		} else {
			try { 
				$message_table_uuid = uniqid("KA", false);
				$last_updated_date = date("Y-m-d H:i:s");
				//now we have to attach the message to the thread
				$sql = "INSERT INTO cse_thread_" . $table_name . " (`thread_" . $table_name . "_uuid`, `thread_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`, `message_id`)
				VALUES ('" . $message_table_uuid  ."', '" . $thread_uuid . "', '" . $table_uuid . "', 'main', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "', '" . $new_id . "')";
				
				$stmt = DB::run($sql);
			} catch(PDOException $e) {
				//echo '{"error":{"text":'. $e->getMessage() .'}}'; 
				echo json_encode(array("text"=>$e->getMessage(), "sql"=>$sql));
			}
		}
		
		//attach the from
		$message_user_uuid = uniqid("TD", false);
		$sql = "INSERT INTO cse_message_user (`message_user_uuid`, `message_uuid`, `user_uuid`, `type`, `last_updated_date`, `last_update_user`, `customer_id`, `thread_uuid`, message_id, user_id)
		VALUES ('" . $message_user_uuid  ."', '" . $table_uuid . "', '" . $_SESSION['user_id'] . "', 'from', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "', '". $thread_uuid . "', '" . $new_id . "', '" . $_SESSION["user_plain_id"] . "')";
		
		try {
			$stmt = DB::run($sql);
		} catch(PDOException $e) {
			echo json_encode(array("text"=>$e->getMessage(), "sql"=>$sql));
		}
		
		$db = getConnection();
		//attach recipients to message
		attachRecipients('message', $table_uuid, $last_updated_date, $arrToID, 'to', $db, "N", $thread_uuid, $new_id, $_SESSION["user_plain_id"]);
		attachRecipients('message', $table_uuid, $last_updated_date, $arrCcID, 'cc', $db, "N", $thread_uuid, $new_id, $_SESSION["user_plain_id"]);
		attachRecipients('message', $table_uuid, $last_updated_date, $arrBccID, 'bcc', $db, "N", $thread_uuid, $new_id, $_SESSION["user_plain_id"]);
		
		echo json_encode(array("success"=>"true", "id"=>$new_id));
		
		//combine the To, Cc, and Bcc arrays, and then send out
		$arrRecipients = array_merge($arrEmailTo, $arrEmailCc, $arrEmailBcc);
		
		//if we have email
		$operation = "insert";
		if (count($arrEmailTo) > 0) {
                     
			$myfile = fopen("../email_module/".$_SESSION["user_id"].".txt", "r") or die("Unable to open file!");
			$_SESSION['user_email'] = fread($myfile, filesize("../email_module/".$_SESSION["user_id"].".txt"));
			fclose($myfile);
			
			// by mukesh on 24-4-23 for sending from email
			$from_address = passed_var("fromInput", "post"); //$_SESSION['user_email'];
			$from_name = $_SESSION['user_name'];
			$attachments = str_replace("https:///uploads/", "D:/uploads/", $message_attachments);
			
			$operation = "sent";
			if ($deleted!="") {
				if ($deleted=="D") {
					$operation = "draft";
				}
			}
			if ($operation == "sent") {
		
				
				$blnSent = sendEmail($table_uuid, $from_address, $from_name, $arrRecipients, $arrEmailTo, $arrEmailCc, $arrEmailBcc, $subject, $email_message, $db, $attachments);
			}
		}
		//track now
		trackMessage($operation, $new_id);	
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .', "sql":' . $sql . '}}'; 
		//echo json_encode(array("text"=>$e->getMessage(), "sql"=>$sql));
	}
}
function readMessage() {
	session_write_close();
	
	$id = passed_var("id", "post");
	$customer_id = $_SESSION["user_customer_id"];
	$user_uuid = $_SESSION["user_id"];
	$user_id = $_SESSION["user_plain_id"];
	
	try {
			//first check if there is a message_user record
		$sql = "SELECT COUNT(mes.message_id) message_count
		FROM cse_message mes, cse_message_user cmu
		WHERE mes.`message_id`= cmu.message_id
		AND (cmu.user_uuid = '" . $_SESSION["user_id"] . "' OR cmu.user_uuid = '" . $_SESSION["user_plain_id"] . "')
		AND (cmu.type = 'to' OR cmu.type = 'cc' OR cmu.type = 'bcc')
		AND mes.message_id = :id
		AND mes.customer_id = :customer_id";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$message_user = $stmt->fetchObject();
		
		$sql_insert = "";
		
		if ($message_user->message_count == 0) {
			//need to insert the message_user record
			$case_message_uuid = uniqid("CM", false);
			
			$sql = "INSERT INTO cse_message_user (`message_user_uuid`, `message_uuid`, `user_uuid`, `thread_uuid`, `type`, `last_updated_date`, `last_update_user`, `customer_id`, message_id, user_id";
			$sql .= ")";
			$sql .= " SELECT '" . $case_message_uuid  ."', mes.`message_uuid`, :user_uuid, ctm.thread_uuid, 'to', '" . date("Y-m-d H:i:s") . "', 'system', :customer_id, :id, :user_id
			FROM cse_message mes
			INNER JOIN cse_thread_message ctm
			ON mes.message_uuid = ctm.message_uuid
			
			WHERE 1
			AND mes.message_id = :id
			AND mes.customer_id = :customer_id";
			
			$sql_insert = $sql;
			
			$db = getConnection();	
			$stmt = $db->prepare($sql);  
			$stmt->bindParam("id", $id);
			$stmt->bindParam("user_id", $user_id);
			$stmt->bindParam("user_uuid", $user_uuid);
			$stmt->bindParam("customer_id", $customer_id);
			$stmt->execute();
		}
		
		$sql = "UPDATE cse_message mes, cse_message_user cmu
		SET cmu.`read_status` = 'Y',
		cmu.read_date = '" . date("Y-m-d H:i:s") . "'
		WHERE mes.`message_id`= cmu.message_id
		AND (cmu.user_uuid = '" . $_SESSION["user_id"] . "' OR cmu.user_uuid = '" . $_SESSION["user_plain_id"] . "')
		AND (cmu.type = 'to' OR cmu.type = 'cc' OR cmu.type = 'bcc')
		AND mes.message_id = :id
		AND mes.customer_id = :customer_id";
			
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		
		echo json_encode(array("success"=>"message marked as read"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
	}
	
	//mark the remote as read if it's an email
	$message = getMessageInfo($id);
	
	if (is_object($message)) {
		$message_type = $message->message_type;
		if ($message_type=="email") {
			$uid = $message->uuid;
			
			$email_info = getEmailInfo($_SESSION['user_plain_id']);
			if(is_object($email_info)) {
				if ($email_info->email_server=="" || $email_info->email_pwd=="" || $email_info->email_port=="" || $email_info->email_address=="") {
					die();
				}
			} else {
				die();
			}
			//encrypt on the way out
			$authorize_key = "ikase.org";			
			$authorize_key = encryptAES($authorize_key);
			$credentials = json_encode($email_info);
			
			if(strpos($email_info->email_name, "@gmail.com") === false && $email_info->read_message=="Y") {
				//let's do this
				$ssl = ($email_info->ssl_required == "Y");
				$method = strtolower($email_info->email_method);
				
				//echo $email_info->email_pwd . "\r\n";
				
				$email_info->ssl = $ssl;
				$email_info->method = $method;
				
				// NISHIT REPLACE IP FROM 173.58.194.150 TO ikase.xyz
				$url = "http://ikase.xyz/ikase/limapi/email.php/seemail";
				//$url = "http://173.58.194.150/ikase/limapi/email.php/seemail";
		
				$fields = array("customer_id"=>urlencode($_SESSION['user_customer_id']), "user_id"=>urlencode($_SESSION['user_plain_id']), "user_name"=>urlencode($_SESSION['user_name']), "authorize_key"=>urlencode($authorize_key), "credentials"=>urlencode($credentials), "through"=>urlencode($email_info->email_pwd), "uid"=>$uid);
				
				//die(print_r($fields));
				$fields_string = "";
				foreach($fields as $key=>$value) { 
					$fields_string .= $key.'='.$value.'&'; 
				}
				rtrim($fields_string, '&');
				$timeout = 5;
				//die($fields_string);
				
				//open connection
				$ch = curl_init();
				
				//set the url, number of POST vars, POST data
				curl_setopt($ch, CURLOPT_URL,$url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
				curl_setopt($ch, CURLOPT_HEADER, false); 
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($ch, CURLOPT_COOKIEFILE, "cookies.txt");
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
				curl_setopt($ch, CURLOPT_POST, count($fields_string));
				curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
				//curl_setopt($ch,CURLOPT_FOLLOWLOCATION,TRUE);
				
				//execute post
				$result = curl_exec($ch);
				//die($result);
				
				$jresult = json_decode($result);
				
				if (!$jresult->success) {
					die("error marking $uid as read");
				}
			} else {
				/*
				$url = "http://173.58.194.150/ikase/gmail/ui/read_message.php";
		
				$fields = array("customer_id"=>urlencode($_SESSION['user_customer_id']), "user_id"=>urlencode($_SESSION['user_plain_id']), "user_name"=>urlencode($_SESSION['user_name']), "authorize_key"=>urlencode($authorize_key), "credentials"=>urlencode($credentials), "through"=>urlencode($email_info->email_pwd), "uid"=>$uid);
				
				//die(print_r($fields));
				$fields_string = "";
				foreach($fields as $key=>$value) { 
					$fields_string .= $key.'='.$value.'&'; 
				}
				rtrim($fields_string, '&');
				$timeout = 5;
				//die($fields_string);
				
				//open connection
				$ch = curl_init();
				
				//set the url, number of POST vars, POST data
				curl_setopt($ch, CURLOPT_URL,$url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
				curl_setopt($ch, CURLOPT_HEADER, false); 
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				curl_setopt($ch, CURLOPT_COOKIEFILE, "cookies.txt");
				curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
				curl_setopt($ch, CURLOPT_POST, count($fields_string));
				curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
				//curl_setopt($ch,CURLOPT_FOLLOWLOCATION,TRUE);
				
				//execute post
				$result = curl_exec($ch);
				die($result);
				*/
				//this will be done via js
			}
		}
	}
}
function updateMessage() {
	$arrSet = array();
	$where_clause = "";
	$table_name = "";
	$table_id = "";
	$deleted = "";
	$to = "";
	$cc = "";
	$bcc = "";
	
	$id = passed_var("table_id", "post");
	$message = getMessageInfo($id);
	//die($message);
	if ($message->deleted=="D") {
		//die("here");
		//this is really an add, it will delete the draft line 2345
		addMessage();
		return;
	}
	foreach($_POST as $fieldname=>$value) {
		if ($fieldname!="messageInput") {
			$value = passed_var($fieldname, "post");
		} else {
			//special case
			//remove script
			$value = @processHTML($_POST["messageInput"]);
		}
		$fieldname = str_replace("Input", "", $fieldname);
		//echo $fieldname . "<br />";
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="table_id") {
			if (!in_array("draft_id", $_POST) && $value!="") {
				$table_id = $value;
				$where_clause = " = " . $value;
				continue;
			} else {
				continue;
			}
		}
		if ($fieldname=="table_attribute") {
			$table_attribute = $value;
			continue;
		}
		if ($fieldname=="deleted") {
			$deleted = $value;
		}
		//signature for email
		if ($fieldname=="signature") {
			//$signature = @processHTML($_POST["signature"]);
			$signature = $value;			
			$arrSign = explode("\r\n", $signature);
			if (count($arrSign)==1) {
				$arrSign = explode("\n", $signature);
			}
			if (count($arrSign)==1) {
				$arrSign = explode(chr(13), $signature);
			}
			$signature = "<div>" . implode("</div><div>", $arrSign) .  "</div>";
		
			continue;
		}
		if ($fieldname=="kinvoice_id") {
			$kinvoice_id = $value;
			if ($kinvoice_id!="") {
				$kinvoice = getKInvoiceInfo($kinvoice_id);
				$kinvoice_uuid = $kinvoice->kinvoice_uuid;
			}
			continue;
		}
		if ($fieldname=="kinvoice_document_id" || $fieldname=="kinvoice_path" || $fieldname=="kinvoice_invoiced_id" || $fieldname=="kinvoice_invoiced_type") {
			continue;
		}
		$arrExclude = array("case_id", "case_file", "source_message_id", "send_document_id", "reaction", "task_assignee", "attach_document_id");
		if (in_array($fieldname, $arrExclude)) {
			continue;
		}
		//no uuids  || $fieldname=="case_uuid"
		if (strpos($fieldname, "_uuid") > -1) {
			continue;
		}
		if ($fieldname=="dateandtime" || $fieldname=="start_date" || $fieldname=="end_date" || $fieldname=="event_dateandtime" || $fieldname=="callback_date") {
			if ($value!="") {
				$value = date("Y-m-d H:i:s", strtotime($value));
			} else {
				continue;
			}
		}
		if ($fieldname=="message_to") {
			if ($value=="All") {
				$value = "";
			}
			$db = getConnection();
			
			explodeRecipient($value, $arrTo, $arrToID, $db);
			
			foreach($arrTo as $toindex=>$thisto) {
				if ($thisto=="") {
					unset($arrTo[$toindex]);
				}
			}
			if (count($arrTo) > 0) {
				if ($to=="") {
					$to = implode(";", $arrTo);
				} else {
					$to .= ";" . implode(";", $arrTo);
				}
			}

			$value = $to;
			//should we be emailing
			if (strpos($value, "@") > -1 && strpos($value, ".") > -1) {
				$blnEmailIt = true;
			}
		}
		if ($fieldname=="message_cc") {
			explodeRecipient($value, $arrCc, $arrCcID, $db);
			foreach($arrCc as $toindex=>$thiscc) {
				if ($thiscc=="") {
					unset($arrCc[$toindex]);
				}
			}
			if (count($arrCc) > 0) {
				if ($cc=="") {
					$cc = implode(";", $arrCc);
				} else {
					$cc .= ";" . implode(";", $arrCc);
				}
			}

			$value = $cc;
			//should we be emailing
			if (strpos($value, "@") > -1 && strpos($value, ".") > -1) {
				$blnEmailIt = true;
			}
		}
		if ($fieldname=="message_bcc") {
			explodeRecipient($value, $arrBcc, $arrBccID, $db);
			foreach($arrBcc as $toindex=>$thisbcc) {
				if ($thisbcc=="") {
					unset($arrBcc[$toindex]);
				}
			}
			if (count($arrBcc) > 0) {
				if ($bcc=="") {
					$bcc = implode(";", $arrBcc);
				} else {
					$bcc .= ";" . implode(";", $arrBcc);
				}
			}

			$value = $bcc;
			//should we be emailing
			if (strpos($value, "@") > -1 && strpos($value, ".") > -1) {
				$blnEmailIt = true;
			}
		}
		if ($fieldname=="draft_id") {
			$draft_id = $value;
			$table_id = $value;
			$where_clause = " = " . $value;
		} else {
			$arrSet[] = "`" . $fieldname . "` = '" . addslashes($value) . "'";
		}
	}
	//die(print_r($arrSet));
	$where_clause = "`" . $table_name . "_id`" . $where_clause;
	$sql = "
	UPDATE `cse_" . $table_name . "`
	SET " . implode(", ", $arrSet) . "
	WHERE " . $where_clause;
//	die( $sql . "\r\n");
	try {
		$stmt = DB::run($sql);
		//, "deleted"=>$deleted, "sql"=>$sql
		echo json_encode(array("success"=>true, "id"=>$table_id));
		
		$operation = "update";
		if ($deleted=="D") {
			$operation = "draft";
		}	
		trackMessage($operation, $table_id);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function deleteMessage($message_id = "") {
	
	if ($message_id == "") {
		$id = passed_var("id", "post");
	} else {
		$id = $message_id;
	}
	$sql = "UPDATE cse_message mes, cse_message_user cmu
			SET cmu.`deleted` = 'Y',
			cmu.last_updated_date = '" . date("Y-m-d H:i:s") . "',
			cmu.last_update_user = '" . $_SESSION["user_id"] . "'
			WHERE mes.`message_id`= cmu.message_id
			AND cmu.user_uuid = '" . $_SESSION["user_id"] . "'
			AND mes.message_id = :id
			AND mes.customer_id = '" . $_SESSION["user_customer_id"] . "'";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		
		if ($message_id == "") {
			echo json_encode(array("success"=>"message marked as deleted", "form_name"=>"messages"));
		}
		trackMessage("delete", $id);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function blockEmailThread() {
	$thread_id = passed_var("id", "post");
	$customer_id =  $_SESSION["user_customer_id"];
	$nickname = $_SESSION["user_nickname"];
	try {
		//first get the message id from the thread
		$sql = "SELECT mes.message_id 
		FROM cse_thread thr
		INNER JOIN cse_thread_message ctm
		ON thr.thread_uuid = ctm.thread_uuid
		INNER JOIN cse_message mes
		ON ctm.message_id = mes.message_id
		WHERE thr.thread_id = :thread_id
		AND thr.customer_id = :customer_id";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("thread_id", $thread_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$message = $stmt->fetchObject();
		
		$id = $message->message_id;
		
		$message = getMessageInfo($id);
		//die(print_r($message));
		
		if ($message->contact_id < 0) {
			$email_contact = $message->from;
			$arrContact = explode("|", $email_contact);
			$arrLength = count($arrContact) - 1;
			for($int = $arrLength; $int >= 0; $int--) {
				$thecontact = $arrContact[$int];
				if ($thecontact=="") {
					unset($arrContact[$int]);
				}
			}
			$email_contact = implode("", $arrContact);
			if ($email_contact!="") {
				//get the id for tracking
				$contact = getContactInfoByEmail($email_contact);
			}
		} else {
			$contact = getContactInfo($message->contact_id);
		}
		if (is_object($contact)) {
			//insert emails in contacts for this user
			$sql = "UPDATE `cse_contact`
			SET spam_status = 'BLOCKED'
			WHERE `email` = '" . $email_contact . "'
			AND `user_uuid` = '" . $_SESSION["user_id"] . "'
			AND customer_id = '" . $_SESSION["user_customer_id"] . "'";
			//die($sql);
			$stmt = DB::run($sql);
			
			trackContact("blocked", $contact->id);
		} else {
			//actually insert it, and then block it...
			$contact_uuid = uniqid("EM", false);
			//insert emails in contacts for this user
			$sql = "INSERT INTO `cse_contact`
			(`contact_uuid`, `user_uuid`, `email`, `customer_id`, spam_status, deleted)
			VALUES ('" . $contact_uuid . "', '" . $_SESSION["user_id"] . "', :email_contact, '" . $_SESSION["user_customer_id"] . "', 'BLOCKED', 'Y')";
			//die($sql);
			$db = getConnection();
			$stmt = $db->prepare($sql);  
			$stmt->bindParam("email_contact", $email_contact);
			$stmt->execute();
			$contact_id = $db->lastInsertId();
			
			//now we have an object for sure
			$contact = getContactInfo($contact_id);
			
			trackContact("insert", $contact_id);
			trackContact("blocked", $contact_id);
		}
		
		//delete the message
		$sql = "UPDATE cse_message mes, cse_message_user cmu
		SET cmu.`deleted` = 'Y', mes.deleted = 'Y',
		cmu.last_updated_date = '" . date("Y-m-d H:i:s") . "',
		cmu.last_update_user = '" . $_SESSION["user_id"] . "'
		WHERE mes.`message_id`= cmu.message_id
		AND cmu.user_uuid = '" . $_SESSION["user_id"] . "'
		AND mes.message_id = :id
		AND mes.customer_id = :customer_id";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		
		//actually delete any message associated with this contact for this user
		$contact_uuid = $contact->uuid;
		
		$sql = "UPDATE cse_message mes, cse_message_contact cmc
		SET mes.deleted = 'Y'
		WHERE mes.`message_id`= cmc.message_id
		AND mes.message_to = :nickname
		AND cmc.contact_uuid = :contact_uuid
		AND mes.customer_id = :customer_id";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("nickname", $nickname);
		$stmt->bindParam("contact_uuid", $contact_uuid);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		
		echo json_encode(array("success"=>true, "email_contact"=>$email_contact));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function confirmEmailThread() {
	$thread_id = passed_var("id", "post");
	$customer_id =  $_SESSION["user_customer_id"];
	
	try {
		//first get the message id from the thread
		$sql = "SELECT mes.message_id 

		FROM cse_thread thr
		INNER JOIN cse_thread_message ctm
		ON thr.thread_uuid = ctm.thread_uuid
		INNER JOIN cse_message mes
		ON ctm.message_id = mes.message_id
		WHERE thr.thread_id = :thread_id
		AND thr.customer_id = :customer_id";
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("thread_id", $thread_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$message = $stmt->fetchObject();
		
		$sql = "UPDATE cse_message mes
				SET mes.`status` = ''
				WHERE 1
				AND mes.message_id = :id
				AND mes.customer_id = :customer_id";
		//die($sql);
		$id = $message->message_id;
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		echo json_encode(array("success"=>true, "form_name"=>"messages"));
		
		trackMessage("assigned", $id);
		
		$message = getMessageInfo($id);
		$email_contact = $message->from;
		$arrContact = explode("|", $email_contact);
		$arrLength = count($arrContact) - 1;
		for($int = $arrLength; $int >= 0; $int--) {
			$thecontact = $arrContact[$int];
			if ($thecontact=="") {
				unset($arrContact[$int]);
			}
		}
		$email_contact = implode("", $arrContact);
		if ($email_contact!="") {
			//is it already in the database
			$sql = "SELECT COUNT(contact_id) contact_count
			FROM cse_contact
			WHERE `email` = '" . $email_contact . "'
			AND user_uuid = '" . $_SESSION["user_id"] . "'
			AND customer_id = " . $_SESSION["user_customer_id"];
			
			$stmt = DB::run($sql);
			$contact = $stmt->fetchObject();
			
			$contact_uuid = uniqid("EM", false);
			//insert emails in contacts for this user
			$sql = "INSERT INTO `cse_contact`
			(`contact_uuid`, `user_uuid`, `email`, `customer_id`)
			SELECT '" . $contact_uuid . "', '" . $_SESSION["user_id"] . "', '" . $email_contact . "', '" . $_SESSION["user_customer_id"] . "'
			FROM dual
			WHERE NOT EXISTS (
					SELECT * 
					FROM `cse_contact` 
					WHERE `email` = '" . $email_contact . "'
					AND `user_uuid` = '" . $_SESSION["user_id"] . "'
					AND customer_id = '" . $_SESSION["user_customer_id"] . "'
				)";
			//die($sql);
			DB::run($sql);
			$contact_id = -1;
			if (!is_object($contact)) {
				$contact_id = DB::lastInsertId();
			}
			
			if ($contact_id > 0) {
				
				$message_contact_uuid = uniqid("MC", false);
				$message_id = $message->id;
				$message_uuid = $message->uuid;
				$attribute = "from";
				if ($message->message_type=="email") {
					$attribute = "to";
				}
				$last_updated_date = date("Y-m-d H:i:s");
				
				$sql = "INSERT INTO cse_message_contact (`message_contact_uuid`, `message_uuid`, `message_id`, `contact_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $message_contact_uuid . "', '" . $message_uuid . "', '" . $message_id . "', '" . $contact_uuid . "', '" . $attribute . "', '" . $last_updated_date . "', :user_uuid, :customer_id)";
				//echo $sql . "\r\n";
				$db = getConnection();
				$stmt = $db->prepare($sql);  
				$stmt->bindParam("user_uuid", $_SESSION["user_id"]);
				$stmt->bindParam("customer_id", $_SESSION["user_customer_id"]);
				$stmt->execute();
			}
		}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function confirmEmailMessage() {
	$id = passed_var("id", "post");
	$case_id = passed_var("case_id", "post");
	$sql = "UPDATE cse_message mes
			SET mes.`status` = ''
			WHERE 1
			AND mes.message_id = :id
			AND mes.customer_id = '" . $_SESSION["user_customer_id"] . "'";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		echo json_encode(array("success"=>"message confirmed", "form_name"=>"messages"));
		
		trackMessage("assigned", $id);
		
		$message = getMessageInfo($id);
		//die(print_r($message));
		
		$email_contact = $message->from;
		$arrContact = explode("|", $email_contact);
		$arrLength = count($arrContact) - 1;
		for($int = $arrLength; $int >= 0; $int--) {
			$thecontact = $arrContact[$int];
			if ($thecontact=="") {
				unset($arrContact[$int]);
			}
		}
		$email_contact = implode("", $arrContact);
		if ($email_contact!="") {
			//is it already in the database
			$sql = "SELECT COUNT(contact_id) contact_count
			FROM cse_contact
			WHERE `email` = '" . $email_contact . "'
			AND user_uuid = '" . $_SESSION["user_id"] . "'
			AND customer_id = " . $_SESSION["user_customer_id"];
			
			$stmt = DB::run($sql);
			$contact = $stmt->fetchObject();
			
			$contact_uuid = uniqid("CT", false);
			//insert emails in contacts for this user
			$sql = "INSERT INTO `cse_contact`
			(`contact_uuid`, `user_uuid`, `email`, `customer_id`)
			SELECT '" . $contact_uuid . "', '" . $_SESSION["user_id"] . "', '" . $email_contact . "', '" . $_SESSION["user_customer_id"] . "'
			FROM dual
			WHERE NOT EXISTS (
					SELECT * 
					FROM `cse_contact` 
					WHERE `email` = '" . $email_contact . "'
					AND `user_uuid` = '" . $_SESSION["user_id"] . "'
					AND customer_id = '" . $_SESSION["user_customer_id"] . "'
				)";
			//die($sql);
			DB::run($sql);
			$contact_id = -1;
			if (!is_object($contact)) {
				$contact_id = DB::lastInsertId();
			}
			
			if ($contact_id > 0) {
				$contact_id = DB::lastInsertId();
				trackContact("insert", $contact_id);
			}
		}
		//attach to case
		if ($case_id!="") {
			$message = getMessageInfo($id);
			
			//attach the from
			$case_message_uuid = uniqid("TD", false);
			$last_updated_date = date("Y-m-d H:i:s");
			
			$sql = "INSERT INTO cse_case_message (`case_message_uuid`, `case_uuid`, `message_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			SELECT '" . $case_message_uuid  ."', `case_uuid`, '" . $message->uuid . "', 'main', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "'
			FROM cse_case 
			WHERE case_id = " . $case_id . "
			AND customer_id = " . $_SESSION['user_customer_id'];
			//echo $sql . "<br />";	
			$stmt = DB::run($sql);
		}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function blockEmailMessage() {
	$id = passed_var("id", "post");
	$customer_id =  $_SESSION["user_customer_id"];
	
	try {
		$message = getMessageInfo($id);
		$email_contact = $message->from;
		$arrContact = explode("|", $email_contact);
		$arrLength = count($arrContact) - 1;
		for($int = $arrLength; $int >= 0; $int--) {
			$thecontact = $arrContact[$int];
			if ($thecontact=="") {
				unset($arrContact[$int]);
			}
		}
		$email_contact = implode("", $arrContact);
		if ($email_contact!="") {
			//get the id for tracking
			$contact = getContactInfoByEmail($email_contact);
			if (is_object($contact)) {
				//insert emails in contacts for this user
				$sql = "UPDATE `cse_contact`
				SET spam_status = 'BLOCKED'
				WHERE `email` = '" . $email_contact . "'
				AND `user_uuid` = '" . $_SESSION["user_id"] . "'
				AND customer_id = '" . $_SESSION["user_customer_id"] . "'";
				//die($sql);
				$stmt = DB::run($sql);
				
				trackContact("blocked", $contact->id);
			} else {
				//actually insert it, and then block it...
				$contact_uuid = uniqid("EM", false);
				//insert emails in contacts for this user
				$sql = "INSERT INTO `cse_contact`
				(`contact_uuid`, `user_uuid`, `email`, `customer_id`, spam_status, deleted)
				VALUES ('" . $contact_uuid . "', '" . $_SESSION["user_id"] . "', :email_contact, '" . $_SESSION["user_customer_id"] . "', 'BLOCKED', 'Y')";
				//die($sql);
				$db = getConnection();
				$stmt = $db->prepare($sql);  
				$stmt->bindParam("email_contact", $email_contact);
				$stmt->execute();
				$contact_id = $db->lastInsertId();
				
				trackContact("insert", $contact_id);
				trackContact("blocked", $contact_id);
			}
			
			//delete the message
			$sql = "UPDATE cse_message mes, cse_message_user cmu
			SET cmu.`deleted` = 'Y', mes.deleted = 'Y',
			cmu.last_updated_date = '" . date("Y-m-d H:i:s") . "',
			cmu.last_update_user = '" . $_SESSION["user_id"] . "'
			WHERE mes.`message_id`= cmu.message_id
			AND cmu.user_uuid = '" . $_SESSION["user_id"] . "'
			AND mes.message_id = :id
			AND mes.customer_id = :customer_id";
			
			$db = getConnection();
			$stmt = $db->prepare($sql);
			$stmt->bindParam("id", $id);
			$stmt->bindParam("customer_id", $customer_id);
			$stmt->execute();
		}
		
		echo json_encode(array("success"=>true, "email_contact"=>$email_contact));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function deleteSpecificThread() {
	$id = passed_var("id", "post");
	$customer_id =  $_SESSION["user_customer_id"];
	
	if (!is_numeric($id)) {
		die();
	}
	/*
	$sql = "UPDATE cse_thread thr
			SET thr.`deleted` = 'Y'
			WHERE 1
			AND thr.thread_id = '" . $id . "'
			AND thr.customer_id = '" . $_SESSION["user_customer_id"] . "'";
	*/
	//die($sql);
	try {
		$sql = "SELECT cmu.message_id,
		ctm.message_uuid, ct.thread_uuid, IFNULL(cmu.message_user_id, 0) message_user_id
		FROM cse_thread ct
		INNER JOIN cse_thread_message ctm
		ON ct.thread_uuid = ctm.thread_uuid
		LEFT OUTER JOIN cse_message_user cmu
		ON ctm.message_id = cmu.message_id
		AND cmu.user_uuid = '" . $_SESSION["user_id"] . "'
		WHERE 1
		AND ct.thread_id = :thread_id
		AND ct.customer_id = :customer_id";
		//die($sql);
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("thread_id", $id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$message_users = $stmt->fetchAll(PDO::FETCH_OBJ);
		$arrSQL = array();
		foreach($message_users as $message_user) {
			if ($message_user->message_user_id == 0) {
				$sql = "INSERT INTO cse_message_user (message_user_uuid, message_uuid, user_uuid, 
				`type`, thread_uuid, read_status, read_date, action, last_updated_date, last_update_user, deleted, customer_id, message_id, user_id)
				SELECT '" . $message_user->thread_uuid . "', '" . $message_user->message_uuid . "', '" . $_SESSION["user_id"] . "', 
				'to', '" . $message_user->thread_uuid . "', 'Y', '" . date("Y-m-d H:i:s") . "', 'reply', '" . date("Y-m-d H:i:s") . "',
				'" . $_SESSION["user_id"] . "', 'Y', '" . $_SESSION["user_customer_id"] . "', '" . $message_user->message_id . "', '" . $_SESSION["user_plain_id"] . "'
				FROM dual
				WHERE NOT EXISTS (
					SELECT * 
					FROM cse_message_user 
					WHERE thread_uuid = '" . $message_user->thread_uuid . "'
					AND message_uuid = '" . $message_user->message_uuid . "'
					AND user_uuid = '" . $_SESSION["user_id"] . "'
				)
				";
				$arrSQL[] = $sql;
			}
		}
		
		if (count($arrSQL)>0) {
			$sql = implode("; ", $arrSQL);
			//die($sql);
			$stmt = DB::run($sql);
		}
		//do not delete the message itself, let the dead relationshiop hide it from the users
		//, mes.deleted = 'Y'
		$sql = "UPDATE cse_message_user cmu, cse_thread_message ctm, cse_thread ct, cse_message mes
			SET cmu.deleted = 'Y', cmu.read_status = 'Y',
			cmu.last_updated_date = '" . date("Y-m-d H:i:s") . "'
			WHERE ct.thread_id = :thread_id
			AND user_uuid = '" . $_SESSION["user_id"] . "'
			AND cmu.customer_id = '" . $_SESSION["user_customer_id"] . "'
			AND cmu.message_id = ctm.message_id
			AND cmu.message_id = mes.message_id
			AND ctm.thread_uuid = ct.thread_uuid";	
		
		//die($sql);
		
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("thread_id", $id);
		$stmt->execute();
			
		echo json_encode(array("success"=>"thread marked as deleted", "form_name"=>"threads"));
		
		//trackThread("delete", $id);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function trackMessage($operation, $message_id, $blnFromEmail = false, $return = "") {
	$sql = "INSERT INTO " . $return . "cse_message_track (`user_uuid`, `user_logon`, `operation`, `message_id`, `message_uuid`, `message_type`, `dateandtime`, `from`, `message_to`, `message_cc`, `message_bcc`, `message`, `subject`, `attachments`, `priority`, `callback_date`, `customer_id`, `status`, `deleted`)
	SELECT '" . $_SESSION['user_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $operation . "', `message_id`, `message_uuid`, `message_type`, `dateandtime`, `from`, `message_to`, `message_cc`, `message_bcc`, `message`, `subject`, `attachments`, `priority`, `callback_date`, `customer_id`, `status`, `deleted`
	FROM " . $return . "cse_message
	WHERE 1
	AND message_id = " . $message_id . "
	AND customer_id = " . $_SESSION['user_customer_id'] . "
	LIMIT 0, 1";
	//die($sql);
	try {
		DB::run($sql);
	$new_id = DB::lastInsertId();
		
		$message = getMessageInfo($message_id, $return);
		//echo $message_id . "<br />" . print_r($message);
		//die();
		//new the case_uuid
		$kase = getKaseInfoByMessage($message_id);
		$case_id = "";
		$case_uuid = "";
		if (is_object($kase)) {
			$case_id = $kase->id;
			$case_uuid = $kase->uuid;
		}
		$activity_category = "Message";
		if ($blnFromEmail) {
			$activity_category = "Email";
		}
		switch($operation){
			case "sent":
				$activity_category = "Email";
				break;
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
		$activity = "Message";
		if ($blnFromEmail) {
			$activity = "Email";
		}
		$activity .= " ['" . $message->subject . "' sent on " . date("m/d/y h:iA", strtotime($message->dateandtime)) . "] was " . $operation . "  by " . $_SESSION['user_name'] . " on " . $message->receiver;
		// on $message->receiver added by mukesh - 25-4-2023
		
		$activity .= "\r\n\r\n";
		$activity .= "To:" . $message->message_to;
		$activity .= "\r\nFrom:" . $message->from; //add by mukesh on 25-4-2023
		if ($message->message_cc!="") {
			$activity .= "\r\nCc:" . $message->message_cc;
		}
		if ($message->message_bcc!="") {
			$activity .= "\r\nBcc:" . $message->message_bcc;
		}
		if ($message->subject!="") {
			$activity .= "\r\nSubject:" . $message->subject;
		}
		$activity .= "\r\n\r\n" . $message->message;
		$attachments = $message->attachments;
		if ($attachments!="") {
			$activity .= "\r\nAttachments:\r\n";
			$arrAttachments = explode("|", $attachments);
			foreach($arrAttachments as $attachment) {
				$document_name = $attachment;
				$document_name = explode("/", $document_name);
				$document_name = $document_name[count($document_name) - 1];
				$root = "D:/uploads/" . $_SESSION['user_customer_id'] . "/";
				
				if ($case_id!="" && $case_id!="-1") {
					$root .= $case_id . "/";
				}
				$activity .= "\r\n<a href='D:/uploads/preview.php?file=" . urlencode($root . $document_name) . "' style='background:yellow;color:black' target='_blank'>" . $document_name . "</a>";
			}
		}
		recordActivity($operation, $activity, $case_uuid, $new_id, $activity_category);
	
		//if ($_SERVER['REMOTE_ADDR']=='47.153.49.248') {
			$url = "https://www.ikase.org/api/messages/setattachments";
			$params = array("customer_id"=> $_SESSION['user_customer_id']);
			/*
			$result = post_curl($url, $params);
			die($result);
			*/
			curl_post_async($url, $params);
		//}
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
