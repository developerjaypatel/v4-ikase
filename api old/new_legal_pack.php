<?php

$app->get('/newlegal', authorize('user'),	'getNewLegals');

$app->get('/newlegal/:case_id', authorize('user'),	'getFullnewlegal');

$app->post('/newlegal/add', authorize('user'), 'addFullNewLegal');


function getNewLegals() {
    $sql = "SELECT nl.* 
			FROM `cse_new_legal` nl 
			WHERE nl.deleted = 'N'
			AND nl.customer_id = " . $_SESSION['user_customer_id'] . "
			ORDER by nl.new_legal_id";
	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$new_legals = $stmt->fetchAll(PDO::FETCH_OBJ);
	
		$db = null;
        if (!isset($_GET['callback'])) {
            echo json_encode($new_legals);
        } else {
            echo $_GET['callback'] . '(' . json_encode($new_legals) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getFullNewLegal($case_id) {
    $sql = "SELECT nl.*, nl.new_legal_id id, nl.new_legal_uuid uuid
			FROM `cse_new_legal` nl 
			WHERE nl.case_id=:case_id
			AND nl.customer_id = " . $_SESSION['user_customer_id'] . "
			AND nl.deleted = 'N'";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->execute();
		$newlegal = $stmt->fetchObject();
		if ($newlegal != "") {
			$newlegal->new_legal_info = str_replace("\r\n", " ", $newlegal->new_legal_info);
			$newlegal->new_legal_info = str_replace("\n", " ", $newlegal->new_legal_info);
			$newlegal->new_legal_info = str_replace(chr(13), " ", $newlegal->new_legal_info);
		}
		//die($newlegal->new_legal_info);
		$db = null;

        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($newlegal);
        } else {
            echo $_GET['callback'] . '(' . json_encode($newlegal) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getNewLegal($case_id) {
    $sql = "SELECT nl.*, nl.new_legal_id id, nl.new_legal_uuid uuid
			FROM `cse_new_legal` nl 
			WHERE nl.case_id=:case_id
			AND nl.customer_id = " . $_SESSION['user_customer_id'] . "
			AND nl.deleted = 'N'";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("case_id", $case_id);
		$stmt->execute();
		$newlegal = $stmt->fetchObject();
		$db = null;

        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($newlegal);
        } else {
            echo $_GET['callback'] . '(' . json_encode($newlegal) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function addNewLegal() {
	//die(print_r($_POST));
	//if ($_POST[0]) {
	//}
	$request = Slim::getInstance()->request();
	$arrFields = array();
	$arrSet = array();
	$table_name = "new_legal";
	$table_id = "";
	$info = "";
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="case_id"){
			$case_id = $value;
			continue;
		}
		if ($fieldname=="table_id") {
			continue;
		}
		if ($fieldname=="table_uuid") {
			continue;
		}
		if ($fieldname=="new_legal_id") {
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
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		$new_id = $db->lastInsertId();
		
		echo json_encode(array("success"=>true, "id"=>$new_id));			
		$db = null;
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}
function addFullNewLegal() {
	//die(print_r($_POST));
	session_write_close();
	$request = Slim::getInstance()->request();
	$arrFields = array();
	$arrSet = array();
	$case_id = 0;
	$table_name = "new_legal";
	$table_id = passed_var("table_id", "post");
	//$case_id = passed_var("case_id", "post");
	$blnUpdate = (is_numeric($table_id) && $table_id!="" && $table_id > 0);
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
			$db = getConnection();
			
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			$new_id = $db->lastInsertId();
			
			$stmt = null; $db = null;
			
			echo json_encode(array("success"=>true, "id"=>$new_id, "operation"=>"insert"));
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
			$db = getConnection();
			
			$stmt = $db->prepare($sql);  
			$stmt->execute();
			
			$stmt = null; $db = null;
			
			echo json_encode(array("success"=>true, "id"=>$table_id, "operation"=>"update"));
			//track now
			//trackPerson("insert", $new_id);	
		} catch(PDOException $e) {	
			echo '{"error":{"text":'. $e->getMessage() .'}}'; 
		}	
	}
}
function updateNewLegal() {
	//die(print_r($_POST));
	//if ($_POST[0]) {
	//}
	$request = Slim::getInstance()->request();
	$arrFields = array();
	$arrSet = array();
	$table_name = "new_legal";
	$table_id = "";
	$info = "";
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="case_id"){
			$case_id = $value;
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
		if ($fieldname=="new_legal_id") {
			continue;
		}
		if ($fieldname=="table_uuid") {
			$table_uuid = $value;
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
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		
		echo json_encode(array("success"=>true, "id"=>$table_id));
		$db = null;	
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
	$db = null;
}
?>