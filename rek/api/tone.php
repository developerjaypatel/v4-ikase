<?php
$app->get('/tone/:id', 'getTone');
$app->get('/tones', 'getTones');

$app->post('/tone/add', 'addTone');
$app->post('/tone/delete', 'deleteTone');
$app->post('/tone/update', 'updateTone');

function getTone($id) {
	$sql = "SELECT `tone_id` `id`, `tone_uuid` `uuid`, `tone_id`, `tone_uuid`, `tone` `value`
	FROM `tbl_tone` 
	WHERE 1
	AND tone_id = :id
	AND deleted = 'N'";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$tone = $stmt->fetchObject();
		$db = null;
		
		echo json_encode($tone);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getTones() {
	$sql = "SELECT `tone_id` `id`, `tone_uuid` `uuid`, `tone_id`, `tone_uuid`, `tone` `value`
	FROM `tbl_tone` 
	WHERE 1
	AND deleted = 'N'
	ORDER BY tone_id DESC";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		
		$stmt->execute();
		$tones = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		
		echo json_encode($tones);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function addTone() {
	$request = Slim::getInstance()->request();
	$arrFields = array();
	$arrSet = array();
	$table_name = "tone";
	$tone_id = "";
	//default attribute
	$table_attribute = "main";
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="table_attribute") {
			$table_attribute = $value;
			continue;
		}
		if ($fieldname=="tone_id") {
			continue;
		}
		if ($fieldname=="table_id") {
			continue;
		}
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "'" . addslashes($value) . "'";
	}
	
	$table_uuid = uniqid("DR", false);
	$sql = "INSERT INTO `tbl_" . $table_name ."` (`" . $table_name . "_uuid`, " . implode(",", $arrFields) . ") 
			VALUES('" . $table_uuid . "', " . implode(",", $arrSet) . ")";
	// die($sql);
	$db = getConnection();
	try { 
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		$new_id = $db->lastInsertId();
		
		echo json_encode(array("id"=>$new_id, "uuid"=>$table_uuid)); 
		
		$db = null;
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}
function updateTone() {
	$request = Slim::getInstance()->request();
	$tone_id = passed_var("tone_id", "post");
	if ($tone_id < 0 || !is_numeric($tone_id)) {
		addTone();
		return;
	}
	$arrSet = array();
	$where_clause = "";
	$table_name = "tone";
	$table_attribute = "";
	//die(print_r($_POST));
	foreach($_POST as $fieldname=>$value) {
		if ($fieldname!="noteInput") {
			$value = passed_var($fieldname, "post");
		} else {
			//special case
			//remove script
			$value = processHTML($_POST["noteInput"]);
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
		//no uuids  || $fieldname=="case_uuid"
		if (strpos($fieldname, "_uuid") > -1) {
			continue;
		}
		if ($fieldname=="tone_id" || $fieldname=="id") {
			$tone_id = $value;
			$where_clause = " = " . $value;
		} else {
			$arrSet[] = "`" . $fieldname . "` = '" . addslashes($value) . "'";
		}
	}
	//die(print_r($arrSet));
	$where_clause = "`" . $table_name . "_id`" . $where_clause;
	$sql = "
	UPDATE `tbl_" . $table_name . "`
	SET " . implode(", ", $arrSet) . "
	WHERE " . $where_clause;
	//die($sql . "\r\n");
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->execute();
		
		$db = null;
		
		echo json_encode(array("success"=>$tone_id)); 
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function deleteTone() {
	$id = passed_var("id", "post");
	$sql = "UPDATE tbl_tone
			SET `deleted` = 'Y'
			WHERE `tone_id`=:id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$db = null;
		echo json_encode(array("success"=>"tone marked as deleted"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	//trackBatch("delete", $id);	
}
?>