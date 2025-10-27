<?php
$app->group('', function (\Slim\Routing\RouteCollectorProxy $app) {
	$app->get('/notes/{id}', 'getKaseNote');
	$app->get('/notes/kases/{case_id}', 'getKaseNotes');
	$app->get('/notes/dash/{case_id}', 'getKaseNotesDash');
	$app->get('/notes/reflag/{case_id}', 'getRedFlag');
	$app->get('/notes/{type}/{case_id}', 'getNotesByType');
	$app->get('/injurynotes/{type}/{case_id}', 'getInjuryNotesByType');

	$app->post('/notes/delete', 'deleteNote');
	$app->post('/notes/add', 'addNote');
	$app->post('/notes/update', 'updateNote');
	$app->post('/notes/detach', 'detachNote');
})->add(\Api\Middleware\Authorize::class);

function getRedFlag($case_id) {
	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	
	$sql = "SELECT cn.*
	FROM cse_notes cn
	INNER JOIN cse_case_notes ccn
	ON cn.notes_uuid = ccn.notes_uuid AND ccn.deleted = 'N'
	INNER JOIN cse_case ccase
	ON ccn.case_uuid = ccase.case_uuid
	WHERE 1 
	AND `type` = 'REDFLAG'
	AND ccase.case_id = :case_id
	AND ccase.customer_id = :customer_id";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$notes = $stmt->fetchAll(PDO::FETCH_OBJ);

        // Include support for JSONP requests
         echo json_encode($notes);        

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getKaseNotes($case_id) {
	session_write_close();
    
	$kase = getKaseInfo($case_id);
	
	if (!is_object($kase)) {
		return false;
	}
	$case_uuids = $kase->uuid;
	$related_kases = getRelatedKases($case_id);
	
	if (count($related_kases) > 0) {
		$arrRelatedList = array();
		foreach($related_kases as $related_kase) {
			$arrRelatedList[] = $related_kase->case_uuid;
		}
		$case_uuids = "'" . implode("','", $arrRelatedList) . "'";
	}
	/*
	$sql = "SELECT * FROM (SELECT `cse_notes`.`notes_id`, `cse_notes`.`notes_uuid` ,  
	`cse_notes`.`note`, `cse_notes`.`title`, `subject`, `cse_notes`.`entered_by` , 
	`cse_notes`.`attachments` ,  
	`cse_notes`.`status`, `cse_notes`.`customer_id` , 
	`cse_notes`.`dateandtime` ,  `cse_notes`.`callback_date` ,  `cse_notes`.`verified` , 
	`cse_case`.`case_id`, `cse_case`.`case_name`, `cse_case`.`case_type`,
	`cse_case_notes`.`case_uuid` , `cse_notes`.`type`, `cse_case_notes`.`attribute`,
	`cse_notes`.`notes_id` `id`,  `cse_notes`.`notes_uuid` `uuid`
			FROM  `cse_notes` 
			INNER JOIN  `cse_case_notes` ON  (`cse_notes`.`notes_uuid` =  `cse_case_notes`.`notes_uuid` 
				AND `cse_case_notes`.case_uuid ='" . $kase->uuid . "')
			INNER JOIN `cse_case` ON  (`cse_case_notes`.`case_uuid` = `cse_case`.`case_uuid`
			)
			WHERE `cse_notes`.`deleted` = 'N'
			#AND cse_notes.type != 'audit'
			AND `cse_notes`.`subject` != 'EAMS Form Filled'
			AND `cse_notes`.customer_id = " . $_SESSION['user_customer_id'] . ") notes
			ORDER BY IF(`status` = 'IMPORTANT', 0, 1) ASC, dateandtime DESC ";
	
	//if ($_SERVER['REMOTE_ADDR']=='71.119.40.148b') {
		*/
	//notes for all related cases
	$sql = " SELECT DISTINCT * FROM (
	 SELECT injury_info.injury_dates,  `main_case_id`, `main_case_number`,  
		`cse_notes`.`notes_id`, `cse_notes`.`notes_uuid` , `cse_notes`.`note`, `cse_notes`.`title`, `subject`, 
		`cse_notes`.`entered_by` , `cse_notes`.`attachments` , `cse_notes`.`status`, `cse_notes`.`customer_id` , 
		`cse_notes`.`dateandtime` , `cse_notes`.`callback_date` , `cse_notes`.`verified` , `cse_case`.`case_id`, 
		`cse_case`.`case_name`, `cse_case`.`case_type`, `cse_case_notes`.`case_uuid` , `cse_notes`.`type`, 
		`cse_case_notes`.`attribute`, IFNULL(inj.injury_id, '') doi_id, IFNULL(inj.start_date, '') doi_start, IFNULL(inj.end_date, '') doi_end,
		`cse_notes`.`notes_id` `id`, `cse_notes`.`notes_uuid` `uuid` 
		
	FROM `cse_notes` 
	
	LEFT OUTER JOIN `cse_injury_notes` `cinotes`
	ON `cse_notes`.`notes_uuid` = `cinotes`.`notes_uuid` AND cinotes.deleted = 'N'
	LEFT OUTER JOIN `cse_injury` inj
	ON cinotes.injury_uuid = inj.injury_uuid
	
	INNER JOIN `cse_case_notes` ON (
				`cse_notes`.`notes_uuid` = `cse_case_notes`.`notes_uuid` AND `cse_case_notes`.case_uuid IN (" . $case_uuids . ")
			)
	INNER JOIN `cse_case` ON (`cse_case_notes`.`case_uuid` = `cse_case`.`case_uuid`) 
	INNER JOIN ( 
	
		SELECT DISTINCT ccase.case_id `main_case_id`, ccase.case_number `main_case_number`, IFNULL(injury_dates, '') injury_dates
		FROM cse_case ccase

		INNER JOIN (
			SELECT DISTINCT ccase.case_id, ccase.case_uuid
			FROM  cse_case_injury cci
			INNER JOIN cse_case ccase
			ON cci.case_uuid = ccase.case_uuid
				
			INNER JOIN (
				SELECT injury_uuid 
				FROM cse_case_injury cinj 
				INNER JOIN cse_case ccase
				ON cinj.case_uuid = ccase.case_uuid
				where case_id = " . $case_id . "
			) injury_list
			ON cci.injury_uuid = injury_list.injury_uuid
		) related_cases
		ON ccase.case_uuid = related_cases.case_uuid

		LEFT OUTER JOIN (
			SELECT case_list.case_id main_case_id, case_list.case_uuid, GROUP_CONCAT(CONCAT(injury_id, '|', start_date, '|', end_date, '|', case_number)) injury_dates
			FROM (
				SELECT case_id, case_uuid, case_number
				FROM (
					SELECT DISTINCT ccase.case_id, ccase.case_uuid, ccase.case_number
					FROM  cse_case_injury cci
					INNER JOIN cse_case ccase
					ON cci.case_uuid = ccase.case_uuid
					AND ccase.customer_id = " . $_SESSION['user_customer_id'] . " AND ccase.deleted = 'N'	
					INNER JOIN (
						SELECT inj.*
						FROM cse_case_injury cinj 
						INNER JOIN cse_case ccase
						ON cinj.case_uuid = ccase.case_uuid
						INNER JOIN cse_injury inj
						ON cinj.injury_uuid = inj.injury_uuid
						where case_id = " . $case_id . "
					) injury_list
					ON cci.injury_uuid = injury_list.injury_uuid
				) all_cases
				WHERE case_id != " . $case_id . "
			) case_list
			INNER JOIN cse_case_injury cci
			ON cci.case_uuid = case_list.case_uuid AND cci.attribute != 'related'
			INNER JOIN cse_injury inj
			ON cci.injury_uuid = inj.injury_uuid
			GROUP BY case_uuid
		) case_injuries
		ON ccase.case_id = case_injuries.main_case_id 
	) injury_info
	ON cse_case.case_id = injury_info.main_case_id
	WHERE 1
	AND cse_notes.notes_uuid != ''
	AND cse_notes.deleted = 'N'
	#AND cse_notes.type != 'audit'
	AND `cse_notes`.note NOT LIKE 'Scanned Document%'
	AND `cse_notes`.note NOT LIKE 'Folder Accessed%'
	AND `cse_notes`.note NOT LIKE 'BULK SCAN%'
	AND `cse_notes`.note NOT LIKE '%(Posted To Activity Log)'
	AND cse_notes.customer_id = '" . $_SESSION["user_customer_id"] . "'
	) all_notes
	ORDER BY IF(`status` = 'IMPORTANT', 0, 1) ASC, dateandtime DESC
	
	";
	if ($_SESSION["user_customer_id"]==1033) {
		$sql .= "
		LIMIT 0, 50";
	}
	
	if ($_SERVER['REMOTE_ADDR']=='47.146.250.221') {
		//die($sql);
	}
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->execute();
		$notes = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		
		//die(print_r($notes));
		foreach($notes as &$note) {
			if (strlen($note->note) > 499) {
				$short_note = truncateWords($note->note, 50);
				$short_note = restoreTags($short_note);
				$note->short_note = $short_note;
				$note->short_note .= "&nbsp;<a id='readmore_" . $note->notes_id . "' style='cursor:pointer;background:white;color:black;padding:2px' class='read_more white_text' title='Click to read more'>read more</a>";
			} else {
				$note->short_note = $note->note;
			}
			
			if ($note->subject=='Intake Notes') {
				$note->subject = "Phone Intake";
				$note->type = "phone intake";
			}
		}
		
        // Include support for JSONP requests
         echo json_encode($notes);        

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function getKaseNotesDash($case_id) {
	session_write_close();
    
	$kase = getKaseInfo($case_id);
	$case_uuids = $kase->uuid;
	$related_kases = getRelatedKases($case_id);
	
	if (count($related_kases) > 0) {
		$arrRelatedList = array();
		foreach($related_kases as $related_kase) {
			$arrRelatedList[] = $related_kase->case_uuid;
		}
		$case_uuids = "'" . implode("','", $arrRelatedList) . "'";
	}
	$sql = "SELECT * FROM (SELECT `cse_notes`.`notes_id`, `cse_notes`.`notes_uuid` ,  
	`cse_notes`.`note`, `cse_notes`.`title`, `subject`, `cse_notes`.`entered_by` , 
	`cse_notes`.`attachments` ,  
	`cse_notes`.`status`, `cse_notes`.`customer_id` , 
	`cse_notes`.`dateandtime` ,  `cse_notes`.`callback_date` ,  `cse_notes`.`verified` , 
	`cse_case`.`case_id`, `cse_case`.`case_name`, `cse_case`.`case_type`,
	`cse_case_notes`.`case_uuid` , `cse_notes`.`type`, `cse_case_notes`.`attribute`,
	`cse_notes`.`notes_id` `id`,  `cse_notes`.`notes_uuid` `uuid`
			FROM  `cse_notes` 
			INNER JOIN  `cse_case_notes` ON  (`cse_notes`.`notes_uuid` =  `cse_case_notes`.`notes_uuid` 
				AND `cse_case_notes`.case_uuid ='" . $kase->uuid . "')
			INNER JOIN `cse_case` ON  (`cse_case_notes`.`case_uuid` = `cse_case`.`case_uuid`
			AND `cse_case`.`case_id` = " . $case_id . ")
			WHERE `cse_notes`.`deleted` = 'N'
			#AND cse_notes.type != 'audit'
			AND `cse_notes`.`subject` != 'EAMS Form Filled'
			AND `cse_notes`.customer_id = " . $_SESSION['user_customer_id'] . ") notes
			ORDER BY IF(`status` = 'IMPORTANT', 0, 1) ASC, dateandtime DESC ";
	
	//if ($_SERVER['REMOTE_ADDR']=='71.119.40.148b') {
		
	//notes for all related cases
	$sql = " SELECT DISTINCT * FROM (
	 SELECT injury_info.injury_dates,  `main_case_id`, `main_case_number`,  
		`cse_notes`.`notes_id`, `cse_notes`.`notes_uuid` , `cse_notes`.`note`, `cse_notes`.`title`, `subject`, 
		`cse_notes`.`entered_by` , `cse_notes`.`attachments` , `cse_notes`.`status`, `cse_notes`.`customer_id` , 
		`cse_notes`.`dateandtime` , `cse_notes`.`callback_date` , `cse_notes`.`verified` , `cse_case`.`case_id`, 
		`cse_case`.`case_name`, `cse_case`.`case_type`, `cse_case_notes`.`case_uuid` , `cse_notes`.`type`, 
		`cse_case_notes`.`attribute`, `cse_notes`.`notes_id` `id`, `cse_notes`.`notes_uuid` `uuid` 
FROM `cse_notes` 
INNER JOIN `cse_case_notes` ON (
			`cse_notes`.`notes_uuid` = `cse_case_notes`.`notes_uuid` AND `cse_case_notes`.case_uuid IN (" . $case_uuids . ")
		)
INNER JOIN `cse_case` ON (`cse_case_notes`.`case_uuid` = `cse_case`.`case_uuid`) 
INNER JOIN ( 
	
		SELECT DISTINCT ccase.case_id `main_case_id`, ccase.case_number `main_case_number`, IFNULL(injury_dates, '') injury_dates
		FROM cse_case ccase

		INNER JOIN (
			SELECT DISTINCT ccase.case_id, ccase.case_uuid
			FROM  cse_case_injury cci
			INNER JOIN cse_case ccase
			ON cci.case_uuid = ccase.case_uuid
				
			INNER JOIN (
				SELECT injury_uuid 
				FROM cse_case_injury cinj 
				INNER JOIN cse_case ccase
				ON cinj.case_uuid = ccase.case_uuid
				where case_id = :case_id
			) injury_list
			ON cci.injury_uuid = injury_list.injury_uuid
		) related_cases
		ON ccase.case_uuid = related_cases.case_uuid

		LEFT OUTER JOIN (
			SELECT case_list.case_id main_case_id, case_list.case_uuid, GROUP_CONCAT(CONCAT(injury_id, '|', start_date, '|', end_date, '|', case_number)) injury_dates
			FROM (
				SELECT case_id, case_uuid, case_number
				FROM (
					SELECT DISTINCT ccase.case_id, ccase.case_uuid, ccase.case_number
					FROM  cse_case_injury cci
					INNER JOIN cse_case ccase
					ON cci.case_uuid = ccase.case_uuid
					AND ccase.customer_id = " . $_SESSION['user_customer_id'] . " AND ccase.deleted = 'N'	
					INNER JOIN (
						SELECT inj.*
						FROM cse_case_injury cinj 
						INNER JOIN cse_case ccase
						ON cinj.case_uuid = ccase.case_uuid
						INNER JOIN cse_injury inj
						ON cinj.injury_uuid = inj.injury_uuid
						where case_id = " . $case_id . "
					) injury_list
					ON cci.injury_uuid = injury_list.injury_uuid
				) all_cases
				WHERE case_id != " . $case_id . "
			) case_list
			INNER JOIN cse_case_injury cci
			ON cci.case_uuid = case_list.case_uuid AND cci.attribute != 'related'
			INNER JOIN cse_injury inj
			ON cci.injury_uuid = inj.injury_uuid
			GROUP BY case_uuid
		) case_injuries
		ON ccase.case_id = case_injuries.main_case_id 
	) injury_info
	ON cse_case.case_id = injury_info.main_case_id
	WHERE 1
	AND cse_notes.deleted = 'N'
	#AND cse_notes.type != 'audit'
	AND `cse_notes`.note NOT LIKE 'Scanned Document%'
	AND `cse_notes`.note NOT LIKE 'Folder Accessed%'
	AND `cse_notes`.note NOT LIKE 'BULK SCAN%'
	AND `cse_notes`.note NOT LIKE '%(Posted To Activity Log)'
	AND cse_notes.customer_id = '" . $_SESSION["user_customer_id"] . "'
	) all_notes
	ORDER BY IF(`status` = 'IMPORTANT', 0, 1) ASC, dateandtime DESC
	";
	//LIMIT 0,300
		
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->execute();
		$notes = $stmt->fetchAll(PDO::FETCH_OBJ);

        // Include support for JSONP requests
         echo json_encode($notes);        

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}


