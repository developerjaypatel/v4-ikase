<?php
use Slim\Routing\RouteCollectorProxy;

$app->group('', function (RouteCollectorProxy $app) {
	$app->group('/coa', function (RouteCollectorProxy $app) {
		$app->get('/{case_id}/{coa_id}', 'getFullCOA');
		$app->post('/add', 'addFullCOA');
	});

	$app->get('/coas/{case_id}/{new_legal_id}', 'getCOAs');
})->add(Api\Middleware\Authorize::class);

	function getCOAs($case_id, $new_legal_id) {
    $sql = "SELECT c.*, c.coa_id id 
			FROM `cse_coa` c 
			WHERE c.deleted = 'N'
			AND c.case_id = " . $case_id . "
			AND c.customer_id = " . $_SESSION['user_customer_id'] . "
			ORDER by c.coa_id";
			//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$stmt->bindParam("new_legal_id", $new_legal_id);
		$coas = $stmt->fetchAll(PDO::FETCH_OBJ);
        if (!isset($_GET['callback'])) {
            echo json_encode($coas);
        } else {
            echo $_GET['callback'] . '(' . json_encode($coas) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getFullCOA($case_id, $coa_id) {
    $sql = "SELECT c.*, c.coa_id id, c.coa_uuid uuid
			FROM `cse_coa` c 
			WHERE c.case_id=:case_id
			AND c.coa_id = :coa_id
			AND c.customer_id = " . $_SESSION['user_customer_id'] . "
			AND c.deleted = 'N'";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->bindParam("coa_id", $coa_id);
		$stmt->execute();
		$coa = $stmt->fetchObject();
		$coa->coa_info = str_replace("\r\n", " ", $coa->coa_info);
		$coa->coa_info = str_replace("\n", " ", $coa->coa_info);
		$coa->coa_info = str_replace(chr(13), " ", $coa->coa_info);
		//die($coa->coa_info);

        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($coa);
        } else {
            echo $_GET['callback'] . '(' . json_encode($coa) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getCOA($case_id) {
    $sql = "SELECT c.*, c.coa_id id, c.coa_uuid uuid
			FROM `cse_coa` c 
			WHERE c.case_id=:case_id
			AND c.customer_id = " . $_SESSION['user_customer_id'] . "
			AND c.deleted = 'N'";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->execute();
		$coa = $stmt->fetchObject();

        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($coa);
        } else {
            echo $_GET['callback'] . '(' . json_encode($coa) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function addCOA() {
	$arrFields = array();
	$arrSet = array();
	$table_name = "coa";
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="case_id"){
			continue;
		}
		if ($fieldname=="table_id") {
			continue;
		}
		if ($fieldname=="table_uuid") {
			continue;
		}
		if ($fieldname=="coa_id") {
			continue;
		}
		
		//if ($fieldname=="dateandtime") {
		if (strpos($fieldname, "_date")!==false || strpos($fieldname, "date_")!==false) {
			if ($value!="") {
				$value = date("Y-m-d H:i:s", strtotime($value));
			} else {
				$value = "0000-00-00 00:00:00";
			}
		}
		
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "'" . str_replace("'", "\'", $value) . "'";
	}	
	
	$table_uuid = uniqid("RD", false);
	//insert the parent record first
	$sql = "INSERT INTO `cse_" . $table_name ."` (`" . $table_name . "_uuid`, `customer_id`, " . implode(",", $arrFields) . ") 
		VALUES('" . $table_uuid . "', '" . $_SESSION['user_customer_id'] . "', " . implode(",", $arrSet) . ")";
	die($sql);
	try {
		DB::run($sql);
	$new_id = DB::lastInsertId();
		
		echo json_encode(array("success"=>true, "id"=>$new_id));
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}
function addFullCOA() {
	session_write_close();
	$arrFields = array();
	$arrSet = array();
	$case_id = 0;
	$table_name = "coa";
	$table_id = passed_var("table_id", "post");
	$blnUpdate = (is_numeric($table_id) && $table_id!="" && $table_id > 0);
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
		if ($fieldname=="billing_time_dropdown") {
			continue;
		}
		
		if ($fieldname=="case_id"){
			
			$case_id = $value;
			//die("case:" . $case_id);
			$injury = getInjuriesInfo($case_id);
			$injury_uuid = $injury[0]->uuid;
			//die(print_r($injury));
			continue;
		}
		if ($fieldname=="table_id") {
			continue;
		}
		
		//if ($fieldname=="dateandtime") {
		if (strpos($fieldname, "_date")!==false || strpos($fieldname, "date_")!==false) {
			if ($value!="") {
				$value = date("Y-m-d H:i:s", strtotime($value));
			} else {
				$value = "0000-00-00 00:00:00";
			}
		}
		//die("before");
		if (!$blnUpdate) {
			$arrFields[] = "`" . $fieldname . "`";
			$arrSet[] = "'" . addslashes($value) . "'";
		} else {
			$arrSet[] = "`" . $fieldname . "` = " . "'" . addslashes($value) . "'";
		}
		//die("after");
	}	
	//$case_id = passed_var("case_id", "post");
	//die("case:" . $case_id);
	//die("table:" . $table_id);
	//die(print_r($arrFields));
	//die(print_r($arrSet));
	
	//insert the parent record first
	if (!$blnUpdate) { 
		$table_uuid = uniqid("RD", false);
		$sql = "INSERT INTO `cse_" . $table_name ."` (`" . $table_name . "_uuid`, `customer_id`, " . implode(",", $arrFields) . ", `case_id`) 
			VALUES('" . $table_uuid . "', '" . $_SESSION['user_customer_id'] . "', " . implode(",", $arrSet) . ", '" . $case_id . "')";
		//die($sql);
		try {
			DB::run($sql);
	$new_id = DB::lastInsertId();
			
			echo json_encode(array("success"=>true, "id"=>$new_id));
			//track now
			//trackPerson("insert", $new_id);	
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
			//trackPerson("insert", $new_id);	
		} catch(PDOException $e) {	
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}	
	}
}
function updateCOA() {
	$arrFields = array();
	$arrSet = array();
	$table_name = "coa";
	$table_id = "";
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="case_id"){
			continue;
		}
		if ($fieldname=="billing_time") {
			continue;
		}
		if ($fieldname=="billing_time_dropdown") {
			continue;
		}
		if ($fieldname=="table_id") {
			$table_id = $value;
			continue;
		}
		if ($fieldname=="coa_id") {
			continue;
		}
		if ($fieldname=="table_uuid") {
			continue;
		}
		
		//if ($fieldname=="dateandtime") {
		if (strpos($fieldname, "_date")!==false || strpos($fieldname, "date_")!==false) {
			if ($value!="") {
				$value = date("Y-m-d H:i:s", strtotime($value));
			} else {
				$value = "0000-00-00 00:00:00";
			}
		}
		
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "`" . $fieldname . "` = '" . addslashes($value) . "'";
	}	
	
	//where
	$where_clause = "= '" . $table_id . "'";
	$where_clause = "`" . $table_name . "_id`" . $where_clause . "
	AND `customer_id` = " . $_SESSION['user_customer_id'];
	
	//actual query
	$sql = "UPDATE `cse_" . $table_name . "`
	SET " . implode(",
	", $arrSet) . "
	WHERE " . $where_clause;
	
	//die($sql);
	try {
		$stmt = DB::run($sql);
		
		echo json_encode(array("success"=>true, "id"=>$table_id));
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
