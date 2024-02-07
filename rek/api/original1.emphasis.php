<?php
$app->get('/emphasis/:id', 'getEmphasis');
$app->get('/emphasises', 'getEmphasises');

$app->post('/emphasis/add', 'addEmphasis');
$app->post('/emphasis/delete', 'deleteEmphasis');
$app->post('/emphasis/update', 'updateEmphasis');

function getEmphasis($id) {
	$sql = "SELECT `emphasis_id` `id`, `emphasis_uuid` `uuid`, `emphasis_id`, `emphasis_uuid`, `emphasis` `value`, 
	`color`, `text_color` 
	FROM `tbl_emphasis` 
	WHERE 1
	AND emphasis_id = :id
	AND deleted = 'N'";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$emphasis = $stmt->fetchObject();
		$db = null;
		
		echo json_encode($emphasis);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getEmphasises() {
	$sql = "SELECT `emphasis_id` `id`, `emphasis_uuid` `uuid`, `emphasis_id`, `emphasis_uuid`, `emphasis` `value`,
	`color`, `text_color` 
	FROM `tbl_emphasis` 
	WHERE 1
	AND deleted = 'N'
	ORDER BY emphasis_id DESC";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		
		$stmt->execute();
		$emphasises = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		
		echo json_encode($emphasises);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function addEmphasis() {
	$request = Slim::getInstance()->request();
	$arrFields = array();
	$arrSet = array();
	$table_name = "emphasis";
	$table_id = "";
	//default attribute
	$table_attribute = "main";
	//die(print_r($_POST));
	
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			$table_name = $value;
			continue;
		}
		if ($fieldname=="emphasis_id" || $fieldname=="table_id") {
			continue;
		}
		
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "'" . addslashes($value) . "'";
	}
	
	$table_uuid = uniqid("DR", false);
	$sql = "INSERT INTO `tbl_" . $table_name ."` (`" . $table_name . "_uuid`, " . implode(",", $arrFields) . ") 
			VALUES('" . $table_uuid . "', " . implode(",", $arrSet) . ")";
	//die($sql);
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
function updateEmphasis() {
	$request = Slim::getInstance()->request();
	$table_id = passed_var("emphasis_id", "post");
	if ($table_id < 0 || !is_numeric($table_id)) {
		addEmphasis();
		return;
	}
	$arrSet = array();
	$where_clause = "";
	$table_name = "emphasis";
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
		//no uuids  || $fieldname=="case_uuid"
		if (strpos($fieldname, "_uuid") > -1) {
			continue;
		}
		if ($fieldname=="table_id") {
			continue;
		}
		if ($fieldname=="emphasis_id") {
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
function deleteEmphasis() {
	$id = passed_var("id", "post");
	$sql = "UPDATE tbl_emphasis
			SET `deleted` = 'Y'
			WHERE `emphasis_id`=:id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$db = null;
		echo json_encode(array("success"=>"emphasis marked as deleted"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	//trackBatch("delete", $id);	
}
?>