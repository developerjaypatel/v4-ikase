<?php
$app->get('/injury_number/:case_id/:injury_id', authorize('user'),	'getInjuryNumber');

//posts
$app->post('/injury_number/delete', authorize('user'), 'deleteInjuryNumber');
$app->post('/injury_number/add', authorize('user'), 'addInjuryNumber');
$app->post('/injury_number/update', authorize('user'), 'updateInjuryNumber');

function getInjuryNumber($case_id, $injury_id) {
	session_write_close();
    $sql = "SELECT inj.*, inj.injury_number_id id, inj.injury_number_uuid uuid, ci.injury_id
			FROM `cse_injury_number` inj 
			INNER JOIN cse_injury_injury_number ciin
			ON inj.injury_number_uuid = ciin.injury_number_uuid
			INNER JOIN `cse_injury` ci
			ON ciin.injury_uuid = ci.injury_uuid
			INNER JOIN cse_case_injury cci
			ON ci.injury_uuid = cci.injury_uuid
			INNER JOIN cse_case ccase
			ON (cci.case_uuid = ccase.case_uuid
			AND `ccase`.`case_id` = :case_id)";
	$sql .= " WHERE 1
		AND `ci`.`injury_id` = :injury_id
		AND inj.customer_id = " . $_SESSION['user_customer_id'] . "
		AND inj.deleted = 'N'
		ORDER BY inj.injury_number_id DESC
		LIMIT 0,1";
	//die($sql);	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("injury_id", $injury_id);
		$stmt->execute();
		$injury_numbers = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;

        echo json_encode($injury_numbers);     
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function deleteInjuryNumber() {
	$id = passed_var("injury_number_id", "post");
	$sql = "UPDATE cse_injury_number inj
			SET inj.`deleted` = 'Y'
			WHERE `injury_number_id`=:id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("injury_number_id", $id);
		$stmt->execute();
		$stmt = null; $db = null;
		echo json_encode(array("success"=>"injury_number marked as deleted"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function addInjuryNumber() {
	$request = Slim::getInstance()->request();
	$arrFields = array();
	$arrSet = array();
	$table_name = "";
	$table_id = "";
	$injury_id = -1;
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="injury_id") {
			$injury_id = $value;
			continue;
		}
		if ($fieldname=="billing_time") {
			continue;
		}
		if ($fieldname=="billing_time_dropdown") {
			continue;
		}
		if ($fieldname=="billing_time_dropdown_in") {
			continue;
		}
		if ($fieldname=="billing_time_dropdown_bp") {
			continue;
		}
		if ($fieldname=="case_id" || $fieldname=="case_uuid" || $fieldname=="table_id" || $fieldname=="table_uuid" || $fieldname=="injury_number_uuid") {
			continue;
		}
		if ($fieldname=="start_date") {
			if ($value!="") {
				$value = date("Y-m-d", strtotime($value));
			}
		}
		if ($fieldname=="end_date") {
			if ($value!="") {
				$value = date("Y-m-d", strtotime($value));
			}
		}
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "'" . addslashes($value) . "'";
	}
	$arrFields[] = "`customer_id`";
	$arrSet[] = $_SESSION['user_customer_id'];
	$table_uuid = uniqid("KS", false);
	$sql = "INSERT INTO `cse_" . $table_name ."` (`" . $table_name . "_uuid`, " . implode(",", $arrFields) . ") 
			VALUES('" . $table_uuid . "', " . implode(",", $arrSet) . ")";
			//die(print_r($arrFields));
	//die($sql);
	try { 
		
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$new_id = $db->lastInsertId();
		$stmt = null; $db = null;
		
		echo json_encode(array("id"=>$new_id, "uuid"=>$table_uuid)); 
		
		//get the uuid
		$sql = "SELECT injury_uuid uuid FROM cse_injury WHERE injury_id = :injury_id";
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("injury_id", $injury_id);
	
		$stmt->execute();
		$injury = $stmt->fetchObject();
		$stmt->closeCursor(); $stmt = null; $db = null;
		
		$injury_table_uuid = uniqid("KA", false);
		$attribute_1 = "main";
		$last_updated_date = date("Y-m-d H:i:s");
		//now we have to attach the injury_number to the injury 
		$sql = "INSERT INTO cse_injury_" . $table_name . " (`injury_" . $table_name . "_uuid`, `injury_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $injury_table_uuid  ."', '" . $injury->uuid . "', '" . $table_uuid . "', 'main', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
		try {
			$db = getConnection();
			$stmt = $db->prepare($sql);  
		
			$stmt->execute();
		} catch(PDOException $e) {
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}
		
		//track now
		$sql = "track";		
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}

function updateInjuryNumber() {
	$request = Slim::getInstance()->request();
	$arrSet = array();
	$where_clause = "";
	$table_name = "";
	$table_id = "";
	$injury_id = -1;
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="injury_id") {
			$injury_id = $value;
			continue;
		}
		if ($fieldname=="billing_time") {
			continue;
		}
		if ($fieldname=="billing_time_dropdown") {
			continue;
		}
		if ($fieldname=="billing_time_dropdown_in") {
			continue;
		}
		if ($fieldname=="billing_time_dropdown_bp") {
			continue;
		}
		if ($fieldname=="case_id" || $fieldname=="case_uuid" || $fieldname=="checkCT" || $fieldname=="injury_number_uuid") {
			continue;
		}
		if ($fieldname=="start_date" || $fieldname=="end_date") {
			if ($value!="") {
				$value = date("Y-m-d", strtotime($value));
			}
		}
		if ($fieldname=="table_id" || $fieldname=="id") {
			$table_id = $value;
			$where_clause = " = " . $value;
		} else {
			$arrSet[] = "`" . $fieldname . "` = '" . addslashes($value) . "'";
		}
	}
	$where_clause = "`" . $table_name . "_id`" . $where_clause;
	$sql = "
	UPDATE `cse_" . $table_name . "`
	SET " . implode(", ", $arrSet) . "
	WHERE " . $where_clause;
	$sql .= " AND `cse_" . $table_name . "`.customer_id = " . $_SESSION['user_customer_id'];
	//die($sql . "\r\n");
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		$stmt = null; $db = null;
		
		echo json_encode(array("id"=>$table_id)); 
		
		trackInjuryNumber("update", $table_id);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}
function trackInjuryNumber($operation, $injury_number_id) {
	$sql = "INSERT INTO cse_injury_number_track (`user_uuid`, `user_logon`, `operation`, `injury_number_id`, `injury_number_uuid`, `insurance_policy_number`, `alternate_policy_number`, `carrier_claim_number`, `alternate_claim_number`, `carrier_building_indentifier`, `carrier_building_description`, `customer_id`, `deleted`)
	SELECT '" . $_SESSION['user_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $operation . "', `injury_number_id`, `injury_number_uuid`, `insurance_policy_number`, `alternate_policy_number`, `carrier_claim_number`, `alternate_claim_number`, `carrier_building_indentifier`, `carrier_building_description`, `customer_id`, `deleted`
	FROM cse_injury_number
	WHERE 1
	AND injury_number_id = " . $injury_number_id . "
	AND customer_id = " . $_SESSION['user_customer_id'] . "
	LIMIT 0, 1";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$new_id = $db->lastInsertId();

		//new the case_uuid
		$kase = getKaseInfoByInjuryNumber($injury_number_id);
		$activity_category = "Injury";
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
		$doi = date("m/d/Y", strtotime($kase->start_date));
		if ($kase->end_date != "0000-00-00") {
			$doi .= " - " . date("m/d/Y", strtotime($kase->end_date)) . " CT";
		}
		$doi = $kase->adj_number . " - " . $doi;			
		$activity = "Injury Number Information  for [" . $doi . "] was " . $operation . "  by " . $_SESSION['user_name'];
		$stmt = null; $db = null;
		
		$billing_time = 0;
		if (isset($_POST["billing_time"])) {
			$billing_time = passed_var("billing_time", "post");
		}
		recordActivity($operation, $activity, $kase->uuid, $new_id, $activity_category, $billing_time);
		
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
?>