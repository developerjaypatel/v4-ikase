<?php
$app->get('/bodyparts/:case_id/:injury_id', authorize('user'), 'getBodyparts');
$app->post('/bodyparts/add', authorize('user'), 'setBodyparts');
$app->post('/bodyparts/updatestatus', authorize('user'), 'updateStatus');

function getBodyparts($case_id, $injury_id) {
	session_write_close();
	
    $sql = "SELECT DISTINCT bp.*, 
			cib.injury_bodyparts_id, cib.attribute bodyparts_number, cib.`status` `bodyparts_status`,
			ccase.case_id, ccase.case_uuid, bp.bodyparts_id id 
			FROM `cse_bodyparts` bp
			INNER JOIN cse_injury_bodyparts cib
			ON bp.bodyparts_uuid = cib.bodyparts_uuid
			INNER JOIN cse_injury ci
			ON (cib.injury_uuid = ci.injury_uuid
			AND `ci`.`injury_id` = :injury_id)
			INNER JOIN cse_case_injury cci
			ON ci.injury_uuid = cci.injury_uuid
			INNER JOIN cse_case ccase
			ON (cci.case_uuid = ccase.case_uuid
			AND `ccase`.`case_id` = :case_id)
			WHERE 1
			AND cci.customer_id = " . $_SESSION['user_customer_id'] . "
			AND cci.deleted = 'N'
			AND cib.deleted = 'N'
			ORDER BY `code` ASC";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("injury_id", $injury_id);
		$stmt->execute();
		$bodyparts = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		echo json_encode($bodyparts);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getBodypartsInfo($case_id, $injury_id) {
	session_write_close();
	
    $sql = "SELECT DISTINCT bp.*, cib.attribute bodyparts_number, ccase.case_id, ccase.case_uuid 
			FROM `cse_bodyparts` bp
			INNER JOIN cse_injury_bodyparts cib
			ON bp.bodyparts_uuid = cib.bodyparts_uuid
			INNER JOIN cse_injury ci
			ON (cib.injury_uuid = ci.injury_uuid
			AND `ci`.`injury_id` = :injury_id)
			INNER JOIN cse_case_injury cci
			ON ci.injury_uuid = cci.injury_uuid
			INNER JOIN cse_case ccase
			ON (cci.case_uuid = ccase.case_uuid
			AND `ccase`.`case_id` = :case_id)
			WHERE 1
			AND cci.customer_id = " . $_SESSION['user_customer_id'] . "
			AND cci.deleted = 'N'
			AND cib.deleted = 'N'
			ORDER BY `code` ASC";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("injury_id", $injury_id);
		$stmt->execute();
		$bodyparts = $stmt->fetchAll(PDO::FETCH_OBJ);
		return $bodyparts;

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function setBodyparts() {
	session_write_close();
	
	$injury_id = passed_var("injury_id","post");
	$blnScraped = isset($_POST["scraped"]);
	
	//get the uuid
	$sql = "SELECT injury_uuid uuid 
	FROM cse_injury 
	WHERE injury_id = :injury_id";
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("injury_id", $injury_id);

	$stmt->execute();
	$injury = $stmt->fetchObject();
	
	
	$kase = getKaseInfoByInjury($injury_id);
	//echo json_encode($injury); 
	
	if ($blnScraped) {
		//bodyparts
		$sql_bp = "SELECT * 
		FROM `cse_bodyparts` 
		WHERE 1
		ORDER BY code ASC";
		
		$stmt = $db->prepare($sql_bp);
		$stmt = $db->query($sql_bp);
		$bodyparts = $stmt->fetchAll(PDO::FETCH_OBJ);
		$arrBodyParts = array();
		foreach($bodyparts as $bodypart){
			$arrBodyParts[$bodypart->code] = $bodypart->bodyparts_uuid;
		}
	}
	
	$sql = "UPDATE cse_injury_bodyparts 
	SET deleted = 'Y'
	WHERE injury_uuid = '" . $injury->uuid . "'";
	
	$stmt = $db->prepare($sql);
	$stmt->execute();
	
	for($int=1;$int<11;$int++) {
		if (isset($_POST["bodypart"  .$int])) {
			if ($_POST["bodypart"  .$int]!="") {
				$table_uuid = uniqid("KS", false);
				if ($blnScraped) {
					//extract the code and then get the uuid
					$code = passed_var("bodypart"  .$int, "post");
					$code = substr($code, 0, 3);
					$bodyparts_uuid = $arrBodyParts[$code];
				} else {
					$bodyparts_uuid = passed_var("bodypart"  .$int, "post");
				}
				$sql = "INSERT INTO cse_injury_bodyparts (`injury_bodyparts_uuid`, `injury_uuid`, `bodyparts_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
				VALUES ('" . $table_uuid . "', '" . $injury->uuid . "','" . $bodyparts_uuid . "','" . $int . "', '" . date("Y-m-d H:i:s") . "', '" . addslashes($_SESSION['user_name']) . "', '" . $_SESSION['user_customer_id'] . "')";
				//echo $sql . "\r\n";
				try {
					$stmt = $db->prepare($sql);
					$stmt->execute();
					$new_id = $db->lastInsertId();
					//trackBodyparts("insert", $new_id);
				} catch(PDOException $e) {
					$error = array("error"=> array("text"=>$e->getMessage(), "sql"=>$sql));
					echo json_encode($error);
				}
			}
		}
	}
	
	//now update the number
	$sql = "SELECT bp.*, cib.injury_bodyparts_id
			FROM `cse_bodyparts` bp
			INNER JOIN cse_injury_bodyparts cib
			ON bp.bodyparts_uuid = cib.bodyparts_uuid
			INNER JOIN cse_injury cinj
			ON (cib.injury_uuid = cinj.injury_uuid
			AND `cinj`.`injury_uuid` = '" . $injury->uuid . "')
			WHERE 1
			AND cib.customer_id = " . $_SESSION['user_customer_id'] . "
			AND cib.deleted = 'N'
			ORDER BY `code` ASC";
	try {
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$injury_bodyparts = $stmt->fetchAll(PDO::FETCH_OBJ);
		
		$body_counter = 0;
		foreach ($injury_bodyparts as $injury_bodypart) {
			$body_counter++;
			$sql_update = "UPDATE cse_injury_bodyparts 
			SET attribute = '" . $body_counter . "'
			WHERE injury_bodyparts_id = '" . $injury_bodypart->injury_bodyparts_id . "'";
			$stmt = $db->prepare($sql_update);
			$stmt->execute();
		}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	$db = null;
	echo json_encode(array("success"=>true)); 
	
	//activity
	recordActivity("update", "Body Parts updated by " . $_SESSION['user_name'], $kase->uuid, 0, "Injury");
}
function updateStatus() {
	session_write_close();
	
	$status = passed_var("status", "post");
	$injury_bodyparts_id = passed_var("id", "post");
	
	if (!is_numeric($injury_bodyparts_id)) {
		die();
	}
	$sql = "UPDATE cse_injury_bodyparts 
			SET `status` = :status
			WHERE injury_bodyparts_id = :injury_bodyparts_id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("status", $status);
		$stmt->bindParam("injury_bodyparts_id", $injury_bodyparts_id);
		$stmt->execute();
		$stmt = null; $db = null;
		
		echo json_encode(array("success"=>true, "status"=>$status, "id"=>$injury_bodyparts_id)); 
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
/*
function trackBodyparts($operation, $bodyparts_id) {
	$sql = "INSERT INTO cse_bodyparts_track (`user_uuid`, `user_logon`, `operation`, `bodyparts_id`, `bodyparts_uuid`, `code`, `description`)
	SELECT '" . $_SESSION['user_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $operation . "', `bodyparts_id`, `bodyparts_uuid`, `code`, `description`
	FROM cse_bodyparts
	WHERE 1
	AND bodyparts_id = " . $bodyparts_id . "
	LIMIT 0, 1";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
	
		$stmt->execute();
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
*/
?>