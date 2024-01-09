<?php
$app->get('/rx/:id', authorize('user'),	'getRx');
$app->get('/rx/person/:person_id', authorize('user'),	'getPersonRx');

//posts
$app->post('/rx/delete', authorize('user'), 'deleteRx');
$app->post('/rx/add', authorize('user'), 'addRx');
$app->post('/rx/update', authorize('user'), 'updateRx');

function getRx($id) {
    $sql = "SELECT crx.*, crx.rx_id id, crx.rx_uuid uuid, IFNULL(corp.corporation_id, -1) doctor_id
			FROM `cse_rx` crx 
			INNER JOIN cse_person_rx cpr
			ON crx.rx_uuid = cpr.rx_uuid AND cpr.deleted = 'N'
			LEFT OUTER JOIN `cse_corporation` corp
			ON crx.doctor_uuid = corp.corporation_uuid
			WHERE crx.rx_id=:id
			AND crx.customer_id = " . $_SESSION['user_customer_id'] . "
			AND crx.deleted = 'N'";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$rx = $stmt->fetchObject();
		$db = null;

        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($rx);
        } else {
            echo $_GET['callback'] . '(' . json_encode($rx) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getRxInfo($id) {
    $sql = "SELECT crx.*, crx.rx_id id, crx.rx_uuid uuid, 
			IFNULL(corp.corporation_id, -1) doctor_id,
			cpr.person_uuid
			FROM `cse_rx` crx 
			INNER JOIN cse_person_rx cpr
			ON crx.rx_uuid = cpr.rx_uuid AND cpr.deleted = 'N'
			LEFT OUTER JOIN `cse_corporation` corp
			ON crx.doctor_uuid = corp.corporation_uuid
			WHERE crx.rx_id=:id
			AND crx.customer_id = " . $_SESSION['user_customer_id'] . "";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$rx = $stmt->fetchObject();
		$db = null;

        return $rx;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getPersonRx($person_id) {
    $sql = "SELECT crx.*, crx.rx_id id, crx.rx_uuid uuid, inj.person_id, 
			IFNULL(corp.corporation_id, -1) doctor_id,
			IFNULL(corp.company_name, '') doctor
			FROM `cse_rx` crx 
			INNER JOIN `cse_person_rx` cia
			ON crx.rx_uuid = cia.rx_uuid AND cia.deleted = 'N'
			INNER JOIN cse_person inj
			ON cia.person_uuid = inj.person_uuid
			LEFT OUTER JOIN `cse_corporation` corp
			ON crx.doctor_uuid = corp.corporation_uuid
			WHERE inj.person_id = :person_id
			AND crx.customer_id = " . $_SESSION['user_customer_id'] . "
			AND crx.deleted = 'N'";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("person_id", $person_id);
		$stmt->execute();
		$rxs = $stmt->fetchAll(PDO::FETCH_OBJ);
		$stmt->closeCursor(); $stmt = null; $db = null;

		//die($rx->rx_details);
        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($rxs);
        } else {
            echo $_GET['callback'] . '(' . json_encode($rxs) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function addRx() {
	session_write_close();
	$request = Slim::getInstance()->request();
	$arrFields = array();
	$arrSet = array();
	$table_name = "rx";
	$table_id = "";
	$person_id = "";
	$info = "";
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
		if ($fieldname=="person_id"){
			$person_id = $value;
			$person = getPersonInfo($person_id);
			//die(print_r($person));
			$person_uuid = $person->uuid;
			
			continue;
		}
		if ($fieldname=="doctor_id"){
			$doctor_id = $value;
			$doctor = getCorporationInfo($doctor_id);
			//die(print_r($person));
			$value = $doctor->uuid;
			$fieldname = "doctor_uuid";
		}
		if ($fieldname=="table_id") {
			continue;
		}
		
		//if ($fieldname=="dateandtime") {
		if (strpos($fieldname, "_date")!==false || strpos($fieldname, "date_")!==false) {
			if ($value!="") {
				$value = date("Y-m-d", strtotime($value));
			} else {
				$value = "0000-00-00";
			}
		}
		
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "'" . str_replace("'", "\'", $value) . "'";
	}	
	
	$table_uuid = uniqid("RD", false);
	//insert the parent record first
	$sql = "INSERT INTO `cse_" . $table_name ."` (`" . $table_name . "_uuid`, `customer_id`, " . implode(",", $arrFields) . ") 
		VALUES('" . $table_uuid . "', '" . $_SESSION['user_customer_id'] . "', " . implode(",", $arrSet) . ")";
	echo $sql;  
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$new_id = $db->lastInsertId();
		$stmt = null; $db = null;
		
		$person_rx_uuid = uniqid("IA", false);
		$sql = "INSERT INTO cse_person_rx (`person_rx_uuid`, `person_uuid`, `rx_uuid`, `attribute`, `last_update_user`, `customer_id`)
		VALUES ('" . $person_rx_uuid  ."', '" . $person_uuid . "', '" . $table_uuid . "', 'main', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
		echo $sql;  
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>true, "id"=>$new_id));
		
		trackRx("insert", $new_id);
		//track now
		//trackPerson("insert", $new_id);	
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
	$db = null;
}
function updateRx() {
	$id = passed_var("table_id", "post");
	if ($id==-1) {
		addRx();
		die();
	}
	session_write_close();
	$request = Slim::getInstance()->request();
	$arrFields = array();
	$arrSet = array();
	$table_name = "rx";
	$table_id = "";
	$person_id = "";
	$info = "";
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
		if ($fieldname=="person_id"){
			$person_id = $value;
			$person = getPersonInfo($person_id);
			//echo $person_id;
			//die(print_r($person));
			$person_uuid = $person->uuid;
			
			continue;
		}
		if ($fieldname=="doctor_id"){
			$doctor_id = $value;
			$doctor = getCorporationInfo($doctor_id);
			//die(print_r($person));
			$value = $doctor->uuid;
			$fieldname = "doctor_uuid";
		}
		if ($fieldname=="table_id") {
			$where_clause = " = " . $value;
			continue;
		}
		
		//if ($fieldname=="dateandtime") {
		if (strpos($fieldname, "_date")!==false || strpos($fieldname, "date_")!==false) {
			if ($value!="") {
				$value = date("Y-m-d", strtotime($value));
			} else {
				$value = "0000-00-00";
			}
		}
		
		$arrSet[] = "`" . $fieldname . "` = '" . addslashes($value) . "'";
	}
	$where_clause = "`" . $table_name . "_id`" . $where_clause;
	$sql = "
	UPDATE `cse_" . $table_name . "`
	SET " . implode(", ", $arrSet) . "
	WHERE " . $where_clause;
	$sql .= " AND `cse_" . $table_name . "`.customer_id = " . $_SESSION['user_customer_id'];
	try {		
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>"updated", "id"=>$id));
		
		trackRx("update", $id);
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function deleteRx() {
	$id = passed_var("rx_id", "post");
	if ($id=="") {
		$id = passed_var("id", "post");
	}
	$sql = "UPDATE cse_rx inv
				SET inv.`deleted` = 'Y'
				WHERE `rx_id`=:id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$stmt = null; $db = null;
		
		//trackInjury("delete", $id);
		
		echo json_encode(array("success"=>"rx marked as deleted", "sql"=>$sql));
		
		//if this is the _only_ injury, then we just clear it and undelete it
		//the information will be part of the tracking
		trackRx("delete", $id);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function trackRx($operation, $rx_id) {
	$sql = "INSERT INTO cse_rx_track (`user_uuid`, `user_logon`, `operation`, `rx_id`,
`rx_uuid`, `doctor_uuid`, `start_date`, `end_date`, `medication`, `dosage`, `regimen`, `refills`, `notes`, `deleted`)
	SELECT '" . $_SESSION['user_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $operation . "', `rx_id`,
`rx_uuid`, `doctor_uuid`, `start_date`, `end_date`, `medication`, `dosage`, `regimen`, `refills`, `notes`, `deleted`
	FROM cse_rx
	WHERE 1
	AND rx_id = " . $rx_id . "
	AND customer_id = " . $_SESSION['user_customer_id'] . "
	LIMIT 0, 1";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
	
		$stmt->execute();
		
		$new_id = $db->lastInsertId();
	
		$rx = getRxInfo($rx_id);
		//new the case_uuid
		$kase = getKaseInfoByRx($rx_id);
		$case_uuid = "";
		if (is_object($kase)) {
			$case_uuid = $kase->uuid;
		} else {
			return false;
		}
		$activity_category = "Rx";
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
		$activity = "Rx [<a title='Click to edit rx' class='white_text edit_rx' id='edit_rx_" . $rx_id . "_" . $kase->applicant_id . "' data-toggle='modal' data-target='#myModal4' style='cursor:pointer'>" . $rx->rx_id . " (medication:" . $rx->medication . ")</a>] was " . $operation . "  by " . $_SESSION['user_name'];
		$billing_time = "0";
		recordActivity($operation, $activity, $case_uuid, $new_id, $activity_category, $billing_time);
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
?>