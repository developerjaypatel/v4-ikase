<?php
use Slim\Routing\RouteCollectorProxy;

$app->group('', function (RouteCollectorProxy $app) {
	$app->group('/homemedical', function (RouteCollectorProxy $app) {
		$app->get('/{id}', 'getHomeMedical');
		$app->post('/delete', 'deleteHomeMedical');
		$app->post('/add', 'addHomeMedical');
		$app->post('/update', 'updateHomeMedical');
	});
	$app->get('/homemedicals/{case_id}', 'getHomeMedicals');
})->add(Api\Middleware\Authorize::class);

function getHomeMedicals($case_id) {
    $sql = "SELECT DISTINCT chm.*, chm.homemedical_id id, chm.homemedical_uuid uuid,
			IFNULL(corp.corporation_id, -1) corporation_id, IFNULL(corp.company_name, '') company_name, 
					IFNULL(corp.full_address, '') full_address, IFNULL(corp.street, '') street, IFNULL(corp.suite, '') suite, 
					IFNULL(corp.city, '') city, IFNULL(corp.state, '') state, IFNULL(corp.zip, '') zip, IFNULL(corp.phone, '') phone
			FROM  `cse_homemedical` chm
			INNER JOIN `cse_case_homemedical` cchm 
			ON chm.homemedical_uuid = cchm.homemedical_uuid
			INNER JOIN `cse_case` cc
			ON cchm.case_uuid = cc.case_uuid
			LEFT OUTER JOIN `cse_corporation_homemedical` cch
			ON chm.homemedical_uuid = cch.homemedical_uuid AND cch.deleted = 'N'
			LEFT OUTER JOIN `cse_corporation` corp
			ON cch.corporation_uuid = corp.corporation_uuid
			WHERE `chm`.`deleted` = 'N'
			AND `cchm`.`deleted` = 'N'
			AND cc.case_id = :case_id
			AND `chm`.customer_id = " . $_SESSION['user_customer_id'] . "
			ORDER BY  `chm`.homemedical_id DESC ";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->execute();
		$homemedicals = $stmt->fetchAll(PDO::FETCH_OBJ);

        // Include support for JSONP requests
         echo json_encode($homemedicals);        

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function getHomeMedical($homemedical_id) {
	$sql = "SELECT `cho`.*, `cho`.`homemedical_id` `id`,  `cho`.`homemedical_uuid` `uuid`, 
			IFNULL(corp.corporation_id, -1) corporation_id, IFNULL(corp.company_name, '') company_name, 
			IFNULL(corp.full_address, '') full_address, IFNULL(corp.street, '') street, IFNULL(corp.suite, '') suite, 
			IFNULL(corp.city, '') city, IFNULL(corp.state, '') state, IFNULL(corp.zip, '') zip, IFNULL(corp.phone, '') phone
			FROM `cse_homemedical` cho 
			LEFT OUTER JOIN `cse_corporation_homemedical` cch
			ON cho.homemedical_uuid = cch.homemedical_uuid AND cch.deleted = 'N'
			LEFT OUTER JOIN `cse_corporation` corp
			ON cch.corporation_uuid = corp.corporation_uuid
			WHERE `cho`.`deleted` = 'N'
			AND `cho`.`homemedical_id` = :homemedical_id
			AND `cho`.customer_id = " . $_SESSION['user_customer_id'];
			
	//die($sql);
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("homemedical_id", $homemedical_id);
		$stmt->execute();
		$note = $stmt->fetchObject();

        // Include support for JSONP requests
        echo json_encode($note);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getHomeMedicalInfo($homemedical_id) {
	$sql = "SELECT `cse_homemedical`.*, `cse_homemedical`.`homemedical_id` `id`,  `cse_homemedical`.`homemedical_uuid` `uuid`
			FROM `cse_homemedical` 
			WHERE `cse_homemedical`.`deleted` = 'N'
			AND `cse_homemedical`.`homemedical_id` = :homemedical_id
			AND `cse_homemedical`.customer_id = " . $_SESSION['user_customer_id'];
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		if ($homemedical_id!="") {
			$stmt->bindParam("homemedical_id", $homemedical_id);
		}
		$stmt->execute();
		$homemedical = $stmt->fetchObject();
		return $homemedical;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function deleteHomeMedical() {
	$id = passed_var("id", "post");
	$sql = "UPDATE `cse_homemedical` 
			SET `deleted` = 'Y'
			WHERE `homemedical_id`=:id
			AND `cse_homemedical`.customer_id = " . $_SESSION['user_customer_id'];
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		echo json_encode(array("success"=>"note marked as deleted"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	//trackDocument("delete", $id);
}
function addHomeMedical() {
	$arrFields = array();
	$arrSet = array();
	$table_name = "";
	$case_uuid = "";
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = str_replace("_", "", $value);
			continue;
		}
		if ($fieldname=="case_id") {
			$case_id = $value;			
			$kase = getKaseInfo($case_id);
			$case_uuid = $kase->uuid;
		
			continue;
		}
		if ($fieldname=="table_id" || $fieldname=="corporation_id" || $fieldname=="full_address" || $fieldname=="phone") {
			continue;
		}
		
		$arrFields[] = "`" . $fieldname . "`";
		
		if (strpos($fieldname, "_date") > -1) {
			if ($value!="") {
				$value = date("Y-m-d", strtotime($value));
			} else {
				$value = "0000-00-00";
			}
		}
		$arrSet[] = "'" . addslashes($value) . "'";
	}
	
	$table_uuid = uniqid("KS", false);
	$sql = "INSERT INTO `cse_" . $table_name ."` (`customer_id`, `" . $table_name . "_uuid`, " . implode(",", $arrFields) . ") 
			VALUES('" . $_SESSION['user_customer_id'] . "', '" . $table_uuid . "', " . implode(",", $arrSet) . ")";
	//die($sql);
	try { 
		
		DB::run($sql);
	$new_id = DB::lastInsertId();
		
		echo json_encode(array("id"=>$new_id, "uuid"=>$table_uuid)); 
		
		//delete any previous relationship that may exist
		$sql = "UPDATE `cse_case_" . $table_name . "`
		SET `deleted` = 'Y' 
		WHERE `case_uuid` = '" . $case_uuid . "'";
		$stmt = DB::run($sql);
		
		//add a new relationship
		$case_table_uuid = uniqid("KA", false);
		//attribute
		$table_attribute = "main";
		
		$last_updated_date = date("Y-m-d H:i:s");
		//now we have to attach the note to the case 
		$sql = "INSERT INTO cse_case_" . $table_name . " (`case_" . $table_name . "_uuid`, `case_uuid`, `" . $table_name . "_uuid`, `attribute`, `last_updated_date`, `last_update_user`, `customer_id`)
		VALUES ('" . $case_table_uuid  ."', '" . $case_uuid . "', '" . $table_uuid . "', '" . $table_attribute . "', '" . $last_updated_date . "', '" . $_SESSION['user_id'] . "', '" . $_SESSION['user_customer_id'] . "')";
				
		$stmt = DB::run($sql);
		
		trackHomeMedical("insert", $new_id);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}

function updateHomeMedical() {
	$arrSet = array();
	$where_clause = "";
	$table_name = "";
	$table_id = "";
	$homemedical_uuid = "";
	foreach($_POST as $fieldname=>$value) {
		if ($fieldname!="homemedicalInput") {
			$value = passed_var($fieldname, "post");
		} else {
			//special case
			//remove script
			$value = @processHTML($_POST["homemedicalInput"]);
		}
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = str_replace("_", "", $value);
			continue;
		}
		if ($fieldname=="table_attribute") {
			$table_attribute = $value;
			continue;
		}
		if ($fieldname=="case_id" || $fieldname=="focusme") {
			continue;
		}
		if ($fieldname=="corporation_id" || $fieldname=="full_address" || $fieldname=="phone") {
			continue;
		}
		//no uuids  || $fieldname=="case_uuid"
		if (strpos($fieldname, "_uuid") > -1) {
			continue;
		}
		if (strpos($fieldname, "_date") > -1) {
			if ($value!="") {
				$value = date("Y-m-d", strtotime($value));
			} else {
				$value = "0000-00-00";
			}
		}
		if ($fieldname=="table_id" || $fieldname=="id") {
			$table_id = $value;
			//get uuid, we need for json output
			$homemedical = getHomeMedicalInfo($value);
			$homemedical_uuid = $homemedical->uuid;
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
	//die( $sql . "\r\n");
	try {
		$stmt = DB::run($sql);
		
		echo json_encode(array("success"=>$table_id, "uuid"=>$homemedical_uuid)); 
		
		//track now
		trackHomeMedical("update", $table_id);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function trackHomeMedical($operation, $homemedical_id) {
	$sql = "INSERT INTO cse_homemedical_track (`user_uuid`, `user_logon`, `operation`, `homemedical_id`, `homemedical_uuid`, `recommended_by`, `provider_name`, `prescription`, `homemedical_report`, `prescription_date`, `report_date`, `filling_fee_paid_date`, `retainer_date`, `lien_filled_date`, `reviewed_date`, `customer_id`, `deleted`)
	SELECT '" . $_SESSION['user_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $operation . "', `homemedical_id`, `homemedical_uuid`, `recommended_by`, `provider_name`, `prescription`, `homemedical_report`, `prescription_date`, `report_date`, `filling_fee_paid_date`, `retainer_date`, `lien_filled_date`, `reviewed_date`, `customer_id`, `deleted`
	FROM cse_homemedical
	WHERE 1
	AND homemedical_id = " . $homemedical_id . "
	AND customer_id = " . $_SESSION['user_customer_id'] . "
	LIMIT 0, 1";
	//die($sql);
	try {
		$stmt = DB::run($sql);
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
