<?php
use Slim\Routing\RouteCollectorProxy;

$app->group('', function (RouteCollectorProxy $app) {
	$app->get('/workflows', 'getWorkflows');

	$app->group('/workflow', function (RouteCollectorProxy $app) {
		$app->get('/{workflow_id}', 'getWorkflow');
		$app->get('/cases/{workflow_id}', 'getWorkflowKases');
		$app->get('/triggers/{workflow_id}', 'getWorkflowTriggers');
		$app->get('/applied/{case_id}', 'getWorflowApplied');

		$app->post('/activate', 'activateWorkflow');
		$app->post('/save', 'saveWorkflow');
	});

	$app->group('/trigger', function (RouteCollectorProxy $app) {
		$app->post('/delete', 'deleteTrigger');
		$app->post('/save', 'saveTrigger');
	});

	$app->get('/workflownext', 'getNextWorkflowID');
})->add(\Api\Middleware\Authorize::class);

function getWorkflowKases($workflow_id) {
	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	
	$sql = "SELECT DISTINCT ccase.case_id id, ccase.case_type, 
	0 balance,
	IF (ccase.case_name = '', IF (ccase.case_number = '', ccase.file_number, ccase.case_number), ccase.case_name) case_name
		
	FROM cse_personal_injury_trigger cpit
	
	INNER JOIN cse_trigger trig
	ON cpit.trigger_uuid = trig.trigger_uuid
	
	INNER JOIN cse_personal_injury cpi
	ON cpit.personal_injury_uuid = cpi.personal_injury_uuid
	
	INNER JOIN cse_case ccase
	ON cpi.case_id = ccase.case_id
	
	INNER JOIN cse_workflow cw
	ON trig.workflow_uuid = cw.workflow_uuid 
	
	WHERE cw.workflow_id = :workflow_id
	AND ccase.customer_id = :customer_id";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("workflow_id", $workflow_id);
		$stmt->execute();
		$cases = $stmt->fetchAll(PDO::FETCH_OBJ);
		echo json_encode($cases);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
	}
}
function getWorflowApplied($case_id) {
	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	
	$sql = "SELECT DISTINCT cw.workflow_id id, cw.description
	FROM cse_personal_injury_trigger cpit
	
	INNER JOIN cse_trigger trig
	ON cpit.trigger_uuid = trig.trigger_uuid
	
	INNER JOIN cse_personal_injury cpi
	ON cpit.personal_injury_uuid = cpi.personal_injury_uuid
	INNER JOIN cse_case ccase
	ON cpi.case_id = ccase.case_id
	
	INNER JOIN cse_workflow cw
	ON trig.workflow_uuid = cw.workflow_uuid 
	
	WHERE ccase.case_id = :case_id
	AND ccase.customer_id = :customer_id";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->bindParam("case_id", $case_id);
		$stmt->execute();
		$workflows = $stmt->fetchAll(PDO::FETCH_OBJ);
		echo json_encode($workflows);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
	}
}
function getWorkflow($workflow_id) {
	getWorkflows($workflow_id, false);
}
function getWorkflowInfo($workflow_id) {
	return getWorkflows($workflow_id, true);
}
function getWorkflows($workflow_id = "", $blnReturn = false) {
	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	
	$sql = "SELECT wf.*, wf.workflow_id id, wf.workflow_uuid uuid
	FROM cse_workflow wf
	WHERE customer_id = :customer_id
	AND deleted = 'N'";
	
	if ($workflow_id!="") {
		$sql .= "
		AND wf.workflow_id = :workflow_id";
	}
	$sql .= "
		ORDER BY workflow_id";
	
	$sql = "SELECT wf.*, wf.workflow_id id, wf.workflow_uuid uuid, 
	IFNULL(last_action.activate_date, '') activate_date, usr.user_name activation_user
	FROM cse_workflow wf
    
    LEFT OUTER JOIN(
		SELECT workflow_id, MAX(time_stamp) activate_date
        FROM cse_workflow_track";
	
		if ($workflow_id!="") {
			$sql .= "
			WHERE workflow_id = :workflow_id";
		}
		$sql .= "
        GROUP BY workflow_id
    ) last_action
    ON wf.workflow_id = last_action.workflow_id
    
    LEFT OUTER JOIN ikase.cse_user usr
    ON wf.activated_by = usr.user_uuid
	
	WHERE wf.customer_id = :customer_id
	AND wf.deleted = 'N'";
	
	if ($workflow_id!="") {
		$sql .= "
		AND wf.workflow_id = :workflow_id";
	}
	$sql .= "
		ORDER BY workflow_id";
		
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		if ($workflow_id!="") {
			$stmt->bindParam("workflow_id", $workflow_id);
		}
		$stmt->execute();
		
		if ($workflow_id!="") {
			$workflow = $stmt->fetchObject();
			if ($blnReturn) {
				return $workflow;
			} else {
				echo json_encode($workflow);     
			}
		} else {
			$workflows = $stmt->fetchAll(PDO::FETCH_OBJ);
			echo json_encode($workflows);     
		}
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
	}
}
function getWorkflowTriggers($workflow_id) {
	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	
	$sql = "SELECT trig.*, trig.trigger_id id, trig.trigger_uuid uuid
	FROM cse_workflow wf
	INNER JOIN cse_trigger trig
	ON wf.workflow_uuid = trig.workflow_uuid
	WHERE wf.customer_id = :customer_id
	AND wf.workflow_id = :workflow_id
	AND wf.deleted = 'N'
	AND trig.deleted = 'N'";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("workflow_id", $workflow_id);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$workflows = $stmt->fetchAll(PDO::FETCH_OBJ);

        echo json_encode($workflows);     
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
	}
}
function getNextWorkflowID() {
	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	
	$sql = "SELECT (IFNULL(MAX(workflow_id), 0) + 1)  * 100 `max_id`
	FROM cse_workflow
	WHERE customer_id = :customer_id";
	try {
		//die($sql);
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		$stat = $stmt->fetchObject();

        echo json_encode($stat);     
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        echo json_encode($error);
	}
}
function activateWorkflow() {
	session_write_close();
	$customer_id = $_SESSION["user_customer_id"];
	$activated_by = $_SESSION["user_id"];
	
	$workflow_id = passed_var("workflow_id", "post");
	$active = passed_var("active", "post");
	
	$sql = "UPDATE cse_workflow
		SET active = :active, 
		activated_by = :activated_by
		WHERE workflow_id = :workflow_id
		AND customer_id = :customer_id";
	try {	
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("active", $active);
		$stmt->bindParam("workflow_id", $workflow_id);
		$stmt->bindParam("activated_by", $activated_by);
		$stmt->bindParam("customer_id", $customer_id);
		$stmt->execute();
		
		trackWorkflow("activate", $workflow_id);
		
		echo json_encode(array("success"=>true, "workflow_id"=>$workflow_id));
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
	exit();
}
function saveWorkflow() {
	$arrFields = array();
	$arrSet = array();
	$table_name = "workflow";
	$table_id = passed_var("table_id", "post");

	$blnUpdate = (is_numeric($table_id) && $table_id!="" && $table_id > 0);
	
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_id") {
			continue;
		}
		if (!$blnUpdate) {
			$arrFields[] = "`" . $fieldname . "`";
			$arrSet[] = "'" . addslashes($value) . "'";
		} else {
			$arrSet[] = "`" . $fieldname . "` = " . "'" . addslashes($value) . "'";
		}
	}	

	//insert the parent record first
	if (!$blnUpdate) { 
		$table_uuid = uniqid("RD", false);
		$sql = "INSERT INTO `cse_" . $table_name ."` (`" . $table_name . "_uuid`, `customer_id`, " . implode(",", $arrFields) . ") 
			VALUES('" . $table_uuid . "', '" . $_SESSION['user_customer_id'] . "', " . implode(",", $arrSet) . ")";
		//die($sql);
		try {
			DB::run($sql);
	$new_id = DB::lastInsertId();
			
			echo json_encode(array("success"=>true, "id"=>$new_id));
			//track now
			trackWorkflow("insert", $new_id);	
		} catch(PDOException $e) {	
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}	
	} else {
		//where
		$where_clause = "= '" . $table_id . "'";
		$where_clause = "`" . $table_name . "_id`" . $where_clause . "
		AND `customer_id` = " . $_SESSION['user_customer_id'];

		//actual query
		$sql = "UPDATE `cse_" . $table_name . "`
		SET " . implode(",", $arrSet) . "
		WHERE " . $where_clause;
		
		//die(implode(",", $arrSet));
		
		//die($sql);
		
		try {
			$stmt = DB::run($sql);
			
			echo json_encode(array("success"=>true, "id"=>$table_id));
			//track now	
			trackWorkflow("update", $table_id);	
		} catch(PDOException $e) {	
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}	
	}
	exit();
}
function deleteTrigger() {
	$id = passed_var("id", "post");
	$sql = "UPDATE `cse_trigger`
			SET `deleted` = 'Y'
			WHERE `trigger_id`=:id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		echo json_encode(array("success"=>"trigger marked as deleted"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
       	echo json_encode($error);
	}
}
function saveTrigger() {
	$arrFields = array();
	$arrSet = array();
	$table_name = "trigger";
	$table_id = passed_var("table_id", "post");
	$workflow_uuid = "";
	$trigger = "";
	
	$blnUpdate = (is_numeric($table_id) && $table_id!="" && $table_id > 0);
	
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_id") {
			continue;
		}
		if ($fieldname=="trigger") {
			/*
			$trigger = "A";
			if ($value!="A") {
				$trigger = "B";
			}
			$value = $trigger;
			*/
		}
		if ($fieldname=="workflow_id") {
			if ($value!="") {
				$workflow = getWorkflowInfo($value);
				$workflow_uuid = $workflow->uuid;
			}
			continue;
		}
		if (!$blnUpdate) {
			$arrFields[] = "`" . $fieldname . "`";
			$arrSet[] = "'" . addslashes($value) . "'";
		} else {
			$arrSet[] = "`" . $fieldname . "` = " . "'" . addslashes($value) . "'";
		}
	}	

	//insert the parent record first
	if (!$blnUpdate) { 
		$arrFields[] = "`workflow_uuid`";
		$arrSet[] = "'" . $workflow_uuid . "'";
			
		$table_uuid = uniqid("RD", false);
		$sql = "INSERT INTO `cse_" . $table_name ."` (`" . $table_name . "_uuid`, `customer_id`, " . implode(",", $arrFields) . ") 
			VALUES('" . $table_uuid . "', '" . $_SESSION['user_customer_id'] . "', " . implode(",", $arrSet) . ")";
		//die($sql);
		try {
			DB::run($sql);
	$new_id = DB::lastInsertId();
			
			echo json_encode(array("success"=>true, "id"=>$new_id));
			//track now
			//trackDeduction("insert", $new_id);	
		} catch(PDOException $e) {	
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}	
	} else {
		//where
		$where_clause = "= '" . $table_id . "'";
		$where_clause = "`" . $table_name . "_id`" . $where_clause . "
		AND `customer_id` = " . $_SESSION['user_customer_id'];

		//actual query
		$sql = "UPDATE `cse_" . $table_name . "`
		SET " . implode(",", $arrSet) . "
		WHERE " . $where_clause;
		
		//die(implode(",", $arrSet));
		
		//die($sql);
		
		try {
			$stmt = DB::run($sql);
			
			echo json_encode(array("success"=>true, "id"=>$table_id));
			//track now	
			//trackDeduction("update", $table_id);	
		} catch(PDOException $e) {	
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}	
	}
}
function trackWorkflow($operation, $workflow_id) {
	$sql = "INSERT INTO cse_workflow_track ( `user_uuid`,  `user_logon`,  `operation`, `workflow_id`,  `workflow_uuid`,  `workflow_date`,  `case_type`,  `workflow_number`,  `description`,  `active`,  `activated_by`,  `deleted`,  `customer_id`)
	SELECT '" . $_SESSION['user_id'] . "', '" . addslashes($_SESSION['user_name']) . "', '" . $operation . "', `workflow_id`,  `workflow_uuid`,  `workflow_date`,  `case_type`,  `workflow_number`,  `description`,  `active`,  `activated_by`,  `deleted`,  `customer_id`
	FROM cse_workflow
	WHERE 1
	AND workflow_id = " . $workflow_id . "
	AND customer_id = " . $_SESSION['user_customer_id'] . "
	LIMIT 0, 1";
	//die($sql);
	try {
		DB::run($sql);
	$new_id = DB::lastInsertId();
		
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
