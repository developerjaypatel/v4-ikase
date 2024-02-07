<?php
$app->get('/cascade/:id', 'getCascade');
$app->get('/cascades', 'getCascades');

$app->post('/cascade/add', 'addCascade');
$app->post('/cascade/delete', 'deleteCascade');
$app->post('/cascade/update', 'updateCascade');

function getCascade($id) {
	$sql = "SELECT `cascade_id` `id`, `cascade_uuid` `uuid`, `cascade_id`, `cascade_uuid`, `cascade` `value`
	FROM `tbl_cascade` 
	WHERE 1
	AND cascade_id = :id
	AND deleted = 'N'";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$cascade = $stmt->fetchObject();
		$db = null;
		
		echo json_encode($cascade);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function getCascades() {
	$sql = "SELECT `cascade_id` `id`, `cascade_uuid` `uuid`, `cascade_id`, `cascade_uuid`, `cascade` `value`
	FROM `tbl_cascade` 
	WHERE 1
	AND deleted = 'N'
	ORDER BY cascade_id DESC";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		
		$stmt->execute();
		$cascades = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		
		echo json_encode($cascades);
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
function addCascade() {
	$request = Slim::getInstance()->request();
	$arrFields = array();
	$arrSet = array();
	$table_name = "cascade";
	$table_id = "";
	//default attribute
	$table_attribute = "main";
	//die(print_r($_POST));
	foreach($_POST as $fieldname=>$value) {
		$value = passed_var($fieldname, "post");
		$fieldname = str_replace("Input", "", $fieldname);
		if ($fieldname=="table_name") {
			continue;
		}
		if ($fieldname=="batch_id") {
			continue;
		}
		if ($fieldname=="cascade_id") {
			continue;
		}
		if ($fieldname=="table_attribute") {
			$table_attribute = $value;
			continue;
		}
		if ($fieldname=="table_id") {
			continue;
		}
		$arrFields[] = "`" . $fieldname . "`";
		$arrSet[] = "'" . addslashes($value) . "'";
	}
	// die(print_r($arrFields));
	
	$table_uuid = uniqid("DR", false);
	$sql = "INSERT INTO `tbl_" . $table_name ."` (`" . $table_name . "_uuid`, " . implode(",", $arrFields) . ") 
			VALUES('" . $table_uuid . "', " . implode(",", $arrSet) . ")";
	//die($sql . "\r\n");
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
function updateCascade() {
	$request = Slim::getInstance()->request();
	$table_id = passed_var("table_id", "post");
	$cascade_id = passed_var("cascade_id", "post");
	if ($table_id=="" || !is_numeric($table_id)) {
		$table_id = $cascade_id;
	}
	if ($table_id < 0 || !is_numeric($table_id)) {
		addCascade();
		return;
	}
	$arrSet = array();
	$where_clause = "";
	$table_name = "cascade";
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
		if ($fieldname=="cascade_name") {
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
		if ($fieldname=="table_id" ||$fieldname=="id" || $fieldname=="table_name") {
			continue;
		}
		if ($fieldname=="cascade_id") {
			$table_id = $value;
			$where_clause = " = " . $value;
		} else {
			$arrSet[] = "`" . $fieldname . "` = '" . addslashes($value) . "'";
		}
	}
	// die($table_name);
	// die(print_r($arrSet));
	$where_clause = "`" . $table_name . "_id`" . $where_clause;
	$sql = "
	UPDATE `tbl_" . $table_name . "`
	SET " . implode(", ", $arrSet) . "
	WHERE " . $where_clause;
	// die($sql . "\r\n");
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
function deleteCascade() {
	$id = passed_var("id", "post");
	$sql = "UPDATE tbl_cascade
			SET `deleted` = 'Y'
			WHERE `cascade_id`=:id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$db = null;
		echo json_encode(array("success"=>"cascade marked as deleted"));
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
	//trackBatch("delete", $id);	
}
function getCascadeInfo($cascade_id) {
	$sql = "SELECT `tbl_cascade`.*,
	`cascade_id` `id`, `cascade_uuid` `uuid`
	FROM `tbl_cascade` 
	WHERE 1
	AND cascade_id = :cascade_id";
	
	//REPLACE(REPLACE(`tbl_cascade`.`content`, '{', '`'), '}', '~') `content`,
	//echo $sql;
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("cascade_id", $cascade_id);
		$stmt->execute();
		$cascade = $stmt->fetchObject();
		$db = null;
		if (is_object($cascade)) {
			$cascade->content = str_replace(chr(92), '', $cascade->content);
		}
		return $cascade;
	} catch(PDOException $e) {
		$error = array("error"=> array("text"=>$e->getMessage()));
        	echo json_encode($error);
	}
}
?>