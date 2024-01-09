<?php
$app->group('/forms', function (\Slim\Routing\RouteCollectorProxy $app) {
	$app->get('', 'getForms');
	$app->get('/{id}', 'getForm');
	$app->post('/delete', 'deleteEAMSForm');
	$app->post('/add', 'addEAMSForm');
	$app->post('/update', 'updateEAMSForm');
})->add(Api\Middleware\Authorize::class);

function getForms() {
    $sql = "SELECT *, `eams_form_id` `id` 
	FROM `cse_eams_forms` 
	WHERE 1
	AND (customer_id = " . $_SESSION['user_customer_id'] . " OR customer_id = 0)
	AND deleted = 'N'
	ORDER BY category, display_name";
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$eamses = $stmt->fetchAll(PDO::FETCH_OBJ);
		//die(print_r($kases));
        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($eamses);
        } else {
            echo $_GET['callback'] . '(' . json_encode($eamses) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function getForm($id) {
    $sql = "SELECT eform.*, eform.eams_form_id id
			FROM `cse_eams_forms` eform 
			WHERE eform.eams_form_id=:id
			AND (eform.customer_id = " . $_SESSION['user_customer_id'] . " OR eform.customer_id = 0)
			AND eform.deleted = 'N'";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$eams_form = $stmt->fetchObject();

        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($eams_form);
        } else {
            echo $_GET['callback'] . '(' . json_encode($eams_form) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function getFormInfo($id) {
	//return a row if id is valid
	$sql = "SELECT eform.*, eform.eams_form_id id
		FROM `cse_eams_forms` eform 
		WHERE eform.eams_form_id=:id
		AND (eform.customer_id = " . $_SESSION['user_customer_id'] . " OR eform.customer_id = 0)";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$eams_form = $stmt->fetchObject();
		//die($sql);

        return $eams_form;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function deleteEAMSForm() {
	$id = passed_var("id", "post");
	$sql = "UPDATE cse_eams_forms eform
			SET eform.`deleted` = 'Y'
			WHERE `eams_form_id`=:id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		
		trackEAMSForm("delete", $id);
		echo json_encode(array("success"=>"eams_form marked as deleted"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function addEAMSForm() {
	$arrFields = array();
	$arrSet = array();
	$name = "";
	$customer_id = 1;
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="customer_id") {
			$customer_id = $value;
			continue;
		}
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="name") {
			$name = $value;
		}
		if ($fieldname=="billing_time") {
			continue;
		}
		if ($fieldname=="billing_time_dropdown") {
			continue;
		}
		if ($fieldname=="table_id" || $fieldname=="eams_form_id" || $fieldname=="send_document_id" || $fieldname=="attachments" || $fieldname=="partie_count") {
			continue;
		}
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "'" . addslashes($value) . "'";
	}
		
	//now we start saving
	$db = getConnection();
	$table_uuid = uniqid("KS", false);
	
	$sql = "INSERT INTO `cse_eams_forms` (`customer_id`, " . implode(", ", $arrFields) . ") 
			VALUES('" . $_SESSION['user_customer_id'] . "'," . implode(",
			", $arrSet) . ")";
			
	//die($sql);
	try { 
		DB::run($sql);
	$new_id = DB::lastInsertId();
		
		echo json_encode(array("id"=>$new_id, "uuid"=>$table_uuid)); 

		//track now
		trackEAMSForm("insert", $new_id);
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
	
	//special case for forms
	if ($customer_id==2) {
		//add to other customers
		$schema_sql = "SELECT `schema_name`
		FROM `information_schema`.schemata 
		WHERE schema_name LIKE 'ikase%'
		AND schema_name != 'ikase'";
		
		try {
			$schemas = DB::select($schema_sql);
			
			//die(print_r($schemas));
			
			foreach($schemas as $schema) {
				//skip
				// || $schema->schema_name=="ikase_gonzalez"
				if ($schema->schema_name=="ikase_glauber" || $schema->schema_name=="ikase_glauber2") {
					continue;
				}
				
				$del_sql = "UPDATE `" . $schema->schema_name . "`.`cse_eams_forms`
				SET deleted = 'Y'
				WHERE `name` = :name
				AND customer_id = 0";
				$db = getConnection();
				$stmt = $db->prepare($del_sql);
				$stmt->bindParam("name", $name);
				$stmt->execute();		
				
				$new_sql = str_replace("INTO `cse_eams_forms`", "INTO `" . $schema->schema_name . "`.`cse_eams_forms`", $sql);
				$new_sql = str_replace("VALUES('" . $_SESSION['user_customer_id'] . "'", "VALUES('0'", $new_sql);
				//echo $new_sql . "\r\n\r\n";
				//die();
				$stmt = DB::run($new_sql);
			}
		} catch(PDOException $e) {
			$error = array("error"=> array("text"=>$e->getMessage()));
			echo json_encode($error);
		}	
	}
}

function updateEAMSForm() {
	
	$arrSet = array();
	$where_clause = "";
	$table_name = "";
	$table_id = "";
	$customer_id = "";
	$eams_form_uuid = "";
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="eams_form_uuid" || $fieldname=="eams_form_id" || $fieldname=="attachments" || $fieldname=="send_document_id" || $fieldname=="partie_count") {
			continue;
		}
		if ($fieldname=="customer_id") {
			$customer_id = $value;
			// continue;
		}
		if ($fieldname=="billing_time") {
			continue;
		}
		if ($fieldname=="billing_time_dropdown") {
			continue;
		}
		if ($fieldname=="table_id" || $fieldname=="id") {
			$table_id = $value;
			//let's look up for uuid
			$where_clause = " = " . $value;
		} else {
			$arrSet[] = "`" . $fieldname . "` = '" . addslashes($value) . "'";
		}
	}
	//where
	$where_clause = "`" . $table_name . "_id`" . $where_clause . "
	AND (`customer_id` = " . $_SESSION['user_customer_id'] . " OR `customer_id` = 0)";
	
	//actual query
	$sql = "UPDATE `cse_eams_forms`
	SET " . implode(",
	", $arrSet) . "
	WHERE " . $where_clause;
	//die($sql . "\r\n");
	try {
		$db = getConnection();
		
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("eams_form_id", $table_id);
		$stmt->execute();
		trackEAMSForm("update", $table_id);
		echo json_encode(array("success"=>$table_id)); 
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}

function trackEAMSForm($operation, $eams_form_id) {
	$sql = "INSERT INTO cse_eams_forms_track (`user_uuid`, `user_logon`, `operation`, `eams_form_id`, `name`, `sort_order`, `display_name`, `status`, `customer_id`, `deleted`)
	SELECT '" . $_SESSION['user_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $operation . "', `eams_form_id`, `name`, `sort_order`, `display_name`, `status`, `customer_id`, `deleted`
	FROM cse_eams_forms
	WHERE 1
	AND eams_form_id = " . $eams_form_id . "
	AND customer_id = " . $_SESSION['user_customer_id'] . "
	LIMIT 0, 1";
	//die($sql);
	try {
		DB::run($sql);
	$new_id = DB::lastInsertId();
	
		$eams_form = getFormInfo($eams_form_id);
		$case_uuid = "";
		$activity_category = "Forms";
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
		$activity = "Form [" . $eams_form->name . "] was " . $operation . "  by " . $_SESSION['user_name'];
		
		$billing_time = 0;
		if (isset($_POST["billing_time"])) {
			$billing_time = passed_var("billing_time", "post");
		}
		recordActivity($operation, $activity, $case_uuid, $new_id, $activity_category, $billing_time);
	
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
