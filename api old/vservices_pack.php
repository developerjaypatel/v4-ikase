<?php
$app->get('/vservices', authorize('user'), 'getvServices');
$app->get('/vservices/:id', authorize('user'), 'getvService');
$app->get('/specialty/:id', 'getSpecialty');
$app->get('/specialties', 'getSpecialties');

$app->post('/vservice/delete', authorize('user'), 'deletevService');
$app->post('/vservice/add', authorize('user'), 'addvService');
$app->post('/vservice/update', authorize('user'), 'updatevService');

function getvService($id) {
	session_write_close();
	//return a row if id is valid
	$sql = "SELECT `vservice`.*, `vservice`.`vservice_id` `id` , `vservice`.`vservice_uuid` `uuid`
		FROM ikase.`cse_vservice` `vservice` 
		WHERE `vservice`.`vservice_id` = :id
		AND `vservice`.deleted = 'N'";

	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		
		$stmt->execute();
		$vservice = $stmt->fetchObject();
		$db = null;
		//die($sql);

        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($vservice);
        } else {
            echo $_GET['callback'] . '(' . json_encode($vservice) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getvServiceInfo($id) {
	session_write_close();
	//return a row if id is valid

	$sql = "SELECT `vservice`.*, `vservice`.`vservice_id` `id`, `vservice`.`vservice_uuid` `uuid`
		FROM ikase.`cse_vservice` `vservice` 
		WHERE `vservice`.`vservice_id` = :id
		AND `vservice`.`deleted` = 'N'";

	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		
		$stmt->execute();
		$vservice = $stmt->fetchObject();
		$db = null;

        return $vservice;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getvServices() {
	session_write_close();
    $sql = "SELECT DISTINCT `vservice`.*, `vservice`.vservice_id id , `vservice`.vservice_uuid uuid
			FROM ikase.`cse_vservice` `vservice`
			WHERE 1
			AND vservice.deleted = 'N'
			ORDER BY `vservice`.`vservice_id` ASC";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$vservices = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		echo json_encode($vservices);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getSpecialty($id) {
	session_write_close();
	//return a row if id is valid
	$sql = "SELECT *
		FROM ikase.`cse_medical_specialties`
		WHERE `specialty_id` = :id";

	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		
		$stmt->execute();
		$specialty = $stmt->fetchObject();
		$db = null;
		//die($sql);

        // Include support for JSONP requests
        if (!isset($_GET['callback'])) {
            echo json_encode($specialty);
        } else {
            echo $_GET['callback'] . '(' . json_encode($specialty) . ');';
        }

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getSpecialties() {
	session_write_close();
    $sql = "SELECT *
			FROM ikase.`cse_medical_specialties`
			WHERE 1
			ORDER BY `specialty` ASC";
	//die($sql);
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$specialties = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		echo json_encode($specialties);

	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}

function addvService() {
	$request = Slim::getInstance()->request();
	$db = getConnection();
	$arrFields = array();
	$arrSet = array();
	$table_name = "";
	$table_id = "";
	$case_id = "";
	$carrier_uuid = "";
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="case_id") {
			$case_id = $value;
			continue;
		}
		if ($fieldname=="carrier") {
			$carrier = getCorporationInfo($value);
			$carrier_uuid = $carrier->uuid;
			continue;
		}
		//FOR NOW
		if ($fieldname=="table_id") {
			continue;
		}
		if (strpos($fieldname, "_date") > -1) {
			if ($value!="") {
				$value = date("Y-m-d", strtotime($value));
			} else {
				$value = "0000-00-00";
			}
		}

		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "'" . addslashes($value) . "'";
	}

	$table_uuid = uniqid("KS", false);
	$sql = "INSERT INTO ikase.`cse_" . $table_name ."` (`" . $table_name . "_uuid`, " . implode(",", $arrFields) . ") 
			VALUES('" . $table_uuid . "', " . implode(",", $arrSet) . ")";
			//die(print_r($arrFields));
			
	$last_updated_date = date("Y-m-d H:i:s");
	try { 
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$new_id = $db->lastInsertId();
		
		$db = null;
		
		echo json_encode(array("id"=>$new_id, "uuid"=>$table_uuid)); 
		
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}
function updatevService() {
	$request = Slim::getInstance()->request();
	$db = getConnection();
	$arrSet = array();
	$where_clause = "";
	$table_name = "";
	$table_id = "";
	$carrier_uuid = "";
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");

		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		//skip fields in update
		if ($fieldname=="case_id") {
			continue;
		}
		if (strpos($fieldname, "_date") > -1) {
			if ($value!="") {
				$value = date("Y-m-d", strtotime($value));
			} else {
				$value = "0000-00-00";
			}
		}
		if ($fieldname=="carrier") {
			$carrier = getCorporationInfo($value);
			$carrier_uuid = $carrier->uuid;
			continue;
		}
		if ($fieldname=="table_id" || $fieldname=="id") {
			$table_id = $value;
			$where_clause = " = " . $value;
		} else {
			$arrSet[] = "`" . $fieldname . "` = '" . addslashes($value) . "'";
		}
	}
	$my_vservice = getvServiceInfo($table_id);
	$table_uuid = $my_vservice->uuid;
	

	$where_clause = "`" . $table_name . "_id`" . $where_clause;
	$sql = "
	UPDATE ikase.`cse_" . $table_name . "`
	SET " . implode(", ", $arrSet) . "
	WHERE " . $where_clause;
	
	try {		
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		$db = null;
		
		echo json_encode(array("success"=>$table_id)); 
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}
function deletevService() {
	$id = passed_var("id", "post");
	$sql = "UPDATE ikase.cse_vservice tsk
			SET tsk.`deleted` = 'Y'
			WHERE `vservice_id`=:id";
	
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		
		$db = null;
		echo json_encode(array("success"=>"vservice marked as deleted"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
?>