function getKaseNote($notes_id) {
	session_write_close();
	if ($notes_id > 0) {
    $sql = "SELECT `notes_id`, `cse_notes`.`notes_uuid`, `cse_notes`.`title`, `subject`, `entered_by`, `cse_notes`.`attachments`,  
			`cse_notes`.`status`,  `cse_notes`.`dateandtime`,  `cse_notes`.`callback_date`, `verified`, `cse_notes`.`deleted` , `cse_case`.`case_id`, 
			`cse_case`.`case_name`, `cse_case`.`case_type`, `cse_notes`.`type`, 
			`cse_notes`.`customer_id`, IFNULL(inj.injury_id, '') doi_id, IFNULL(inj.start_date, '') doi_start, IFNULL(inj.end_date, '') doi_end,
			`cse_notes`.`notes_id` `id`,  `cse_notes`.`notes_uuid` `uuid`, `note`, IFNULL(ct.task_id, -1) task_id
			FROM `cse_notes` 
						
			LEFT OUTER JOIN `cse_injury_notes` `cinotes`
			ON `cse_notes`.`notes_uuid` = `cinotes`.`notes_uuid` AND cinotes.deleted = 'N'
			LEFT OUTER JOIN `cse_injury` inj
			ON cinotes.injury_uuid = inj.injury_uuid
			
			INNER JOIN  `cse_case_notes` ON  `cse_notes`.`notes_uuid` =  `cse_case_notes`.`notes_uuid` 
			INNER JOIN `cse_case` ON  (`cse_case_notes`.`case_uuid` = `cse_case`.`case_uuid`)
			LEFT OUTER JOIN `cse_notes_task` cnt
			ON `cse_notes`.`notes_uuid` =  `cnt`.`notes_uuid`
			LEFT OUTER JOIN `cse_task` ct
			ON cnt.task_uuid = ct.task_uuid 
			WHERE `cse_notes`.`deleted` = 'N'
			AND `cse_notes`.`notes_id` = :notes_id
			AND `cse_notes`.note NOT LIKE 'Scanned Document%'
			AND `cse_notes`.note NOT LIKE 'Folder Accessed%'
			AND `cse_notes`.note NOT LIKE 'BULK SCAN%'
			AND `cse_notes`.note NOT LIKE '%(Posted To Activity Log)'
			AND `cse_notes`.customer_id = " . $_SESSION['user_customer_id'];
	} else {
		$sql = "SELECT '-1' `notes_id`, '' `notes_uuid`, '' `note`, '' `title`, '' subject, '' `entered_by`, `attachments`, 'STANDARD' `status`, '" . date("Y-m-d H:i:s") . "' `dateandtime`, '' `verified`, 'N' `deleted` , '' `case_id`, '' `type`, -1 task_id";
	}
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($notes_id!="") {
			$stmt->bindParam("notes_id", $notes_id);
		}
		$stmt->execute();
		$note = $stmt->fetchObject();

        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($note);
        } else {
            echo $_GET['callback'] . '(' . json_encode($note) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getNoteInfo($notes_id) {
	session_write_close();
	$sql = "SELECT `notes_id`, `cse_notes`.`notes_uuid`, `note`, `cse_notes`.`title`, `subject`, `entered_by`, `cse_notes`.`attachments`, `status`, `dateandtime`, `callback_date`, `verified`, `cse_notes`.`deleted` , `cse_case`.`case_id`, `type`, `cse_notes`.`customer_id`, `cse_notes`.`notes_id` `id`,  `cse_notes`.`notes_uuid` `uuid`
			FROM `cse_notes` 
			INNER JOIN  `cse_case_notes` ON  `cse_notes`.`notes_uuid` =  `cse_case_notes`.`notes_uuid` 
			INNER JOIN `cse_case` ON  (`cse_case_notes`.`case_uuid` = `cse_case`.`case_uuid`)
			WHERE 1
			AND `cse_notes`.`notes_id` = :notes_id
			AND `cse_notes`.customer_id = " . $_SESSION['user_customer_id'];
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($notes_id!="") {
			$stmt->bindParam("notes_id", $notes_id);
		}
		$stmt->execute();
		$note = $stmt->fetchObject();
		return $note;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getNotesByType($type, $case_id) {
	session_write_close();
	if ($type!="quick") {
		$sql = "SELECT DISTINCT `cse_notes`.`notes_id`, `cse_case_notes`.`deleted`, `cse_notes`.`notes_uuid`, `note`, 
		`cse_notes`.`title`, `subject`, 
		`entered_by`, `attachments`, `status`, `dateandtime`, `callback_date`, `verified`, `cse_notes`.`deleted`, `type`, `cse_notes`.`customer_id`, `cse_notes`.`notes_id` `id`,  `cse_notes`.`notes_uuid` `uuid`, `cse_case`.case_id
		FROM `cse_notes` 
		INNER JOIN  `cse_case_notes` ON  `cse_notes`.`notes_uuid` =  `cse_case_notes`.`notes_uuid` 
		INNER JOIN `cse_case` ON  (`cse_case_notes`.`case_uuid` = `cse_case`.`case_uuid`)
		WHERE `cse_notes`.`deleted` = 'N'
		AND `cse_case_notes`.`deleted` = 'N'
		AND `cse_case`.`case_id` = :case_id
		AND `cse_case_notes`.`attribute` = :type
		AND `cse_notes`.customer_id = " . $_SESSION['user_customer_id'] . "
		ORDER BY `cse_notes`.notes_id DESC";
	} else {
		/*
		$sql = "SELECT DISTINCT `cse_notes`.`notes_id`, `cse_case_notes`.`deleted`, `cse_notes`.`notes_uuid`, `note`, 
		`cse_notes`.`title`, `subject`, 
		`entered_by`, `attachments`, `status`, `dateandtime`, `callback_date`, `verified`, `cse_notes`.`deleted`, `type`, `cse_notes`.`customer_id`, `cse_notes`.`notes_id` `id`,  `cse_notes`.`notes_uuid` `uuid`, `cse_case`.case_id
		FROM `cse_notes` 
		INNER JOIN  `cse_case_notes` ON  `cse_notes`.`notes_uuid` =  `cse_case_notes`.`notes_uuid` 
		INNER JOIN `cse_case` ON  (`cse_case_notes`.`case_uuid` = `cse_case`.`case_uuid`)
		WHERE `cse_notes`.`deleted` = 'N'
		AND `cse_case_notes`.`deleted` = 'N'
		AND `cse_case`.`case_id` = :case_id
		AND (`cse_case_notes`.`attribute` = :type OR LOWER(`cse_notes`.`type`) = 'quick')
		AND `cse_notes`.customer_id = " . $_SESSION['user_customer_id'] . "
		ORDER BY `cse_notes`.notes_id DESC";
		
		$sql = "SELECT *
		FROM (
			SELECT DISTINCT `cse_notes`.`notes_id`, `cse_notes`.`notes_uuid`, `note`, 
			`cse_notes`.`title`, `subject`, 
			`entered_by`, `attachments`, `status`, `dateandtime`, `callback_date`, `verified`, `cse_notes`.`deleted`, `type`, `cse_notes`.`customer_id`, `cse_notes`.`notes_id` `id`,  `cse_notes`.`notes_uuid` `uuid`, `cse_case`.case_id
			FROM `cse_case_notes`
		   
			INNER JOIN `cse_case` 
			ON  `cse_case_notes`.`case_uuid` = cse_case.case_uuid
			
			INNER JOIN `cse_notes` 
			ON `cse_case_notes`.notes_uuid = cse_notes.notes_uuid
			WHERE 1
			AND `cse_case_notes`.`deleted` = 'N'
			AND `cse_case`.`case_id` = :case_id
			AND (`cse_case_notes`.`attribute` = :type OR LOWER(`cse_notes`.`type`) = 'quick')
		) quicks
		WHERE quicks.deleted = 'N'
		AND quicks.customer_id = " . $_SESSION['user_customer_id'] . "
		ORDER BY notes_id DESC";
		*/
		$sql = "SELECT *
		FROM (
			SELECT DISTINCT `cse_case_notes`.`deleted` cdeleted, `cse_notes`.`notes_id`, `cse_notes`.`notes_uuid`, `note`, 
			`cse_notes`.`title`, `subject`, 
			`entered_by`, `attachments`, `status`, `dateandtime`, `callback_date`, `verified`, `cse_notes`.`deleted`, `type`, `cse_notes`.`customer_id`, `cse_notes`.`notes_id` `id`,  `cse_notes`.`notes_uuid` `uuid`, `cse_case`.case_id
			FROM `cse_notes` 
            INNER JOIN `cse_case_notes`
			ON cse_notes.notes_uuid = `cse_case_notes`.notes_uuid
            
			INNER JOIN `cse_case` 
			ON  `cse_case_notes`.`case_uuid` = cse_case.case_uuid

			WHERE 1
			
			AND `cse_case`.`case_id` = :case_id
			AND (`cse_notes`.`type` = :type OR `cse_notes`.`type` = 'Quick Note')
			AND `cse_notes`.note NOT LIKE 'Scanned Document%'
			AND `cse_notes`.note NOT LIKE 'Folder Accessed%'
			AND `cse_notes`.note NOT LIKE 'BULK SCAN%'
			AND `cse_notes`.note NOT LIKE '%(Posted To Activity Log)'
		) quicks
		WHERE quicks.deleted = 'N'
		AND quicks.customer_id = " . $_SESSION['user_customer_id'] . "
        AND quicks.cdeleted = 'N'
		ORDER BY notes_id DESC";
		//AND (`cse_case_notes`.`attribute` = 'quick' OR LOWER(`cse_notes`.`type`) = 'quick')
	}
	
	if ($_SERVER['REMOTE_ADDR']=='47.153.59.9') {
		//die($sql);
	}
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("type", $type);
		$stmt->execute();
		$notes = $stmt->fetchAll(PDO::FETCH_OBJ);

        // Include support for JSONP requests
         echo json_encode($notes);        
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getInjuryNotesByType($type, $injury_id) {
	session_write_close();
	$sql = "SELECT `cse_notes`.`notes_id`, `cse_injury_notes`.`deleted`, `cse_notes`.`notes_uuid`, `note`, 
	`cse_notes`.`title`, `subject`, 
	`entered_by`, `attachments`, `status`, `dateandtime`, `callback_date`, `verified`, `cse_notes`.`deleted`, `cse_notes`.`type`, `cse_notes`.`customer_id`, `cse_notes`.`notes_id` `id`,  `cse_notes`.`notes_uuid` `uuid`, `cse_injury`.injury_id, `cse_case`.case_id
	FROM `cse_notes` 
	INNER JOIN  `cse_injury_notes` ON  `cse_notes`.`notes_uuid` =  `cse_injury_notes`.`notes_uuid` 
	INNER JOIN `cse_injury` ON `cse_injury_notes`.`injury_uuid` = `cse_injury`.`injury_uuid`
	INNER JOIN `cse_case_injury` ON `cse_injury`.`injury_uuid` = `cse_case_injury`.`injury_uuid`
	INNER JOIN `cse_case` ON `cse_case_injury`.`case_uuid` = `cse_case`.`case_uuid`
	WHERE `cse_notes`.`deleted` = 'N'
	AND `cse_injury_notes`.`deleted` = 'N'
	AND `cse_injury`.`injury_id` = :injury_id
	AND `cse_notes`.`type` = :type
	AND `cse_notes`.customer_id = " . $_SESSION['user_customer_id'] . "
	ORDER BY `cse_notes`.notes_id DESC
	";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("injury_id", $injury_id);
		$stmt->bindParam("type", $type);
		$stmt->execute();
		$notes = $stmt->fetchAll(PDO::FETCH_OBJ);

        // Include support for JSONP requests
         echo json_encode($notes);        
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function deleteNote() {
	$id = passed_var("id", "post");
	$customer_id = $_SESSION["user_customer_id"];
	
	session_write_close();
	
	$sql = "UPDATE `cse_notes` 
			SET `deleted` = 'Y'
			WHERE `notes_id`=:id
			AND `cse_notes`.customer_id = :customer_id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		echo json_encode(array("success"=>"note marked as deleted"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	trackNote("delete", $id);
}
function detachNote() {
	$id = passed_var("id", "post");
	$file = passed_var("file", "post");
	$customer_id = $_SESSION["user_customer_id"];
	$file = str_replace(" ", "%20", $file);
	
	//echo $file . "\r\n";
	session_write_close();
	
	$note = getNoteInfo($id);
	$attachments = $note->attachments;
	$arrAttachments = explode("|", $attachments);
	//print_r($arrAttachments);
	$arrNewAttach = array();
	foreach($arrAttachments as $attach) {
		$arrPath = explode("/", $attach);
		if (in_array("uploads", $arrPath)) {
			$attach = $arrPath[count($arrPath) - 1];
		}
		//die(print_r($arrKase));
		if ($attach == $file) {
			continue;
		}
		$arrNewAttach[] = $attach;
	}
	//die(print_r($arrNewAttach));
	$new_attachments = implode("|", $arrNewAttach);
	//die("new:" . $new_attachments);
	$sql = "UPDATE `cse_notes` 
			SET `attachments` = :attachments
			WHERE `notes_id`=:id
			AND `cse_notes`.customer_id = :customer_id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->bindParam("attachments", $new_attachments);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		echo json_encode(array("success"=>"note attachment detached"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	trackNote("update", $id);
}
function addNote() {
	session_write_close();
	
	$arrFields = array();
	$arrSet = array();
	$table_name = "";
	$partie_id = "";
	$injury_uuid = "";
	$case_id = "";
	$case_uuid = "";
	$type = "";
	$subject = "";
	$note = "";
	$send_document_id = "";
	$attachments = "";
	$attachment_case = "";
	$blnAttachments = true;
	$arrTo = array();
	$arrToID = array();
	$arrCc = array();
	$arrCcID = array();
	$arrBcc = array();
	$arrBccID = array();				
			
	//default attribute
	$table_attribute = "main";
	$db = getConnection();
	
	//die(print_r($_POST));
	
	foreach($_POST as $fieldname=>$value) {
		if ($fieldname!="noteInput") {
			$value = passed_var($fieldname, "post");
		} else {
			//special case
			//remove script
			$value = @processHTML($_POST["noteInput"]);
			$note = $value;
			
			$arrTo = array_merge($arrTo, $arrCc, $arrBcc);
			$arrRecipients = array();
			foreach($arrTo as $to) {
				if ($to=="") {
					continue;
				}
				$arrRecipients[] = strtoupper($to);
			}
			if (count($arrRecipients) > 0) {		
				$value .= "<div style='font-size:0.7em; margin-top:-5px'>(sent to " . implode(" and ", $arrRecipients) . ")</div>";
			}
		}
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="subject") {
			$subject = $value;
			//continue;
		}
		if ($fieldname=="billing_time" || $fieldname=="task_id" || $fieldname=="assignee") {
			continue;
		}
		if ($fieldname=="kinvoice_id" || $fieldname=="kinvoice_document_id" || $fieldname=="kinvoice_path") {
			continue;
		}
		if ($fieldname=="billing_time_dropdown") {
			continue;
		}
		if ($fieldname=="signature") {
			$signature = $value;
			continue;
		}
		if ($fieldname=="table_attribute") {
			$table_attribute = $value;
			continue;
		}
		if ($fieldname=="case_uuid") {
			$case_uuid = $value;
			continue;
		}
		if ($fieldname=="case_id") {
			$case_id = $value;
			continue;
		}
		if ($fieldname=="case_id") {
			$case_id = $value;
			continue;
		}
		if ($fieldname=="partie_id") {
			$partie_id = $value;
			continue;
		}
		if ($fieldname=="injury_id") {
			if ($value!=null && $value!="null") {
					
				$injury_id = $value;
				//get the uuid
				$injury = getInjuryInfo($injury_id);
				$injury_uuid = $injury->uuid;
			}
			continue;
		}
		if ($fieldname=="type") {
			$type = $value;
			//special case
			if ($type=="quick") {
				$table_attribute = $value;
			}
		}
		if ($fieldname=="message_to") {
			explodeRecipient($value, $arrTo, $arrToID, $db);
			continue;
		}
		if ($fieldname=="message_cc") {
			explodeRecipient($value, $arrCc, $arrCcID, $db);
			continue;
		}
		if ($fieldname=="message_bcc") {
			explodeRecipient($value, $arrBcc, $arrBccID, $db);
			continue;
		}
		// || $fieldname=="message_to" || $fieldname=="message_cc" || $fieldname=="message_bcc"
		if ($fieldname=="case_file" || $fieldname=="table_id" || $fieldname=="priority" || $fieldname=="source_message_id" || $fieldname=="from" || $fieldname=="reaction" || $fieldname=="task_assignee") {
			continue;
		}
		if (strpos($fieldname, "_uuid") > -1) {
			continue;
		}
		if ($fieldname=="send_document_id") {
			$send_document_id = $value;
			if ($send_document_id!="") {
				$send_document = getDocumentInfo($send_document_id);
				$attachments = $send_document->document_filename;
				
				$arrFields[] = "`attachments`";
				$arrSet[] = "'" . $attachments . "'";
				$blnAttachments = false;
			}
			continue;
		}
		if ($fieldname=="attach_document_id") {
			$arrSendDocumentsID = explode("|", $value);
			foreach($arrSendDocumentsID as $attach_document_id) {
				if ($attach_document_id!="") {
					$attach_document = getDocumentInfo($attach_document_id);
					//store the document object in an array
					$arrAttachedCaseDocuments[] = $attach_document;
					
					if ($attachment_case=="") {
						$attachment_case = "D:/uploads/" . $_SESSION['user_customer_id'] . "/" . $attach_document->document_filename;
					} else {
						$attachment_case .= "|D:/uploads/" . $_SESSION['user_customer_id'] . "/" . $attach_document->document_filename;
					}
				}
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

		$arrFields[] = "`" . $fieldname . "`";
		if ($fieldname=="dateandtime" || $fieldname=="start_date" || $fieldname=="end_date" || $fieldname=="callback_date") {
			if ($value!="") {
				$value = date("Y-m-d H:i:s", strtotime($value));
			} else {
				$value = "0000-00-00 00:00:00";
			}
		}
		$arrSet[] = "'" . addslashes($value) . "'";
	}

	if ($attachments!="" || $attachment_case!="") {
		if (!in_array("`attachments`", $arrFields)) { 
			$arrFields[] = "`attachments`";
		
			if ($attachment_case!="") {
				if ($attachments!="") {
					$arrSet[] = "'" . $attachments . "|" . $attachment_case . "'";
				} else {
					$arrSet[] = "'" . $attachment_case . "'";
				}
			} else {
				$arrSet[] = "'" . $attachments . "'";
			}
		}
	}
	
	$table_uuid = uniqid("KS", false);
	//combine 
	$sql = "INSERT INTO `cse_" . $table_name ."` (`customer_id`, `entered_by`, `" . $table_name . "_uuid`, " . implode(",", $arrFields) . ") 
			VALUES('" . $_SESSION['user_customer_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $table_uuid . "', " . implode(",", $arrSet) . ")";
	//die($sql);
	try { 
		DB::run($sql);
	$notes_id = DB::lastInsertId();
		
		
		//let's get send document details if any
		if ($send_document_id != "") {
			$last_updated_date = date("Y-m-d H:i:s");
			$message_document_uuid = uniqid("TD", false);
			$sql = "INSERT INTO cse_message_document (`message_document_uuid`, `message_uuid`, `document_uuid`, `attribute_1`, `attribute_2`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $message_document_uuid  ."', '" . $table_uuid . "', '" . $send_document->document_uuid . "', 'attach', '', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			
			$stmt = DB::run($sql);
		}
		
		if ($case_uuid=="" && $case_id!="") {
			$kase = getKaseInfo($case_id);
			$case_uuid = $kase->uuid;
		}
		
		//attach attachments
		if ($attachments!="") {
			$arrAttachments = explode("|", $attachments);
			foreach ($arrAttachments as $aindex=>$attachment) {
				$document_name = $attachment;
				$document_name = explode("/", $document_name);
				$document_name = $document_name[count($document_name) - 1];
				//keep the file name
				$document_filename = $document_name;
				$description_html = "note attachment";
				
				//but we might change the name itself
				if ($aindex==0) {
					if ($subject!="") {
						//remove all weird characters
						$document_name = noSpecialFilename($subject);
					}
					if ($note!="") {
						$description_html = $note;
						//however, ironically, no html for description...
						$description_html = str_replace("</p>", "</p>\r\n", $description_html);
						$description_html = strip_tags($description_html, "");
					}
				}
				$document_date = date("Y-m-d H:i:s");
				$document_extension = explode(".", $document_name);
				$document_extension = $document_extension[count($document_extension) - 1];
				$customer_id = $_SESSION["user_customer_id"];
				$description = "note attachment";
				
				$type = "note_attachment";
				$verified = "Y";
				$thumbnail_folder = $case_id . "/medium";
				//attachment is a document
				$document_uuid = uniqid("KS");
				$sql = "INSERT INTO cse_document (document_uuid, parent_document_uuid, document_name, document_date, document_filename, document_extension, thumbnail_folder, description, description_html, type, verified, customer_id) 
			VALUES (:document_uuid, :parent_document_uuid, :document_name, :document_date, :document_filename, :document_extension, :thumbnail_folder, :description, :description_html, :type, :verified, :customer_id)";
				
				$stmt = $db->prepare($sql);  
				$stmt->bindParam("document_uuid", $document_uuid);
				$stmt->bindParam("parent_document_uuid", $document_uuid);
				$stmt->bindParam("document_name", $document_name);
				$stmt->bindParam("document_date", $document_date);
				$stmt->bindParam("document_filename", $document_filename);
				$stmt->bindParam("document_extension", $document_extension);
				$stmt->bindParam("description", $description);
				$stmt->bindParam("thumbnail_folder", $thumbnail_folder);
				$stmt->bindParam("description_html", $description_html);
				$stmt->bindParam("type", $type);
				$stmt->bindParam("verified", $verified);
				$stmt->bindParam("customer_id", $customer_id);
				$stmt->execute();
				$new_id = $db->lastInsertId();

				//die(print_r($newEmployee));
				trackDocument("insert", $new_id);
				
				$message_document_uuid = uniqid("TD", false);
				$last_updated_date = date("Y-m-d H:i:s");
				$sql = "INSERT INTO cse_notes_document (`notes_document_uuid`, `notes_uuid`, `document_uuid`, `attribute_1`, `attribute_2`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $message_document_uuid  ."', '" . $table_uuid . "', '" . $document_uuid . "', 'attach', '', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
				
				$stmt = DB::run($sql);
				
				if ($case_uuid!="") {
					$sql = "INSERT INTO cse_case_document (`case_document_uuid`, `case_uuid`, `document_uuid`, `attribute_1`, `attribute_2`, `last_updated_date`, `last_update_user`, `customer_id`)
					VALUES ('" . $message_document_uuid  ."', '" . $case_uuid . "', '" . $document_uuid . "', 'attach', '', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
					
					$stmt = DB::run($sql);
				}
			}
		}
		
		//FOR SOME REASON THIS IS HERE TWICE, WILL REMOVE
		//attachments
		//attach attachments
		/*
		if ($attachments!="") {
			$arrAttachments = explode("|", $attachments);
			foreach ($arrAttachments as $attachment) {
				$document_name = $attachment;
				
				$document_name = explode("/", $document_name);
				$document_name = $document_name[count($document_name) - 1];
				$document_date = date("Y-m-d H:i:s");
				$document_extension = explode(".", $document_name);
				$document_extension = $document_extension[count($document_extension) - 1];
				$customer_id = $_SESSION["user_customer_id"];
				$description = "note attachment";
				$description_html = "note attachment";
				$type = "note attachment";
				$verified = "Y";
				
				//attachment is a document
				$document_uuid = uniqid("KS");
				$sql = "INSERT INTO cse_document (document_uuid, parent_document_uuid, document_name, document_date, document_filename, document_extension, description, description_html, type, verified, customer_id) 
			VALUES (:document_uuid, :parent_document_uuid, :document_name, :document_date, :document_filename, :document_extension, :description, :description_html, :type, :verified, :customer_id)";
				
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
				$new_id = $db->lastInsertId();

				//die(print_r($newEmployee));
				trackDocument("insert", $new_id);
				
				$message_document_uuid = uniqid("TD", false);
				$last_updated_date = date("Y-m-d H:i:s");
				$sql = "INSERT INTO cse_notes_document (`notes_document_uuid`, `notes_uuid`, `document_uuid`, `attribute_1`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $message_document_uuid  ."', '" . $table_uuid . "', '" . $document_uuid . "', 'attach', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
				
				$stmt = DB::run($sql);
			}
		}
		*/
		$last_updated_date = date("Y-m-d H:i:s");
		
		//attach to party
		if ($partie_id!="" && $partie_id!="button") {
			
			if (is_numeric((int)$partie_id)) {
				//die($table_attribute . "  => partiesss:" . $partie_id);
				$partie_id = (int)$partie_id;
				if ($table_attribute!="applicant") {
					$party = getCorporationInfo($partie_id);
					$entity = "corporation";					
					$party_uuid = "";
					if (is_object($party)) {
						$party_uuid = $party->uuid;
						$table_attribute = $party->type;
					}
				} else {
					$party = getPersonInfo($partie_id);
					//die(print_r($party));
					$entity = "person";
					$party_uuid = "";
					if (is_object($party)) {
						$party_uuid = $party->uuid;
					}
				}
				
				$entity_uuid = uniqid("CR", false);
				
				//now we have to attach the note to the case 
				$sql = "INSERT INTO cse_" . $entity . "_" . $table_name . " (`" . $entity . "_" . $table_name . "_uuid`, `" . $entity . "_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
					VALUES ('" . $entity_uuid  ."', '" . $party_uuid . "', '" . $table_uuid . "', '" . $table_attribute . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
				//die($sql);	
				$stmt = DB::run($sql);
			}
			
			//die($table_attribute . "  NOT partie:" . $partie_id);
		}
		
		
		if ($case_id!="") {
			$case_table_uuid = uniqid("KA", false);
			//attribute
			if ($table_attribute=="") {
				//default
				$table_attribute = "main";
			}
			
			//now we have to attach the note to the case 
			$sql = "INSERT INTO cse_case_" . $table_name . " (`case_" . $table_name . "_uuid`, `case_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
			VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', '" . $table_attribute . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
			
			//die($sql);
			try {
				$stmt = DB::run($sql);
			} catch(PDOException $e) {
				echo '{"error case notes insert":{"text":'. $e->getMessage() .'}}'; 
			}
			
			if ($type=="lien" || $type=="settlement" || $injury_uuid!="") {
				$last_updated_date = date("Y-m-d H:i:s");
				$injury_table_uuid = uniqid("KA", false);
				//attribute
				$table_attribute = $type;
				
				//now we have to attach the note to the case 
				$sql = "INSERT INTO cse_injury_" . $table_name . " (`injury_" . $table_name . "_uuid`, `injury_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $injury_table_uuid  ."', '" . $injury_uuid . "', '" . $table_uuid . "', '" . $table_attribute . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
				
				try {
					$stmt = DB::run($sql);
				} catch(PDOException $e) {
					echo '{"error injury notes insert":{"text":'. $e->getMessage() .'}}'; 
				}
			}
		}
		$activity_id = trackNote("insert", $notes_id);
		
		echo json_encode(array("success"=>true, "id"=>$notes_id, "uuid"=>$table_uuid, "activity_id"=>$activity_id)); 
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}

function updateNote() {
	$arrSet = array();
	$where_clause = "";
	$table_name = "";
	$table_id = "";
	$attachment_case = "";
	$arrAttachments = array();
	foreach($_POST as $fieldname=>$value) {
		if ($fieldname!="noteInput") {
			$value = passed_var($fieldname, "post");
		} else {
			//special case
			//remove script
			$value = @processHTML($_POST["noteInput"]);
		}
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="table_attribute") {
			$table_attribute = $value;
			continue;
		}
		if ($fieldname=="case_id" || $fieldname=="task_id" || $fieldname=="case_file") {
			continue;
		}
		if ($fieldname=="type") {
			$type = $value;
		}
		if ($fieldname=="billing_time" || $fieldname=="assignee") {
			continue;
		}
		if ($fieldname=="billing_time_dropdown") {
			continue;
		}
		if ($fieldname=="partie_id") {
			continue;
		}
		if ($fieldname=="injury_id") {
			continue;
		}
		if ($fieldname=="task_assignee") {
			continue;
		}
		//no uuids  || $fieldname=="case_uuid"
		if (strpos($fieldname, "_uuid") > -1) {
			continue;
		}
		if ($fieldname=="send_document_id") {
			$send_document_id = $value;
			if ($send_document_id!="") {
				$arrDocs = explode("|", $send_document_id);
				foreach($arrDocs as $send_document_id) {
					$send_document = getDocumentInfo($send_document_id);
					$arrAttachments[] = $send_document->document_filename;
				}
			}
			continue;
		}
		if ($fieldname=="attach_document_id") {
			$arrSendDocumentsID = explode("|", $value);
			foreach($arrSendDocumentsID as $attach_document_id) {
				if ($attach_document_id!="") {
					$attach_document = getDocumentInfo($attach_document_id);
					//store the document object in an array
					$arrAttachedCaseDocuments[] = $attach_document;
					
					if ($attachment_case=="") {
						$attachment_case = "D:/uploads/" . $_SESSION['user_customer_id'] . "/" . $attach_document->document_filename;
					} else {
						$attachment_case .= "|D:/uploads/" . $_SESSION['user_customer_id'] . "/" . $attach_document->document_filename;
					}
				}
			}
			continue;
		}
		if ($fieldname=="attachments") {
			if ($value!="") {
				$attachments = $value;
				$arrAttachments[] = $value;
			}
			continue;
		}
		if ($fieldname=="dateandtime" || $fieldname=="start_date" || $fieldname=="end_date" || $fieldname=="callback_date") {
			if ($value!="") {
				$value = date("Y-m-d H:i:s", strtotime($value));
			} else {
				$value = "0000-00-00 00:00:00";
			}
		}

		if ($fieldname=="table_id" || $fieldname=="id") {
			$table_id = $value;
			$where_clause = " = " . $value;
		} else {
			$arrSet[] = "`" . $fieldname . "` = '" . addslashes($value) . "'";
		}
	}
	if (count($arrAttachments) > 0 || $attachment_case !="") {
		if (count($arrAttachments) > 0) {
			$the_attaches = implode("|", $arrAttachments);
		}
		$the_attaches .= $attachment_case;
		
		$arrSet[] = "`attachments` = '" . $the_attaches . "'";
		//we're going to need a table_uuid for attaching
		$note = getNoteInfo($table_id);
		$table_uuid = $note->uuid;
	}
	$where_clause = "`" . $table_name . "_id`" . $where_clause;
	$sql = "
	UPDATE `cse_" . $table_name . "`
	SET " . implode(", ", $arrSet) . "
	WHERE " . $where_clause;
	$sql .= " AND `cse_" . $table_name . "`.customer_id = " . $_SESSION['user_customer_id'];
	//die( $sql . "\r\n");
	try {
		$stmt = DB::run($sql);
		
		echo json_encode(array("success"=>true, "id"=>$table_id)); 
		
		foreach ($arrAttachments as $attachment) {
			$document_name = $attachment;
			//first check if this document is _already_ attached
			$sql = "SELECT COUNT(doc.document_id) thecount
			FROM `cse_document` doc
			INNER JOIN `cse_notes_document` cnd
			ON doc.document_uuid = cnd.document_uuid
			WHERE doc.document_name = '" . $document_name . "'";
			$stmt = DB::run($sql);
			$document_count = $stmt->fetchObject();
			
			if ($document_count->thecount==0) {
				$document_name = explode("/", $document_name);
				$document_name = $document_name[count($document_name) - 1];
				$document_date = date("Y-m-d H:i:s");
				$document_extension = explode(".", $document_name);
				$document_extension = $document_extension[count($document_extension) - 1];
				$customer_id = $_SESSION["user_customer_id"];
				$description = "note attachment";
				$description_html = "note attachment";
				$type = "note_attachment";
				$verified = "Y";
				
				//attachment is a document
				$document_uuid = uniqid("KS");
				$sql = "INSERT INTO cse_document (document_uuid, parent_document_uuid, document_name, document_date, document_filename, document_extension, description, description_html, type, verified, customer_id) 
			VALUES (:document_uuid, :parent_document_uuid, :document_name, :document_date, :document_filename, :document_extension, :description, :description_html, :type, :verified, :customer_id)";
				
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
				$new_id = $db->lastInsertId();

				//die(print_r($newEmployee));
				trackDocument("insert", $new_id);
				
				$message_document_uuid = uniqid("TD", false);
				$last_updated_date = date("Y-m-d H:i:s");
				$sql = "INSERT INTO cse_notes_document (`notes_document_uuid`, `notes_uuid`, `document_uuid`, `attribute_1`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $message_document_uuid  ."', '" . $table_uuid . "', '" . $document_uuid . "', 'attach', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
				
				$stmt = DB::run($sql);
			}
		}		
		//track now
		trackNote("update", $table_id);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function trackNote($operation, $notes_id) {
	$sql = "INSERT INTO cse_notes_track (`user_uuid`, `user_logon`, `operation`, `notes_id`, `notes_uuid`, `type`, `note`, `title`, `subject`, `entered_by`, `status`,`callback_date`, `verified`, `deleted`, `customer_id`)
	SELECT '" . $_SESSION['user_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $operation . "', `notes_id`, `notes_uuid`, `type`, `note`, `title`, `subject`, `entered_by`, `status`, `callback_date`, `verified`, `deleted`, `customer_id`
	FROM cse_notes
	WHERE 1
	AND notes_id = " . $notes_id . "
	AND customer_id = " . $_SESSION['user_customer_id'] . "
	LIMIT 0, 1";
	//die($sql);
	try {
		DB::run($sql);
	$new_id = DB::lastInsertId();
		
		//new the case_uuid
		$kase = getKaseInfoByNote($notes_id);
		$case_uuid = "";
		$case_id = "";
		if (is_object($kase)) {
			$case_uuid = $kase->uuid;
			$case_id = $kase->id;
		}
		$note = getNoteInfo($notes_id);
		if (!is_object($note)) {
			die();
		}
		if ($note->subject != "EAMS Form Filled" && $note->subject != "Letter Added") {
			$activity_category = "Notes";
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
			$activity = "Note was " . $operation . "  by " . $_SESSION['user_name'];
			$activity .= "\r\n\r\n";
			$activity .= $note->note;
			$attachments = $note->attachments;
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
			$billing_time = 0;
			$activity_id = recordActivity($operation, $activity, $case_uuid, $new_id, $activity_category, $billing_time);
			return $activity_id;
			//recordActivity($operation, $activity, $case_uuid, $new_id, $activity_category);
		}
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
