<?php
$app->get('/language/:id', 'getLanguage');
$app->get('/languages', 'getLanguages');

$app->post('/language/add', 'addLanguage');
$app->post('/language/delete', 'deleteLanguage');
$app->post('/language/update', 'updateLanguage');

function getLanguage($id) {
	$sql = "SELECT `language_id` `id`, `language_uuid` `uuid`, `language_id`, `language_uuid`, `language` `value`
	FROM `tbl_language` 
	WHERE 1
	AND language_id = :id
	AND deleted = 'N'";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$language = $stmt->fetchObject();
		$db = null;
		
		echo json_encode($language);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getLanguages() {
	$sql = "SELECT `language_id` `id`, `language_uuid` `uuid`, `language_id`, `language_uuid`, `language` `value`
	FROM `tbl_language` 
	WHERE 1
	AND deleted = 'N'
	ORDER BY language_id DESC";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		
		$stmt->execute();
		$languages = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		
		echo json_encode($languages);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function addLanguage() {
	$request = Slim::getInstance()->request();
	$arrFields = array();
	$arrSet = array();
	$table_name = "language";
	$table_id = "";
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
		if ($fieldname=="table_id") {
			continue;
		}
		if ($fieldname=="language_id") {
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
		// die($new_id);
		echo json_encode(array("id"=>$new_id, "uuid"=>$table_uuid)); 
		
		$db = null;
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}	
}
function updateLanguage() {
	$request = Slim::getInstance()->request();
	$table_id = passed_var("table_id", "post");
	if ($table_id < 0 || !is_numeric($table_id)) {
		addLanguage();
		return;
	}
	$arrSet = array();
	$where_clause = "";
	$table_name = "language";
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
		if ($fieldname=="table_id" || $fieldname=="id") {
			$table_id = $value;
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
		
		echo json_encode(array("success"=>$table_id)); 
	} catch(PDOException $e) {	
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
function deleteLanguage() {
	$id = passed_var("id", "post");
	$sql = "UPDATE tbl_language
			SET `deleted` = 'Y'
			WHERE `language_id`=:id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$db = null;
		echo json_encode(array("success"=>"language marked as deleted"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	//trackBatch("delete", $id);	
}
?>